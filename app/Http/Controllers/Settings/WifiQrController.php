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

        return view('settings.wifi-qr.index', compact('wifi'));
    }

    /**
     * Save Wi-Fi settings.
     */
    public function save(Request $request)
    {
        $request->validate([
            'ssid'     => 'required|string|max:100',
            'password' => 'nullable|string|max:100',
            'security' => 'required|in:WPA,WEP,nopass',
        ]);

        Setting::setValue('wifi_ssid',     $request->ssid,                'wifi', 'string');
        Setting::setValue('wifi_password', $request->password ?? '',      'wifi', 'string');
        Setting::setValue('wifi_security', $request->security,            'wifi', 'string');

        return back()->with('success', 'Paramètres Wi-Fi enregistrés avec succès.');
    }
}
