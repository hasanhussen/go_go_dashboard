@extends('admin.layouts.master')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h4 class="fw-bold mb-3 text-danger">✏️ Edit Coupon: {{ $coupon->code }}</h4>

{{-- رسائل النجاح والتحذير --}}
    @include('admin.partials.alerts')
            <form action="{{ route('coupons.update',$coupon) }}" method="POST">
                @csrf
                @method('PATCH') {{-- مهم لتحديث --}}
                
                <div class="row">
                    <div class="col-md-6">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" value="{{ $coupon->name }}" required>
                    </div>

                    <div class="col-md-6">
                        <label>Discount Type</label>
                        <select name="details" class="form-select" required>
                            <option value="products_price" {{ $coupon->details === 'products_price' ? 'selected' : '' }}>products_price</option>
                            <option value="delivery_price" {{ $coupon->details === 'delivery_price' ? 'selected' : '' }}>delivery_price</option>
                             <option value="total_price" {{ $coupon->details === 'total_price' ? 'selected' : '' }}>total_price</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label>Discount Value</label>
                        <input type="number" name="discount" class="form-control" value="{{ $coupon->discount }}" required>
                    </div>

                    <div class="col-md-6">
                        <label>Usage Limit</label>
                        <input type="number" name="count" class="form-control" value="{{ $coupon->count }}" required>
                    </div>

                 <div class="col-md-6">
    <label>Start Date</label>
    <input type="date" name="valid_from" class="form-control"
           value="{{ $coupon->valid_from ? $coupon->valid_from->format('Y-m-d') : '' }}" required>
</div>

<div class="col-md-6">
    <label>End Date</label>
    <input type="date" name="valid_to" class="form-control"
           value="{{ $coupon->valid_to ? $coupon->valid_to->format('Y-m-d') : '' }}" required>
</div>

 {{-- ملاحظات --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Notes (Optional)</label>
                        <input type="text" name="notes" class="form-control" placeholder="Add extra notes if needed" value="{{ $coupon->notes }}">
                    </div>

                    
                </div>

                <button class="btn btn-danger mt-3 px-4">Update Coupon</button>
            </form>
        </div>
    </div>
</div>
@endsection
