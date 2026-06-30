<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PaymentSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentSettingsController extends Controller
{
    public function __construct(private readonly PaymentSettingsService $paymentSettings)
    {
    }

    public function edit(): View
    {
        return view('admin.settings.payment', [
            'settings' => $this->paymentSettings->formValues(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payment_gateway' => ['required', 'in:demo,midtrans'],
            'payment_webhook_gateway' => ['required', 'in:midtrans,xendit,auto'],
            'midtrans_is_production' => ['nullable', 'boolean'],
            'midtrans_snap_url' => ['nullable', 'url', 'max:255'],
            'midtrans_server_key' => ['nullable', 'string', 'max:255'],
            'midtrans_client_key' => ['nullable', 'string', 'max:255'],
            'xendit_callback_token' => ['nullable', 'string', 'max:255'],
        ]);

        $this->paymentSettings->update([
            ...$validated,
            'midtrans_is_production' => $request->boolean('midtrans_is_production'),
        ]);

        return redirect()
            ->route('admin-master.payment-settings.edit')
            ->with('success', 'Pengaturan payment gateway berhasil disimpan.');
    }
}
