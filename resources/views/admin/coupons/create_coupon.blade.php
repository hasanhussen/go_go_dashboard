@extends('admin.layouts.master')

@section('content')
<div class="container py-4">
    <h4 class="fw-bold mb-4 text-danger">🎟️ Create New Coupon</h4>

   {{-- رسائل النجاح والتحذير --}}
    @include('admin.partials.alerts')

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('coupons.store') }}" method="POST">
                @csrf

                <div class="row g-3">
                    {{-- الاسم --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Coupon Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Enter coupon name" value="{{ old('name') }}" required>
                    </div>

                    {{-- قيمة الخصم --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Discount Value</label>
                        <input type="number" name="discount" min="5" max="100" step="1" class="form-control" placeholder="Enter discount value" value="{{ old('discount') }}" required min="1">
                    </div>

                    {{-- عدد الاستخدامات --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Usage Limit</label>
                        <input type="number" name="count" class="form-control" placeholder="Enter number of allowed uses" value="{{ old('count') }}" required min="1">
                    </div>

                    {{-- نوع الخصم --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Discount Applies To</label>
                        <select name="details" class="form-select" required>
                            <option value="total_price" {{ old('details') == 'total_price' ? 'selected' : '' }}>Total Price</option>
                            <option value="products_price" {{ old('details') == 'products_price' ? 'selected' : '' }}>Products Price</option>
                            <option value="delivery_price" {{ old('details') == 'delivery_price' ? 'selected' : '' }}>Delivery Price</option>
                        </select>
                    </div>

                   

                    {{-- تاريخ البداية والنهاية --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Start Date</label>
                        <input type="datetime-local" name="valid_from" class="form-control" value="{{ old('valid_from') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">End Date</label>
                        <input type="datetime-local" name="valid_to" class="form-control" value="{{ old('valid_to') }}" required>
                    </div>

                     {{-- ملاحظات --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Notes (Optional)</label>
                        <input type="text" name="notes" class="form-control" placeholder="Add extra notes if needed" value="{{ old('notes') }}">
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <a href="{{ route('coupons.index') }}" class="btn btn-secondary me-2">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-check2-circle"></i> Save Coupon
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
