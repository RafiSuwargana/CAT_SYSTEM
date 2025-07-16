<?php

namespace App\Http\Controllers;

use App\Services\CATService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class CATController extends Controller
{
    protected $catService;

    public function __construct(CATService $catService)
    {
        $this->catService = $catService;
    }

    /**
     * Start a new test session
     */
    public function startTest(): JsonResponse
    {
        try {
            $result = $this->catService->startSession();
            return response()->json($result);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Submit response and get next item
     */
    public function submitResponse(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'session_id' => 'required|string',
                'item_id' => 'required|string',
                'answer' => 'required|integer|in:0,1',
                'response_duration_seconds' => 'integer|min:0'
            ]);

            $result = $this->catService->submitResponse(
                $request->session_id,
                $request->item_id,
                $request->answer,
                $request->response_duration_seconds ?? 0
            );

            return response()->json($result);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get session history
     */
    public function getSessionHistory(string $sessionId): JsonResponse
    {
        try {
            $result = $this->catService->getSessionHistory($sessionId);
            return response()->json($result);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
