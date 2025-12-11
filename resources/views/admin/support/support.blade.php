@extends('admin.layouts.master')

@section('content')
<div class="container-fluid py-4">
     @include('admin.partials.alerts')
    <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold">📨 إدارة الشكاوى</h4>
    <div>
        <a href="{{ route('admin.supports.closed') }}" class="btn btn-outline-danger"> 🗃️ عرض الأرشيف </a>
    </div>
</div>
        <form class="d-flex" method="GET" action="{{ url()->current() }}">
            <select name="status" class="form-select me-2" onchange="this.form.submit()">
                <option value="all">كل الحالات</option>
                <option value="open" {{ request('status')=='open' ? 'selected' : '' }}>مفتوحة</option>
                <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>قيد المعالجة</option>
                <option value="closed" {{ request('status')=='closed' ? 'selected' : '' }}>مغلقة</option>
            </select>
            <input type="hidden" name="search" value="{{ request('search') }}">
        </form>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            @if($supports->isEmpty())
                <div class="text-center text-muted py-5">
                    <h5>😴 لا توجد شكاوى حالياً</h5>
                </div>
            @else
            <div class="table-responsive" id="support-table">
                <table class="table align-middle">
                    <thead class="table-light">
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
                        @foreach ($supports as $complaint)
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

            <div class="mt-3">
                {{-- {{ $supports->links() }} --}}
                {{ $supports->appends(request()->query())->links('pagination::bootstrap-5') }}

            </div>
            @endif
        </div>
    </div>
</div>
@endsection


