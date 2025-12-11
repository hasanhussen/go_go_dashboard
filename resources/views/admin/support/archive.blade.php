@extends('admin.layouts.master')

@section('content')
<div class="container mt-4">
    <h4 class="mb-4">أرشيف الشكاوي</h4>

    @if($complaints->isEmpty())
        <div class="alert alert-info text-center">لا يوجد شكاوي مؤرشفة حالياً</div>
    @else
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                         <th class="text-center">رقم الشكوى</th>
                            <th class="text-center">المرسل</th>
                            <th class="text-center">البريد الإلكتروني</th>
                            <th class="text-center">نوع المستخدم</th>
                            <th class="text-center">الموضوع</th>
                            <th class="text-center">الحالة</th>
                            <th class="text-center">التاريخ</th>
                            <th class="text-center">خيارات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($complaints as $complaint)
                        <tr>
                            <td class="text-center">{{ $complaint->id }}</td>
                                <td class="text-center">{{ $complaint->user->name ?? '—' }}</td>
                                <td class="text-center">{{ $complaint->user->email ?? '—' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-info">{{ ucfirst($complaint->role ?? 'user') }}</span>
                                </td>
                                <td class="text-center">{{ Str::limit($complaint->subject, 40) }}</td>
                                <td class="text-center">
                                    @if($complaint->status == 'open')
                                        <span class="badge bg-success">مفتوحة</span>
                                    @elseif($complaint->status == 'pending')
                                        <span class="badge bg-warning text-dark">قيد المعالجة</span>
                                    @else
                                        <span class="badge bg-secondary">مغلقة</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $complaint->created_at->format('Y-m-d') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.supports.show', $complaint->id) }}" class="btn btn-sm btn-outline-primary">عرض</a>
                                    @if($complaint->status != 'closed')
                                        <form action="{{ route('admin.supports.close', $complaint->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('هل أنت متأكد من إغلاق الشكوى؟')">إغلاق</button>
                                        </form>
                                    @endif
                                </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <a href="{{ route('admin.supports.index') }}" class="btn btn-danger mt-3">⬅ الرجوع</a>
</div>
@endsection
