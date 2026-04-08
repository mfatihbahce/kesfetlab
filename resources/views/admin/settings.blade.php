@extends('admin.layout')

@section('title', 'Ayarlar - ' . ($brandName ?? 'Kesfet LAB'))
@section('page-title', 'Ayarlar')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="table-card">
            <div class="table-header">
                <i class="fas fa-sliders-h me-2"></i>
                Marka Ayarlari
            </div>
            <div class="p-4">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="brand_name" class="form-label">Marka Adi</label>
                        <input
                            type="text"
                            id="brand_name"
                            name="brand_name"
                            class="form-control @error('brand_name') is-invalid @enderror"
                            value="{{ old('brand_name', $settings['brand_name']) }}"
                            required
                        >
                        @error('brand_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="brand_logo" class="form-label">Logo Yukle</label>
                        <input
                            type="file"
                            id="brand_logo"
                            name="brand_logo"
                            class="form-control @error('brand_logo') is-invalid @enderror"
                            accept=".jpg,.jpeg,.png,.webp,.svg"
                        >
                        <small class="text-muted">Maksimum 5MB. Onerilen oran: yatay logo.</small>
                        @error('brand_logo')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>
                        Ayarlari Kaydet
                    </button>
                </form>
            </div>
        </div>

    </div>

    <div class="col-lg-4">
        <div class="table-card">
            <div class="table-header">
                <i class="fas fa-eye me-2"></i>
                Onizleme
            </div>
            <div class="p-4 text-center">
                @if(!empty($settings['brand_logo_path']))
                    <img src="{{ asset(ltrim($settings['brand_logo_path'], '/')) }}" alt="Logo" style="max-width: 100%; max-height: 120px;">
                @else
                    <div class="text-muted">Logo yuklenmemis.</div>
                @endif
                <div class="mt-3 fw-bold">{{ $settings['brand_name'] }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
