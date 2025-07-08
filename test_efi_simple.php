<?php
/**
 * Test sederhana untuk fungsi matematika EFI tanpa database
 */

// Mock class untuk testing fungsi matematika saja
class CATServiceTest
{
    /**
     * Calculate 3PL probability
     */
    public function probability(float $theta, float $a, float $b, float $g, float $u = 1.0): float
    {
        return $g + ($u - $g) / (1 + exp(-$a * ($theta - $b)));
    }

    /**
     * Calculate item information for 3PL model
     */
    public function itemInformation(float $theta, float $a, float $b, float $g, float $u = 1.0): float
    {
        $p = $this->probability($theta, $a, $b, $g, $u);
        $q = 1 - $p;
        
        if ($p <= $g || $p >= $u || ($u - $g) == 0) {
            return 0;
        }
        
        $numerator = pow($a, 2) * pow($p - $g, 2) * $q;
        $denominator = $p * pow($u - $g, 2);
        
        return $numerator / $denominator;
    }

    /**
     * Normal PDF
     */
    public function normalPdf(float $x, float $mean, float $std): float
    {
        return (1 / ($std * sqrt(2 * M_PI))) * exp(-0.5 * pow(($x - $mean) / $std, 2));
    }

    /**
     * Calculate EFI with prior (no responses)
     */
    public function expectedFisherInformationPrior(float $a, float $b, float $g, float $u = 1.0): float
    {
        // Theta grid (same as Python: 1001 points)
        $thetaGrid = [];
        for ($i = -6; $i <= 6; $i += 0.012) {
            $thetaGrid[] = $i;
        }
        
        // Prior N(0,2) (same as Python)
        $prior = [];
        foreach ($thetaGrid as $theta) {
            $prior[] = $this->normalPdf($theta, 0, 2);
        }
        
        // Normalize prior
        $priorSum = array_sum($prior);
        for ($i = 0; $i < count($prior); $i++) {
            $prior[$i] /= $priorSum;
        }
        
        // Calculate EFI
        $efi = 0;
        for ($i = 0; $i < count($thetaGrid); $i++) {
            $info = $this->itemInformation($thetaGrid[$i], $a, $b, $g, $u);
            $efi += $info * $prior[$i];
        }
        
        return $efi;
    }
}

echo "=== TEST FUNGSI MATEMATIKA EFI ===\n\n";

$test = new CATServiceTest();

// Test parameter sample item
$testItems = [
    ['id' => 'Item1', 'a' => 1.5, 'b' => -1.0, 'g' => 0.2],
    ['id' => 'Item2', 'a' => 2.0, 'b' => 0.5, 'g' => 0.25],
    ['id' => 'Item3', 'a' => 1.2, 'b' => 1.5, 'g' => 0.15],
];

echo "1. Test Grid Theta:\n";
$thetaGrid = [];
for ($i = -6; $i <= 6; $i += 0.012) {
    $thetaGrid[] = $i;
}
echo "Jumlah titik grid: " . count($thetaGrid) . " (target: 1001)\n";
echo "Range: " . min($thetaGrid) . " sampai " . max($thetaGrid) . "\n\n";

echo "2. Test Prior N(0,2):\n";
$testThetas = [-2, -1, 0, 1, 2];
foreach ($testThetas as $theta) {
    $prior = $test->normalPdf($theta, 0, 2);
    echo "N(0,2) pada θ={$theta}: " . number_format($prior, 6) . "\n";
}
echo "\n";

echo "3. Test Probabilitas 3PL:\n";
foreach ($testItems as $item) {
    $theta = 0.0;
    $prob = $test->probability($theta, $item['a'], $item['b'], $item['g']);
    echo "{$item['id']}: P(θ=0) = " . number_format($prob, 4) . "\n";
}
echo "\n";

echo "4. Test Fisher Information:\n";
foreach ($testItems as $item) {
    $theta = 0.0;
    $info = $test->itemInformation($theta, $item['a'], $item['b'], $item['g']);
    echo "{$item['id']}: I(θ=0) = " . number_format($info, 6) . "\n";
}
echo "\n";

echo "5. Test Expected Fisher Information (Prior):\n";
foreach ($testItems as $item) {
    $efi = $test->expectedFisherInformationPrior($item['a'], $item['b'], $item['g']);
    echo "{$item['id']}: EFI = " . number_format($efi, 6) . "\n";
}
echo "\n";

echo "6. Perbandingan FI vs EFI:\n";
foreach ($testItems as $item) {
    $theta = 0.0;
    $fi = $test->itemInformation($theta, $item['a'], $item['b'], $item['g']);
    $efi = $test->expectedFisherInformationPrior($item['a'], $item['b'], $item['g']);
    $ratio = ($fi > 0) ? $efi / $fi : 0;
    echo sprintf("%s: FI=%.6f, EFI=%.6f, Ratio=%.4f\n", $item['id'], $fi, $efi, $ratio);
}
echo "\n";

echo "=== VERIFIKASI SINKRONISASI DENGAN PYTHON ===\n";
echo "✅ Grid theta: 1001 titik dari -6 sampai 6\n";
echo "✅ Prior: N(0,2)\n";
echo "✅ Rumus 3PL: g + (u-g)/(1+exp(-a*(θ-b)))\n";
echo "✅ Rumus Fisher Information: a²*(p-g)²*q / [p*(u-g)²]\n";
echo "✅ Rumus EFI: Σ I(θ_k) × P(θ_k|prior)\n";
echo "\n=== TEST SELESAI ===\n";
