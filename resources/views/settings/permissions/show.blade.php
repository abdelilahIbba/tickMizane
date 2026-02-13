<x-layout.app title="Permissions - {{ $user->name }}">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="permissionsManager()">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-white">Permissions - {{ $user->name }}</h1>
            <p class="text-gray-400 mt-1">{{ $user->username }} • {{ ucfirst($user->role) }}</p>
        </div>
        <div class="flex gap-3">
            <form action="{{ route('settings.permissions.grant-all', $user) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                        onclick="return confirm('Accorder toutes les permissions?')">
                    Tout autoriser
                </button>
            </form>
            <form action="{{ route('settings.permissions.revoke-all', $user) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                        onclick="return confirm('Révoquer toutes les permissions?')">
                    Tout révoquer
                </button>
            </form>
            <a href="{{ route('settings.permissions.index') }}" class="px-4 py-2 bg-gray-800 text-gray-300 rounded-lg hover:bg-gray-700 transition-colors">
                Retour
            </a>
        </div>
    </div>

    @if($user->role === 'admin')
    <div class="mb-6 p-4 bg-blue-500/10 border border-blue-500/30 rounded-lg">
        <div class="flex items-center gap-3">
            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-blue-300">Les administrateurs ont automatiquement accès à toutes les permissions.</p>
        </div>
    </div>
    @endif

    <!-- Permissions Matrix -->
    <form @submit.prevent="savePermissions">
        <div class="bg-gray-900/50 backdrop-blur-sm rounded-lg border border-gray-800 shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-800">
                    <thead class="bg-gray-950/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider sticky left-0 bg-gray-950/50">
                                Module
                            </th>
                            @foreach($actions as $actionKey => $actionName)
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">
                                {{ $actionName }}
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        @foreach($permissionMatrix as $moduleKey => $moduleData)
                        <tr class="hover:bg-gray-800/30">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white sticky left-0 bg-gray-900/50">
                                {{ $moduleData['name'] }}
                            </td>
                            @foreach($actions as $actionKey => $actionName)
                            <td class="px-4 py-4 text-center">
                                <input type="checkbox" 
                                       name="permissions[{{ $moduleKey }}][{{ $actionKey }}]" 
                                       value="1"
                                       {{ $moduleData['actions'][$actionKey]['allowed'] ?? false ? 'checked' : '' }}
                                       x-model="permissions['{{ $moduleKey }}']['{{ $actionKey }}']"
                                       class="w-5 h-5 text-blue-600 bg-gray-800 border-gray-700 rounded focus:ring-blue-500"
                                       @if($user->role === 'admin') disabled @endif>
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($user->role !== 'admin')
        <div class="mt-6 flex justify-end">
            <button type="submit" 
                    class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors"
                    :disabled="saving"
                    x-text="saving ? 'Enregistrement...' : 'Enregistrer les permissions'">
            </button>
        </div>
        @endif
    </form>
</div>

<script>
function permissionsManager() {
    return {
        permissions: @json(collect($permissionMatrix)->mapWithKeys(fn($module, $key) => [
            $key => collect($module['actions'])->mapWithKeys(fn($action, $actionKey) => [$actionKey => $action['allowed']])
        ])),
        saving: false,

        savePermissions() {
            this.saving = true;

            fetch('{{ route("settings.permissions.update", $user) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    permissions: this.permissions
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Permissions mises à jour avec succès');
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
        }
    }
}
</script>
</x-layout.app>
