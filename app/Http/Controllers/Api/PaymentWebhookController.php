<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\PaymentSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly PaymentSettingsService $paymentSettings
    )
    {
    }

    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();
        $gateway = $this->paymentSettings->webhookGateway();

        if (!$this->hasConfiguredWebhookSecret($gateway, $request, $payload)) {
            Log::critical('Payment webhook rejected because signature secret is not configured', [
                'gateway' => $gateway,
            ]);

            return response()->json(['error' => 'Webhook signature secret is not configured'], 503);
        }

        if (!$this->verifySignature($gateway, $request, $payload)) {
            Log::warning('Payment webhook rejected because signature is invalid', [
                'gateway' => $gateway,
                'order_number' => $this->extractOrderNumber($payload),
            ]);

            return response()->json(['error' => 'Invalid webhook signature'], 401);
        }

        $orderNumber = $this->extractOrderNumber($payload);
        $paymentReference = $this->firstPayloadValue($payload, [
            'transaction_id',
            'payment_id',
            'invoice_id',
            'reference',
            'id',
            'data.id',
            'data.payment_id',
            'data.payment_request_id',
            'paymentCapture.value.data.payment_id',
            'paymentCapture.value.data.payment_request_id',
            'paymentRequest.value.data.id',
        ]);
        $status = $this->extractStatus($payload);
        $method = $this->firstPayloadValue($payload, [
            'payment_type',
            'method',
            'channel_code',
            'data.channel_code',
            'paymentCapture.value.data.channel_code',
            'paymentRequest.value.data.channel_code',
        ]) ?? 'unknown';

        if (!$orderNumber || !$paymentReference) {
            Log::warning('Payment webhook received with missing required fields', [
                'gateway' => $gateway,
                'has_order_number' => (bool) $orderNumber,
                'has_payment_reference' => (bool) $paymentReference,
                'status' => $status,
            ]);

            return response()->json(['error' => 'Missing required fields'], 422);
        }

        if ($this->isSuccessfulPaymentStatus($status)) {
            try {
                $this->assertAmountMatchesOrder($orderNumber, $this->extractPaymentAmount($payload));

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
            'gateway' => $gateway,
            'order_number' => $orderNumber,
            'status' => $status,
        ]);

        return response()->json(['message' => 'Webhook processed, payment not settled.']);
    }

    private function hasConfiguredWebhookSecret(string $gateway, Request $request, array $payload): bool
    {
        if ($gateway === 'auto') {
            if ($this->looksLikeMidtransPayload($payload)) {
                return filled($this->paymentSettings->midtransServerKey());
            }

            if ($request->headers->has('x-callback-token')) {
                return filled($this->paymentSettings->xenditCallbackToken());
            }

            return false;
        }

        return match ($gateway) {
            'midtrans' => filled($this->paymentSettings->midtransServerKey()),
            'xendit' => filled($this->paymentSettings->xenditCallbackToken()),
            default => false,
        };
    }

    private function verifySignature(string $gateway, Request $request, array $payload): bool
    {
        if ($gateway === 'auto') {
            if ($this->looksLikeMidtransPayload($payload)) {
                return $this->verifyMidtransSignature($payload);
            }

            return $this->verifyXenditCallbackToken($request);
        }

        return match ($gateway) {
            'midtrans' => $this->verifyMidtransSignature($payload),
            'xendit' => $this->verifyXenditCallbackToken($request),
            default => false,
        };
    }

    private function verifyMidtransSignature(array $payload): bool
    {
        $serverKey = (string) $this->paymentSettings->midtransServerKey();
        $signature = $payload['signature_key'] ?? null;
        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;

        if (!$signature || !$orderId || !$statusCode || !$grossAmount || !$serverKey) {
            return false;
        }

        $expected = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        return hash_equals($expected, (string) $signature);
    }

    private function verifyXenditCallbackToken(Request $request): bool
    {
        $callbackToken = (string) $this->paymentSettings->xenditCallbackToken();
        $headerToken = (string) $request->header('x-callback-token', '');

        return $callbackToken !== '' && $headerToken !== '' && hash_equals($callbackToken, $headerToken);
    }

    private function looksLikeMidtransPayload(array $payload): bool
    {
        return array_key_exists('signature_key', $payload)
            || array_key_exists('transaction_status', $payload)
            || array_key_exists('gross_amount', $payload);
    }

    private function extractOrderNumber(array $payload): ?string
    {
        return $this->firstPayloadValue($payload, [
            'order_number',
            'order_id',
            'external_id',
            'reference_id',
            'data.reference_id',
            'data.external_id',
            'paymentCapture.value.data.reference_id',
            'paymentRequest.value.data.reference_id',
        ]);
    }

    private function extractStatus(array $payload): ?string
    {
        return $this->firstPayloadValue($payload, [
            'status',
            'transaction_status',
            'data.status',
            'paymentCapture.value.data.status',
            'paymentRequest.value.data.status',
        ]);
    }

    private function extractPaymentAmount(array $payload): ?float
    {
        $amount = $this->firstPayloadValue($payload, [
            'gross_amount',
            'amount',
            'paid_amount',
            'request_amount',
            'data.amount',
            'data.paid_amount',
            'data.request_amount',
            'paymentCapture.value.data.amount',
            'paymentCapture.value.data.request_amount',
            'paymentRequest.value.data.amount',
            'paymentRequest.value.data.request_amount',
        ]);

        return $amount === null ? null : (float) $amount;
    }

    private function firstPayloadValue(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = data_get($payload, $key);

            if ($value !== null && $value !== '') {
                return (string) $value;
            }
        }

        return null;
    }

    private function isSuccessfulPaymentStatus(?string $status): bool
    {
        return in_array(strtolower((string) $status), [
            'settlement',
            'capture',
            'success',
            'succeeded',
            'paid',
            'completed',
        ], true);
    }

    private function assertAmountMatchesOrder(string $orderNumber, ?float $paidAmount): void
    {
        if ($paidAmount === null) {
            throw new \RuntimeException('Nominal pembayaran dari gateway tidak tersedia.');
        }

        $order = Order::where('order_number', $orderNumber)->first();

        if (!$order) {
            throw new \RuntimeException('Order tidak ditemukan.');
        }

        if ((int) round((float) $order->total_amount) !== (int) round($paidAmount)) {
            throw new \RuntimeException('Nominal pembayaran tidak sesuai dengan total order.');
        }
    }
}
