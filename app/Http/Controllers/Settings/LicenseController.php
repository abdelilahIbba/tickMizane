<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Services\LicenseService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class LicenseController extends Controller
{
    public function __construct(
        private readonly LicenseService $licenseService,
    ) {
    }

    public function index()
    {
        $this->licenseService->markExpiredLicenses();

        $licenses = License::query()
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('settings.licenses.index', [
            'licenses' => $licenses,
            'periods' => $this->licenseService->periods(),
            'current' => $this->licenseService->current(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_name' => ['required', 'string', 'max:255'],
            'period' => ['required', Rule::in(array_keys($this->licenseService->periods()))],
            'notes' => ['nullable', 'string', 'max:1000'],
            'activate_now' => ['nullable', 'boolean'],
        ]);

        $license = $this->licenseService->create(
            $validated['client_name'],
            $validated['period'],
            $validated['notes'] ?? null,
        );

        if ($request->boolean('activate_now')) {
            $this->licenseService->activate($license);

            return redirect()
                ->route('settings.licenses.index')
                ->with('success', 'Licence créée et activée pour '.$license->client_name.'.');
        }

        return redirect()
            ->route('settings.licenses.index')
            ->with('success', 'Licence créée. Elle restera inactive jusqu\'à activation explicite.');
    }

    public function activate(License $license)
    {
        try {
            $this->licenseService->activate($license);
        } catch (InvalidArgumentException $exception) {
            return redirect()
                ->route('settings.licenses.index')
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('settings.licenses.index')
            ->with('success', 'Licence activée jusqu\'au '.$license->fresh()->expires_at?->format('d/m/Y H:i').'.');
    }

    public function revoke(License $license)
    {
        $this->licenseService->revoke($license);

        return redirect()
            ->route('settings.licenses.index')
            ->with('success', 'Licence révoquée.');
    }
}
