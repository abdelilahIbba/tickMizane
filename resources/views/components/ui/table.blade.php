@props([
    'headers' => [],
    'striped' => true,
])

<div class="overflow-x-auto rounded-xl border border-gray-700">
    <table class="w-full text-left">
        <thead class="bg-gray-800 border-b border-gray-700">
            <tr>
                @foreach($headers as $header)
                    <th class="px-6 py-4 text-sm font-semibold text-gray-300 uppercase tracking-wider">
                        {{ $header }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-700">
            {{ $slot }}
        </tbody>
    </table>
</div>
