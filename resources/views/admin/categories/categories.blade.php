@extends('admin.layouts.master')

@section('content')
<div class="container py-3">
    {{-- رسائل النجاح والتحذير --}}
    @include('admin.partials.alerts')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0 text-danger">categories Management</h4>
        <a href="{{ route('categories.create') }}" class="btn btn-info">
            <i class="bi bi-plus-lg"></i> Add New Category
        </a>
    </div>

    

   
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped mb-0 align-middle">
                <thead class="table-light">
                    <tr>
            
        <th>Name</th>
        <th class="text-center">image</th>
        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            
                            <td style="padding-left: 10px;">
                <h6 class="mb-0 text-sm" style="margin-left: 10px;">
                  {{-- <a href="{{ route('categories.show', $category->id) }}"> --}}
                    {{ $category->type }}
                {{-- </a> --}}
                </h6>
</td>
<td class="text-center">
                <img src="{{ $category->image ? asset('storage/'.$category->image) : asset('assets/img/default-avatar.png') }}"
                  class="avatar avatar-sm me-3 bstore-radius-lg" alt="{{ $category->name }}" style="width: 80px; height: 80px;">
              </td>
                            

                          {{-- الأزرار --}}
<td class="d-flex gap-1" style="justify-content: center; padding-top: 20px;">
   

    {{-- تعديل --}}
    <a href="{{ route('categories.edit', $category->id) }}" class="btn btn-sm btn-info d-flex align-items-center" title="Edit">
        <i class="bi bi-pencil me-1"></i> Edit
    </a>

    {{-- حذف --}}
<form action="{{ route('categories.destroy', $category->id) }}" method="POST" class="d-inline delete-category-form">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-danger d-flex align-items-center" title="Delete">
        <i class="bi bi-trash me-1"></i> Delete
    </button>
</form>
</td>
 </tr>

                        

                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-3">No categories found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    
</div>
@endsection
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const deleteForms = document.querySelectorAll('.delete-category-form');

    deleteForms.forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            Swal.fire({
                title: 'هل أنت متأكد من الحذف؟',
                text: "⚠️ عند حذف هذا الصنف سيتم حذف جميع المتاجر المرتبطة به أيضًا!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'نعم، احذف!',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
</script>
@endsection
