<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class WifiQrController extends Controller
{
    /**
     * Show the Wi-Fi QR code generation settings page.
     */
    public function index()
    {
        $wifi = [
            'ssid'     => Setting::getValue('wifi_ssid', ''),
            'password' => Setting::getValue('wifi_password', ''),
            'security' => Setting::getValue('wifi_security', 'WPA'),
        ];

        $publicBaseUrl = Setting::getValue('public_base_url', rtrim((string) config('app.url'), '/'));

        return view('settings.wifi-qr.index', compact('wifi', 'publicBaseUrl'));
    }

    /**
     * Save Wi-Fi settings.
     */
    public function save(Request $request)
    {
        $request->validate([
            'ssid'     => 'nullable|string|max:100',
            'password' => 'nullable|string|max:100',
            'security' => 'nullable|in:WPA,WEP,nopass',
            'public_base_url' => 'nullable|string|max:255',
        ]);

        // Public URL/IP is saved as entered by the user (no forced rewrite).
        $rawPublicUrl = trim((string) $request->input('public_base_url', ''));
        if ($rawPublicUrl !== '') {
            Setting::setValue('public_base_url', $rawPublicUrl, 'wifi', 'string');
        }

        // Keep Wi-Fi fields optional so IP updates can be saved independently.
        if ($request->filled('ssid')) {
            Setting::setValue('wifi_ssid', (string) $request->input('ssid'), 'wifi', 'string');
        }

        if ($request->has('password')) {
            Setting::setValue('wifi_password', (string) $request->input('password', ''), 'wifi', 'string');
        }

        if ($request->filled('security')) {
            Setting::setValue('wifi_security', (string) $request->input('security'), 'wifi', 'string');
        }

        return back()->with('success', 'Paramètres Wi-Fi enregistrés avec succès.');
    }
}
