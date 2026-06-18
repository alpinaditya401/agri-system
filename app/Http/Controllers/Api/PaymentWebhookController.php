<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function __construct(private readonly OrderService $orderService)
    {
    }

    public function handle(Request $request): JsonResponse
    {
        // TODO: Validate webhook signature from payment gateway
        // $signature = $request->header('X-Payment-Signature');
        // if (!$this->verifySignature($signature, $request->getContent())) {
        //     return response()->json(['error' => 'Invalid signature'], 401);
        // }

        $payload = $request->all();

        $orderNumber = $payload['order_number'] ?? $payload['order_id'] ?? null;
        $paymentReference = $payload['transaction_id'] ?? $payload['reference'] ?? null;
        $status = $payload['status'] ?? $payload['transaction_status'] ?? null;
        $method = $payload['payment_type'] ?? $payload['method'] ?? 'unknown';

        if (!$orderNumber || !$paymentReference) {
            Log::warning('Payment webhook received with missing data', $payload);
            return response()->json(['error' => 'Missing required fields'], 422);
        }

        if ($status === 'settlement' || $status === 'capture' || $status === 'success') {
            try {
                $order = $this->orderService->confirmPayment($orderNumber, $paymentReference, $method);
                return response()->json([
                    'success' => true,
                    'order_number' => $order->order_number,
                ]);
            } catch (\RuntimeException $e) {
                Log::error('Payment webhook processing failed', [
                    'order_number' => $orderNumber,
                    'error' => $e->getMessage(),
                ]);
                return response()->json(['error' => $e->getMessage()], 400);
            }
        }

        Log::info('Payment webhook received non-success status', [
            'order_number' => $orderNumber,
            'status' => $status,
        ]);

        return response()->json(['message' => 'Webhook processed, payment not settled.']);
    }
}
