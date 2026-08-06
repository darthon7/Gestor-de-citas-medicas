@if (session('success'))
    <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-3 shadow-sm">
        <span class="material-symbols-outlined text-emerald-600">check_circle</span>
        <div class="text-sm font-medium flex-1">{{ session('success') }}</div>
    </div>
@endif

@if (session('error'))
    <div class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center gap-3 shadow-sm">
        <span class="material-symbols-outlined text-rose-600">error</span>
        <div class="text-sm font-medium flex-1">{{ session('error') }}</div>
    </div>
@endif

@if (session('warning'))
    <div class="mb-4 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 flex items-center gap-3 shadow-sm">
        <span class="material-symbols-outlined text-amber-600">warning</span>
        <div class="text-sm font-medium flex-1">{{ session('warning') }}</div>
    </div>
@endif

@if ($errors->any())
    <div class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 shadow-sm space-y-2">
        <div class="flex items-center gap-2 font-semibold text-sm text-rose-900">
            <span class="material-symbols-outlined text-rose-600">warning</span>
            <span>Por favor corrige los siguientes errores:</span>
        </div>
        <ul class="list-disc list-inside text-xs space-y-1 text-rose-700 pl-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
