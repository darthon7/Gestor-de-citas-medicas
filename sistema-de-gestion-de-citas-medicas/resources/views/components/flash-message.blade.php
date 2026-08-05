@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 d-flex align-items-center mb-4" role="alert">
        <i data-lucide="check-circle" class="me-2 flex-shrink-0"></i>
        <div>{{ session('success') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 d-flex align-items-center mb-4" role="alert">
        <i data-lucide="alert-triangle" class="me-2 flex-shrink-0"></i>
        <div>{{ session('error') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('warning'))
    <div class="alert alert-warning alert-dismissible fade show shadow-sm border-0 d-flex align-items-center mb-4" role="alert">
        <i data-lucide="alert-circle" class="me-2 flex-shrink-0"></i>
        <div>{{ session('warning') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
        <div class="d-flex align-items-center fw-bold mb-1">
            <i data-lucide="alert-triangle" class="me-2 flex-shrink-0"></i>
            <span>Por favor corrige los siguientes errores:</span>
        </div>
        <ul class="mb-0 ps-4 small">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
