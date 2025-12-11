@extends('admin.layouts.master')

@section('content')
<div class="container py-4">
    <h4 class="fw-bold mb-4 text-danger">Create New slider</h4>

    {{-- رسائل النجاح والتحذير --}}
    @include('admin.partials.alerts')

    <div class="card shadow-sm border-0">
        <div class="card-body">
            {{-- لازم نحط enctype لأن فيه رفع ملف --}}
            <form action="{{ route('sliders.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    {{-- الاسم --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">slider Name</label>
                        <input type="text" name="slider_title" class="form-control" placeholder="Enter slider name" value="{{ old('type') }}" required>
                    </div>

                    {{-- الصورة --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">slider Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*" id="imageInput">
                        <small class="text-muted">Allowed formats: JPG, PNG, JPEG — Max 2MB</small>

                        {{-- المعاينة --}}
                        <div class="mt-3">
                            <img id="previewImage" src="#" alt="Preview" class="rounded shadow-sm d-none" width="150">
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <a href="{{ route('sliders.index') }}" class="btn btn-secondary me-2">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-check2-circle"></i> Save slider
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection
@section('scripts')
{{-- سكربت معاينة الصورة --}}
<script>
    document.getElementById('imageInput').addEventListener('change', function(event) {
        const preview = document.getElementById('previewImage');
        const file = event.target.files[0];

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('d-none');
            }
            reader.readAsDataURL(file);
        } else {
            preview.src = '#';
            preview.classList.add('d-none');
        }
    });
</script>
@endsection