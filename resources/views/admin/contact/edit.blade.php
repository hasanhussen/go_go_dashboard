@extends('admin.layouts.master')

@section('content')

<style>
    /* 🔥 تنسيق خاص للحقول لتكون أوضح */
    .form-control {
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 0.95rem;
        background: #fafafa;
        border: 1.5px solid #ddd;
        transition: 0.2s;
    }

    .form-control:focus {
        border-color: #dc3545;
        box-shadow: 0 0 7px rgba(220, 53, 69, 0.25);
        background: #fff;
    }

    .card {
        border-radius: 12px;
        box-shadow: 0 6px 18px rgba(0,0,0,0.06);
    }

    .card-header {
        border-radius: 12px 12px 0 0 !important;
    }

    .form-label {
        font-weight: 600;
    }
</style>

<div class="container py-4">
    @include('admin.partials.alerts')

    <div class="card">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0">إعدادات من نحن و تواصل معنا</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.contact.update') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">الوصف</label>
                    <textarea name="description" class="form-control" rows="4">{{ $info->description ?? '' }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">رقم الهاتف</label>
                        <input type="text" name="phone" value="{{ $info->phone ?? '' }}" class="form-control">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">البريد الإلكتروني</label>
                        <input type="email" name="email" value="{{ $info->email ?? '' }}" class="form-control">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">العنوان</label>
                        <input type="text" name="address" value="{{ $info->address ?? '' }}" class="form-control">
                    </div>
                </div>

                <hr>

                <h6 class="fw-bold mb-3">حسابات السوشال ميديا</h6>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Facebook</label>
                        <input type="text" name="facebook" value="{{ $info->facebook ?? '' }}" class="form-control">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Instagram</label>
                        <input type="text" name="instagram" value="{{ $info->instagram ?? '' }}" class="form-control">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">WhatsApp</label>
                        <input type="text" name="whatsapp" value="{{ $info->whatsapp ?? '' }}" class="form-control">
                    </div>
                </div>

                <button type="submit" class="btn btn-danger w-100 mt-3" style="padding: 12px; font-size: 1rem; border-radius: 10px;">
                    حفظ التعديلات
                </button>

            </form>
        </div>
    </div>
</div>

@endsection
