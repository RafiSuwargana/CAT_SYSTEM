<?php

namespace App\Services;

use App\Models\ItemParameter;
use App\Models\TestSession;
use App\Models\TestResponse;
use App\Models\UsedItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * CAT Service dengan Expected Fisher Information (EFI) dan Expected A Posteriori (EAP)
 * 
 * Metode yang digunakan:
 * - Estimasi θ: EAP (Expected A Posteriori)
 * - Pemilihan Item: EFI (Expected Fisher Information)
 * - SE: SE_EAP = √(Var[θ|responses])
 * - Model Item: 3PL (Three-Parameter Logistic)
 * 
 * OPTIMIZED: Menggunakan caching untuk performa lebih cepat
 */
class CATService
{
    // Cache untuk posterior distribution per session
    private $posteriorCache = [];
    
    // Pre-calculated theta grid dan prior
    private $thetaGrid = null;
    private $priorCache = null;
    
    public function __construct()
    {
        // Pre-calculate theta grid once (1001 points untuk akurasi maksimum, sinkron dengan Python)
        $this->thetaGrid = [];
        for ($i = -6; $i <= 6; $i += 0.012) { // 1001 points, exact same as Python
            $this->thetaGrid[] = $i;
        }
        
        // Pre-calculate prior N(0,2) once
        $this->priorCache = [];
        foreach ($this->thetaGrid as $theta) {
            $this->priorCache[] = $this->normalPdf($theta, 0, 2);
        }
        $priorSum = array_sum($this->priorCache);
        for ($i = 0; $i < count($this->priorCache); $i++) {
            $this->priorCache[$i] /= $priorSum;
        }
    }
    /**
     * Calculate 3PL probability
     * P(θ) = g + (u - g) / (1 + exp(-a * (θ - b)))
     */
    public function probability(float $theta, float $a, float $b, float $g, float $u = 1.0): float
    {
        return $g + ($u - $g) / (1 + exp(-$a * ($theta - $b)));
    }

    /**
     * Calculate item information for 3PL model
     * I(θ) = a² * (P - g)² * Q / (P * (u - g)²)
     */
    public function itemInformation(float $theta, float $a, float $b, float $g, float $u = 1.0): float
    {
        $p = $this->probability($theta, $a, $b, $g, $u);
        $q = 1 - $p;
        
        // Avoid division by zero
        if ($p <= $g || $p >= $u || ($u - $g) == 0) {
            return 0;
        }
        
        // Fisher Information formula for 3PL
        $numerator = pow($a, 2) * pow($p - $g, 2) * $q;
        $denominator = $p * pow($u - $g, 2);
        
        return $numerator / $denominator;
    }

    /**
     * OPTIMIZED: Calculate posterior only once per session
     */
    private function calculatePosterior(string $sessionId): array
    {
        // Check cache first
        if (isset($this->posteriorCache[$sessionId])) {
            return $this->posteriorCache[$sessionId];
        }
        
        $responses = TestResponse::where('session_id', $sessionId)
            ->orderBy('item_order')
            ->get();
        
        if ($responses->isEmpty()) {
            return $this->priorCache;
        }
        
        // Calculate likelihood once
        $likelihood = array_fill(0, count($this->thetaGrid), 1.0);
        
        foreach ($responses as $response) {
            $item = $response->item;
            
            for ($i = 0; $i < count($this->thetaGrid); $i++) {
                $p = $this->probability($this->thetaGrid[$i], $item->a, $item->b, $item->g, 1.0);
                $p = max(1e-10, min(1 - 1e-10, $p));
                
                if ($response->answer == 1) {
                    $likelihood[$i] *= $p;
                } else {
                    $likelihood[$i] *= (1 - $p);
                }
            }
        }
        
        // Calculate posterior
        $posterior = [];
        for ($i = 0; $i < count($this->thetaGrid); $i++) {
            $posterior[$i] = $likelihood[$i] * $this->priorCache[$i];
        }
        
        // Normalize posterior
        $posteriorSum = array_sum($posterior);
        if ($posteriorSum > 0) {
            for ($i = 0; $i < count($posterior); $i++) {
                $posterior[$i] /= $posteriorSum;
            }
        } else {
            $posterior = $this->priorCache;
        }
        
        // Cache result
        $this->posteriorCache[$sessionId] = $posterior;
        
        return $posterior;
    }

