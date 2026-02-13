<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use Illuminate\Http\Request;

class SystemSettingsController extends Controller
{
    public function __construct(
        protected SettingService $settingService
    ) {}

    /**
     * Display settings index with all groups
     */
    public function index()
    {
        $groups = $this->settingService->getGroups();
        
        return view('settings.system.index', compact('groups'));
    }

    /**
     * Display settings for a specific group
     */
    public function showGroup(string $group)
    {
        $groups = $this->settingService->getGroups();
        
        if (!isset($groups[$group])) {
            abort(404, 'Groupe de paramètres non trouvé');
        }
        
        $settings = $this->settingService->getGroup($group);
        $groupInfo = $groups[$group];
        
        return view('settings.system.group', compact('group', 'settings', 'groupInfo', 'groups'));
    }

    /**
     * Update settings for a group
     */
    public function updateGroup(Request $request, string $group)
    {
        $groups = $this->settingService->getGroups();
        
        if (!isset($groups[$group])) {
            return response()->json([
                'success' => false,
                'message' => 'Groupe de paramètres non trouvé',
            ], 404);
        }
        
        $settings = $request->input('settings', []);
        
        // Convert checkbox values properly
        foreach ($settings as $key => $value) {
            if ($value === 'on') {
                $settings[$key] = true;
            } elseif ($value === '0' || $value === 'off') {
                $settings[$key] = false;
            }
        }
        
        $this->settingService->setMany($settings);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Paramètres enregistrés avec succès',
            ]);
        }
        
        return redirect()->route('settings.system.group', $group)
            ->with('success', 'Paramètres enregistrés avec succès');
    }

    /**
     * Reset a group to default values
     */
    public function resetGroup(Request $request, string $group)
    {
        $groups = $this->settingService->getGroups();
        
        if (!isset($groups[$group])) {
            return response()->json([
                'success' => false,
                'message' => 'Groupe de paramètres non trouvé',
            ], 404);
        }
        
        $this->settingService->resetGroup($group);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Paramètres réinitialisés aux valeurs par défaut',
            ]);
        }
        
        return redirect()->route('settings.system.group', $group)
            ->with('success', 'Paramètres réinitialisés aux valeurs par défaut');
    }
}
