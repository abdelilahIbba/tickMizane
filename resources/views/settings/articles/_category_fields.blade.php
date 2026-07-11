{{-- Category form fields – shared between create and edit forms --}}
<div>
    <label class="block text-xs font-medium text-gray-400 mb-1.5">Nom <span class="text-red-400">*</span></label>
    <input type="text" name="name" required maxlength="255"
           class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-xl text-white text-sm
                  focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 placeholder-gray-600"
           placeholder="Ex : Tajines">
</div>

<div>
    <label class="block text-xs font-medium text-gray-400 mb-1.5">Description</label>
    <textarea name="description" rows="2" maxlength="500"
              class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-xl text-white text-sm
                     focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 placeholder-gray-600 resize-none"
              placeholder="Description courte..."></textarea>
</div>

<div>
    <label class="block text-xs font-medium text-gray-400 mb-1.5">Statut</label>
    <select name="status"
            class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-xl text-white text-sm
                   focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500">
        <option value="active">Actif</option>
        <option value="archived">Archivé</option>
    </select>
</div>

<div class="space-y-3">
    <label class="block text-xs font-medium text-gray-400">Image</label>

    {{-- URL input --}}
    <input type="url" name="image_url" id="catUrlInput" maxlength="2048"
           class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-xl text-white text-sm
                  focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 placeholder-gray-600"
           placeholder="https://... (URL de l'image)">

    {{-- File input --}}
    <div class="relative">
        <label class="flex items-center gap-2 px-3 py-2.5 bg-gray-800 border border-dashed border-gray-600
                      rounded-xl cursor-pointer hover:border-gray-500 transition-colors text-sm text-gray-500
                      hover:text-gray-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span id="catFileLabel">Choisir un fichier…</span>
            <input type="file" name="image_file" id="catFileInput" accept="image/*"
                   class="absolute inset-0 opacity-0 cursor-pointer w-full"
                   onchange="document.getElementById('catFileLabel').textContent = this.files[0]?.name || 'Choisir un fichier…'">
        </label>
    </div>

    {{-- Preview --}}
    <div id="catImagePreview" class="hidden">
        <img id="catPreviewImg" src="" alt="Aperçu"
             class="h-32 w-full object-cover rounded-xl border border-gray-700"
             onerror="this.parentElement.classList.add('hidden')">
    </div>
</div>