    /**
     * OPTIMIZED: Expected Fisher Information with cached posterior
     */
    public function expectedFisherInformation(float $a, float $b, float $g, string $sessionId, float $u = 1.0): float
    {
        $posterior = $this->calculatePosterior($sessionId);
        
        // Calculate EFI using optimized grid
        $efi = 0;
        for ($i = 0; $i < count($this->thetaGrid); $i++) {
            $info = $this->itemInformation($this->thetaGrid[$i], $a, $b, $g, $u);
            $efi += $info * $posterior[$i];
        }
        
        return $efi;
    }

    /**
     * OPTIMIZED: Select next item with pre-calculated posterior
     */
    public function selectNextItem(float $theta, string $sessionId): ?ItemParameter
    {
        // Clear cache for new calculation
        unset($this->posteriorCache[$sessionId]);
        
        // Get used items for this session
        $usedItemIds = UsedItem::where('session_id', $sessionId)
            ->pluck('item_id')
            ->toArray();
        
        // Get available items
        $availableItems = ItemParameter::whereNotIn('id', $usedItemIds)->get();
        
        if ($availableItems->isEmpty()) {
            return null;
        }
        
        // Get b_max and b_min for forcing logic
        $bValues = $availableItems->pluck('b')->toArray();
        $bValuesValid = array_filter($bValues, function($b) {
            return $b >= -6 && $b <= 6;
        });
        
        if (empty($bValuesValid)) {
            $bMax = max($bValues);
            $bMin = min($bValues);
        } else {
            $bMax = max($bValuesValid);
            $bMin = min($bValuesValid);
        }
        
        $margin = max(0.5, 0.1 * ($bMax - $bMin));
        
        // Force difficult item if theta is very high
        if ($theta > $bMax - $margin) {
            foreach ($availableItems as $item) {
                if (abs($item->b - $bMax) < 0.001 && $item->b >= -6 && $item->b <= 6) {
                    return $item;
                }
            }
        }
        
        // Force easy item if theta is very low
        if ($theta < $bMin + $margin) {
            foreach ($availableItems as $item) {
                if (abs($item->b - $bMin) < 0.001 && $item->b >= -6 && $item->b <= 6) {
                    return $item;
                }
            }
        }
        
        // OPTIMIZED: Calculate EFI for all items (posterior calculated once)
        $maxEFI = -1;
        $selectedItem = null;
        
        // Pre-calculate posterior once for all items
        $posterior = $this->calculatePosterior($sessionId);
        
        foreach ($availableItems as $item) {
            // Direct EFI calculation without recalculating posterior
            $efi = 0;
            for ($i = 0; $i < count($this->thetaGrid); $i++) {
                $info = $this->itemInformation($this->thetaGrid[$i], $item->a, $item->b, $item->g, $item->u);
                $efi += $info * $posterior[$i];
            }
            
            if ($efi > $maxEFI) {
                $maxEFI = $efi;
                $selectedItem = $item;
            }
        }
        
        return $selectedItem;
    }

    /**
     * OPTIMIZED: Estimate theta and SE using shared calculations
     */
    public function estimateThetaAndSE(string $sessionId, float $currentTheta): array
    {
        $responses = TestResponse::where('session_id', $sessionId)
            ->orderBy('item_order')
            ->get();
        
        if ($responses->isEmpty()) {
            return [0.0, 1.0];
        }
        
        // Control theta change based on number of responses
        $numResponses = $responses->count();
        $maxAllowedChange = ($numResponses <= 5) ? 1.0 : 0.25;
        
        // Use cached posterior calculation
        $posterior = $this->calculatePosterior($sessionId);
        
        // Calculate EAP
        $thetaEAP = 0;
        for ($i = 0; $i < count($this->thetaGrid); $i++) {
            $thetaEAP += $this->thetaGrid[$i] * $posterior[$i];
        }
        
        // Enforce change limits
        $totalChange = $thetaEAP - $currentTheta;
        
        // Debug logging
        Log::debug("CAT Debug - Item {$numResponses}: θ {$currentTheta} → {$thetaEAP} (Δ=" . abs($totalChange) . ")");
        Log::debug("CAT Debug - Max allowed change: {$maxAllowedChange}");
        
        if (abs($totalChange) > $maxAllowedChange) {
            $direction = ($totalChange > 0) ? 1 : -1;
            $thetaEAP = $currentTheta + $direction * $maxAllowedChange;
            Log::debug("CAT Debug - Change limited to: {$thetaEAP}");
        }
        
        // Absolute theta bounds
        $thetaEAP = max(-6, min(6, $thetaEAP));
        
        // Calculate SE_EAP
        $varianceEAP = 0;
        for ($i = 0; $i < count($this->thetaGrid); $i++) {
            $varianceEAP += pow($this->thetaGrid[$i] - $thetaEAP, 2) * $posterior[$i];
        }
        $seEAP = sqrt($varianceEAP);
        
        Log::debug("CAT Debug - Final: θ={$thetaEAP}, SE={$seEAP}");
        
        return [$thetaEAP, $seEAP];
    }

