@extends('admin.layouts.master')

@section('content')
<div class="container py-4">
    <h4 class="fw-bold mb-4 text-danger">Edit Category</h4>

    {{-- رسائل النجاح والتحذير --}}
    @include('admin.partials.alerts')

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('categories.update', $category->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('Patch')

                <div class="row g-3">
                    {{-- الاسم / type --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Category Name</label>
                        <input type="text" name="type" class="form-control @error('type') is-invalid @enderror" placeholder="Enter category name" value="{{ old('type', $category->type) }}" required>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- الصورة الحالية + رفع جديدة --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Category Image</label>

                        @if($category->image)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $category->image) }}" alt="category image" id="currentImage" style="max-height:120px; border-radius:8px;">
                            </div>
                        @endif

                        <input type="file" name="image" id="imageInput" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                        <small class="text-muted">Allowed: JPG, PNG, JPEG, WEBP — Max 2MB</small>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        {{-- preview --}}
                        <div class="mt-2" id="previewContainer" style="display:none;">
                            <p class="mb-1 small">Preview:</p>
                            <img id="previewImage" style="max-height:120px; border-radius:8px;">
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <a href="{{ route('categories.index') }}" class="btn btn-secondary me-2">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-check2-circle"></i> Update Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- preview script --}}
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const imageInput = document.getElementById('imageInput');
    const previewContainer = document.getElementById('previewContainer');
    const previewImage = document.getElementById('previewImage');

    imageInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) {
            previewContainer.style.display = 'none';
            return;
        }

        // simple size check (client-side): 2MB
        if (file.size > 2 * 1024 * 1024) {
            alert('Image is larger than 2MB. Please choose a smaller file.');
            this.value = '';
            previewContainer.style.display = 'none';
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            previewImage.src = e.target.result;
            previewContainer.style.display = 'block';
        }
        reader.readAsDataURL(file);
    });
});
</script>
@endsection

@endsection
