@extends('admin.layouts.master')

@section('content')
<div class="container mt-4">
     @include('admin.partials.alerts')
    <a href="{{ route('admin.supports.index') }}" class="btn btn-secondary mb-3">
        ← العودة إلى قائمة الطلبات
    </a>

    <div class="card shadow">
        <div class="card-header bg-danger text-white">
            تفاصيل الطلب
        </div>
        <div class="card-body">
            <h5 class="card-title">العنوان: {{ $support->subject }}</h5>
            <p><strong>النوع:</strong> {{ ucfirst($support->type) }}</p>
            <p><strong>الحالة الحالية:</strong> {{ ucfirst($support->status) }}</p>

            <p><strong>الرسالة:</strong></p>
            <p>{{ $support->message }}</p>

            @if($support->image)
                <p><strong>الصورة المرفقة:</strong></p>
                <img src="{{ asset('storage/' . $support->image) }}" alt="صورة الطلب" style="max-width: 300px; max-height: 300px;">
            @endif

            <hr>

            <h5>معلومات المستخدم</h5>
            <p><strong>الاسم:</strong> {{ $support->user->name ?? 'غير متوفر' }}</p>
            <p><strong>البريد الإلكتروني:</strong> {{ $support->user->email ?? 'غير متوفر' }}</p>
            <p><strong>الدور:</strong> {{ $support->role }}</p>

            <hr>

            {{-- تحديث الحالة --}}
            <form action="{{ route('admin.supports.updateStatus', $support->id) }}" method="POST" class="mb-3">
                @csrf
                <div class="form-group">
                    <label>تغيير الحالة:</label>
                    <select name="status" class="form-select w-25">
                        <option value="open" {{ $support->status == 'open' ? 'selected' : '' }}>Open</option>
                        <option value="in_progress" {{ $support->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="resolved" {{ $support->status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="closed" {{ $support->status == 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary mt-2">تحديث الحالة</button>
            </form>

            <hr>

            {{-- الرد على الطلب --}}
            <form action="{{ route('admin.supports.reply', $support->id) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>الرد:</label>
                    <textarea name="reply" class="form-control" rows="5" placeholder="اكتب الرد هنا...">{{ $support->reply }}</textarea>
                </div>
                <button type="submit" class="btn btn-success mt-2">إرسال الرد</button>
            </form>
        </div>
    </div>
</div>
@endsection