    /**
     * Check if test should be stopped
     */
    public function shouldStopTest(string $sessionId, float $theta, float $se, array $usedItems): array
    {
        $responses = TestResponse::where('session_id', $sessionId)->get();
        $numResponses = $responses->count();
        
        // Get b_max and b_min values
        $bValues = ItemParameter::whereBetween('b', [-6, 6])->pluck('b')->toArray();
        $bMax = max($bValues);
        $bMin = min($bValues);
        
        // Check if participant got max difficulty item (and answered correctly) or min difficulty item (and answered incorrectly)
        $hasBMax = false;
        $hasBMin = false;
        
        foreach ($responses as $response) {
            $item = $response->item;
            if (abs($item->b - $bMax) < 0.001 && $item->b >= -6 && $item->b <= 6 && $response->answer == 1) {
                $hasBMax = true;
            }
            if (abs($item->b - $bMin) < 0.001 && $item->b >= -6 && $item->b <= 6 && $response->answer == 0) {
                $hasBMin = true;
            }
        }
        
        // Check stopping criteria
        if ($numResponses >= 10 && $se <= 0.25) {
            return [true, "SE_EAP mencapai 0.25 dengan minimal 10 soal"];
        } elseif ($numResponses >= 30) {
            return [true, "Mencapai maksimal 30 soal"];
        } elseif (count($usedItems) >= ItemParameter::count()) {
            return [true, "Semua item telah digunakan"];
        } elseif ($hasBMax) {
            return [true, "Peserta sudah mendapat soal dengan b maksimum (paling sulit): " . number_format($bMax, 3)];
        } elseif ($hasBMin) {
            return [true, "Peserta sudah mendapat soal dengan b minimum (paling mudah): " . number_format($bMin, 3)];
        }
        
        return [false, ""];
    }

    /**
     * Calculate final score
     */
    public function calculateScore(float $theta): float
    {
        return 100 + (15 * $theta);
    }

    /**
     * Normal PDF for EAP calculation
     */
    public function normalPdf(float $x, float $mean, float $std): float
    {
        return (1 / ($std * sqrt(2 * M_PI))) * exp(-0.5 * pow(($x - $mean) / $std, 2));
    }

    /**
     * Start a new test session
     */
    public function startSession(): array
    {
        $sessionId = 'CAT_' . time() . '_' . rand(1000, 9999);
        
        // Create new session
        $session = TestSession::create([
            'session_id' => $sessionId,
            'theta' => 0.0,
            'standard_error' => 1.0,
            'test_completed' => false
        ]);
        
        // Get first item
        $firstItem = $this->selectNextItem(0.0, $sessionId);
        
        if (!$firstItem) {
            throw new \Exception('No items available');
        }
        
        // Mark item as used
        UsedItem::create([
            'session_id' => $sessionId,
            'item_id' => $firstItem->id
        ]);
        
        return [
            'session_id' => $sessionId,
            'item' => $firstItem,
            'theta' => 0.0,
            'se' => 1.0,
            'item_number' => 1,
            'probability' => $this->probability(0.0, $firstItem->a, $firstItem->b, $firstItem->g, $firstItem->u),
            'information' => $this->itemInformation(0.0, $firstItem->a, $firstItem->b, $firstItem->g, $firstItem->u),
            'expected_fisher_information' => $this->expectedFisherInformation($firstItem->a, $firstItem->b, $firstItem->g, $sessionId, $firstItem->u)
        ];
    }

