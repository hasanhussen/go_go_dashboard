@extends('admin.layouts.master')

@section('content')
<div class="container py-3">
    {{-- رسائل النجاح والتحذير --}}
    @include('admin.partials.alerts')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0 text-danger">sliders Management</h4>
        <a href="{{ route('sliders.create') }}" class="btn btn-info">
            <i class="bi bi-plus-lg"></i> Add New Slider
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
                    @forelse($sliders as $slider)
                        <tr>
                            
                            <td class="text-wrap" style="padding-left: 10px;">
                <h6 class="mb-0 text-sm" style="margin-left: 10px;">
                    {{ $slider->slider_title }}
                </h6>
</td>
<td class="text-center">
                <img src="{{ $slider->image ? asset('storage/'.$slider->image) : asset('assets/img/default-avatar.png') }}"
                  class="avatar avatar-sm me-3 bstore-radius-lg" alt="{{ $slider->name }}" style="width: 80px; height: 80px;">
              </td>
                            

                          {{-- الأزرار --}}
<td class="d-flex gap-1" style="justify-content: center; padding-top: 20px;">
   

    {{-- تعديل --}}
    <a href="{{ route('sliders.edit', $slider->id) }}" class="btn btn-sm btn-info d-flex align-items-center" title="Edit">
        <i class="bi bi-pencil me-1"></i> Edit
    </a>

    {{-- حذف --}}
<form action="{{ route('sliders.destroy', $slider->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this slider?')">
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
                            <td colspan="9" class="text-center text-muted py-3">No sliders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    
</div>
@endsection
