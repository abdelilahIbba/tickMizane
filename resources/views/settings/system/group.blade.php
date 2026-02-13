<x-layout.app title="Paramètres - {{ $groupInfo['label'] }}">
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="settingsForm()">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <nav class="flex items-center gap-2 text-sm text-gray-400 mb-2">
                <a href="{{ route('settings.system.index') }}" class="hover:text-white">Paramètres</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-white">{{ $groupInfo['label'] }}</span>
            </nav>
            <h1 class="text-3xl font-bold text-white">{{ $groupInfo['label'] }}</h1>
            <p class="text-gray-400 mt-1">{{ $groupInfo['description'] }}</p>
        </div>
        <div class="flex gap-3">
            <button type="button" @click="resetToDefault()" 
                    class="px-4 py-2 bg-gray-800 text-gray-300 rounded-lg hover:bg-gray-700 transition-colors">
                Réinitialiser
            </button>
            <a href="{{ route('settings.system.index') }}" class="px-4 py-2 bg-gray-800 text-gray-300 rounded-lg hover:bg-gray-700 transition-colors">
                Retour
            </a>
        </div>
    </div>

    <!-- Settings Sidebar + Form -->
    <div class="flex gap-6">
        <!-- Sidebar Navigation -->
        <div class="w-48 flex-shrink-0">
            <nav class="space-y-1">
                @foreach($groups as $key => $grp)
                <a href="{{ route('settings.system.group', $key) }}" 
                   class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ $key === $group ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }} transition-colors">
                    {{ $grp['label'] }}
                </a>
                @endforeach
            </nav>
        </div>

        <!-- Settings Form -->
        <div class="flex-1">
            <form @submit.prevent="saveSettings()" class="bg-gray-900/50 backdrop-blur-sm rounded-lg border border-gray-800 shadow-lg">
                <div class="p-6 space-y-6">
                    @foreach($settings as $key => $setting)
                    <div class="setting-field">
                        @if($setting['type'] === 'boolean')
                        <label class="flex items-center justify-between">
                            <div>
                                <span class="block text-sm font-medium text-white">{{ $setting['label'] }}</span>
                                @if(!empty($setting['description']))
                                <span class="block text-xs text-gray-500 mt-1">{{ $setting['description'] }}</span>
                                @endif
                            </div>
                            <div class="relative">
                                <input type="checkbox" 
                                       name="settings[{{ $key }}]"
                                       x-model="formData['{{ $key }}']"
                                       class="sr-only peer">
                                <div @click="formData['{{ $key }}'] = !formData['{{ $key }}']"
                                     :class="formData['{{ $key }}'] ? 'bg-blue-600' : 'bg-gray-700'"
                                     class="w-11 h-6 rounded-full cursor-pointer transition-colors"></div>
                                <div @click="formData['{{ $key }}'] = !formData['{{ $key }}']"
                                     :class="formData['{{ $key }}'] ? 'translate-x-5' : 'translate-x-0'"
                                     class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform cursor-pointer"></div>
                            </div>
                        </label>
                        @elseif($setting['type'] === 'integer' || $setting['type'] === 'float')
                        <label class="block">
                            <span class="block text-sm font-medium text-white mb-2">{{ $setting['label'] }}</span>
                            @if(!empty($setting['description']))
                            <span class="block text-xs text-gray-500 mb-2">{{ $setting['description'] }}</span>
                            @endif
                            <input type="number" 
                                   name="settings[{{ $key }}]"
                                   x-model="formData['{{ $key }}']"
                                   step="{{ $setting['type'] === 'float' ? '0.01' : '1' }}"
                                   class="w-full px-4 py-2 bg-gray-800 border border-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </label>
                        @else
                        <label class="block">
                            <span class="block text-sm font-medium text-white mb-2">{{ $setting['label'] }}</span>
                            @if(!empty($setting['description']))
                            <span class="block text-xs text-gray-500 mb-2">{{ $setting['description'] }}</span>
                            @endif
                            @if(str_contains($key, 'header') || str_contains($key, 'footer') || str_contains($key, 'address'))
                            <textarea name="settings[{{ $key }}]"
                                      x-model="formData['{{ $key }}']"
                                      rows="3"
                                      class="w-full px-4 py-2 bg-gray-800 border border-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                            @else
                            <input type="text" 
                                   name="settings[{{ $key }}]"
                                   x-model="formData['{{ $key }}']"
                                   class="w-full px-4 py-2 bg-gray-800 border border-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @endif
                        </label>
                        @endif
                    </div>
                    @endforeach
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 bg-gray-950/30 border-t border-gray-800 flex justify-between items-center">
                    <p class="text-sm text-gray-500" x-show="saved" x-transition>
                        <span class="text-green-400">✓</span> Paramètres enregistrés
                    </p>
                    <div x-show="!saved"></div>
                    <button type="submit" 
                            class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
                            :disabled="saving">
                        <span x-show="!saving">Enregistrer</span>
                        <span x-show="saving">Enregistrement...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function settingsForm() {
    return {
        formData: @json(collect($settings)->mapWithKeys(fn($s, $k) => [$k => $s['value']])),
        saving: false,
        saved: false,

        saveSettings() {
            this.saving = true;
            this.saved = false;

            fetch('{{ route("settings.system.update", $group) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    settings: this.formData
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.saved = true;
                    setTimeout(() => this.saved = false, 3000);
                } else {
                    alert('Erreur: ' + (data.message || 'Une erreur est survenue'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erreur de connexion');
            })
            .finally(() => {
                this.saving = false;
            });
        },

        resetToDefault() {
            if (!confirm('Réinitialiser tous les paramètres de cette catégorie aux valeurs par défaut?')) {
                return;
            }

            fetch('{{ route("settings.system.reset", $group) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Erreur: ' + (data.message || 'Une erreur est survenue'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erreur de connexion');
            });
        }
    }
}
</script>
</x-layout.app>