    /**
     * Submit response and get next item
     */
    public function submitResponse(string $sessionId, string $itemId, int $answer): array
    {
        // Clear cache at start of new response
        unset($this->posteriorCache[$sessionId]);
        
        $session = TestSession::where('session_id', $sessionId)->first();
        if (!$session) {
            throw new \Exception('Session not found');
        }
        
        if ($session->test_completed) {
            throw new \Exception('Test already completed');
        }
        
        $item = ItemParameter::find($itemId);
        if (!$item) {
            throw new \Exception('Item not found');
        }
        
        // Get current response count
        $responseCount = TestResponse::where('session_id', $sessionId)->count();
        
        // Calculate probability, information, and EFI before response
        $probabilityBefore = $this->probability($session->theta, $item->a, $item->b, $item->g, $item->u);
        $informationBefore = $this->itemInformation($session->theta, $item->a, $item->b, $item->g, $item->u);
        $efiBefore = $this->expectedFisherInformation($item->a, $item->b, $item->g, $sessionId, $item->u);
        
        // Store response
        TestResponse::create([
            'session_id' => $sessionId,
            'item_id' => $itemId,
            'answer' => $answer,
            'theta_before' => $session->theta,
            'theta_after' => $session->theta, // Will be updated below
            'se_after' => $session->standard_error, // Will be updated below
            'item_order' => $responseCount + 1,
            'probability' => $probabilityBefore,
            'information' => $informationBefore,
            'expected_fisher_information' => $efiBefore
        ]);
        
        // Estimate new theta and SE
        [$newTheta, $newSE] = $this->estimateThetaAndSE($sessionId, $session->theta);
        
        // Update response with new theta and SE
        TestResponse::where('session_id', $sessionId)
            ->where('item_id', $itemId)
            ->update([
                'theta_after' => $newTheta,
                'se_after' => $newSE
            ]);
        
        // Update session
        $session->update([
            'theta' => $newTheta,
            'standard_error' => $newSE
        ]);
        
        // Check if test should stop
        $usedItems = UsedItem::where('session_id', $sessionId)->pluck('item_id')->toArray();
        [$shouldStop, $stopReason] = $this->shouldStopTest($sessionId, $newTheta, $newSE, $usedItems);
        
        if ($shouldStop) {
            $finalScore = $this->calculateScore($newTheta);
            $session->update([
                'test_completed' => true,
                'stop_reason' => $stopReason,
                'final_score' => $finalScore
            ]);
            
            return [
                'test_completed' => true,
                'theta' => $newTheta,
                'se' => $newSE,
                'final_score' => $finalScore,
                'stop_reason' => $stopReason,
                'total_items' => $responseCount + 1
            ];
        }
        
        // Get next item
        $nextItem = $this->selectNextItem($newTheta, $sessionId);
        
        if (!$nextItem) {
            $finalScore = $this->calculateScore($newTheta);
            $session->update([
                'test_completed' => true,
                'stop_reason' => 'No more items available',
                'final_score' => $finalScore
            ]);
            
            return [
                'test_completed' => true,
                'theta' => $newTheta,
                'se' => $newSE,
                'final_score' => $finalScore,
                'stop_reason' => 'No more items available',
                'total_items' => $responseCount + 1
            ];
        }
        
        // Mark next item as used
        UsedItem::create([
            'session_id' => $sessionId,
            'item_id' => $nextItem->id
        ]);
        
        return [
            'test_completed' => false,
            'item' => $nextItem,
            'theta' => $newTheta,
            'se' => $newSE,
            'item_number' => $responseCount + 2,
            'probability' => $this->probability($newTheta, $nextItem->a, $nextItem->b, $nextItem->g, $nextItem->u),
            'information' => $this->itemInformation($newTheta, $nextItem->a, $nextItem->b, $nextItem->g, $nextItem->u),
            'expected_fisher_information' => $this->expectedFisherInformation($nextItem->a, $nextItem->b, $nextItem->g, $sessionId, $nextItem->u)
        ];
    }

    /**
     * Get session history
     */
    public function getSessionHistory(string $sessionId): array
    {
        $session = TestSession::where('session_id', $sessionId)->first();
        if (!$session) {
            throw new \Exception('Session not found');
        }
        
        $responses = TestResponse::where('session_id', $sessionId)
            ->with('item')
            ->orderBy('item_order')
            ->get();
        
        $thetaHistory = [0]; // Start with initial theta
        $seHistory = [1.0]; // Start with initial SE
        
        foreach ($responses as $response) {
            $thetaHistory[] = $response->theta_after;
            $seHistory[] = $response->se_after;
        }
        
        return [
            'session' => $session,
            'responses' => $responses,
            'theta_history' => $thetaHistory,
            'se_history' => $seHistory
        ];
    }
}
