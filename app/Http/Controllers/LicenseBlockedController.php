<?php

namespace App\Http\Controllers;

use App\Services\LicenseService;
use App\Support\SuperAdmin;
use Illuminate\Support\Facades\Auth;

class LicenseBlockedController extends Controller
{
    public function __invoke(LicenseService $licenseService)
    {
        if ($licenseService->hasActiveLicense()) {
            return redirect()->route('dashboard');
        }

        if (SuperAdmin::is(Auth::user())) {
            return redirect()->route('settings.licenses.index');
        }

        return view('license.blocked', [
            'user' => Auth::user(),
            'message' => $licenseService->clientBlockMessage(),
            'expiredLicense' => $licenseService->latestExpired(),
        ]);
    }
}
