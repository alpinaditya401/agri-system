<?php

namespace App\Services;

class PaymentSettingsService
{
    public function __construct(private readonly AppSettingService $settings)
    {
    }

    public function gateway(): string
    {
        return strtolower((string) $this->settings->get('payment.gateway', config('services.payment.gateway', 'midtrans')));
    }

    public function webhookGateway(): string
    {
        return strtolower((string) $this->settings->get('payment.webhook_gateway', config('services.payment.webhook_gateway', 'midtrans')));
    }

    public function midtransServerKey(): ?string
    {
        return $this->settings->get('midtrans.server_key', config('services.midtrans.server_key'));
    }

    public function midtransClientKey(): ?string
    {
        return $this->settings->get('midtrans.client_key', config('services.midtrans.client_key'));
    }

    public function midtransIsProduction(): bool
    {
        return filter_var(
            $this->settings->get('midtrans.is_production', config('services.midtrans.is_production', false)),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    public function midtransSnapUrl(): string
    {
        $url = $this->settings->get(
            'midtrans.snap_url',
            config('services.midtrans.snap_url')
        );

        if (!filled($url)) {
            $url = 'https://app.sandbox.midtrans.com';
        }

        return rtrim((string) $url, '/');
    }

    public function xenditCallbackToken(): ?string
    {
        return $this->settings->get('xendit.callback_token', config('services.xendit.callback_token'));
    }

    public function isMidtransConfigured(): bool
    {
        return filled($this->midtransServerKey());
    }

    public function hasStored(string $key): bool
    {
        return $this->settings->hasFilled($key);
    }

    public function formValues(): array
    {
        return [
            'payment_gateway' => $this->gateway(),
            'payment_webhook_gateway' => $this->webhookGateway(),
            'midtrans_is_production' => $this->midtransIsProduction(),
            'midtrans_snap_url' => $this->midtransSnapUrl(),
            'midtrans_server_key_saved' => $this->hasStored('midtrans.server_key'),
            'midtrans_client_key_saved' => $this->hasStored('midtrans.client_key'),
            'xendit_callback_token_saved' => $this->hasStored('xendit.callback_token'),
        ];
    }

    public function update(array $data): void
    {
        $this->settings->set('payment.gateway', $data['payment_gateway']);
        $this->settings->set('payment.webhook_gateway', $data['payment_webhook_gateway']);
        $this->settings->set('midtrans.is_production', !empty($data['midtrans_is_production']) ? '1' : '0');
        $this->settings->set('midtrans.snap_url', $data['midtrans_snap_url'] ?: 'https://app.sandbox.midtrans.com');

        foreach ([
            'midtrans_server_key' => 'midtrans.server_key',
            'midtrans_client_key' => 'midtrans.client_key',
            'xendit_callback_token' => 'xendit.callback_token',
        ] as $input => $key) {
            if (array_key_exists($input, $data) && filled($data[$input])) {
                $this->settings->set($key, $data[$input], true);
            }
        }
    }
}
