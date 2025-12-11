@extends('admin.layouts.master')

@section('content')
<div class="container py-3">
    {{-- رسائل النجاح والتحذير --}}
    @include('admin.partials.alerts')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0 text-danger">🎟️ Coupons Management</h4>
        <a href="{{ route('coupons.create') }}" class="btn btn-info">
            <i class="bi bi-plus-lg"></i> Add New Coupon
        </a>
    </div>

    {{-- 🔹 فلترة الحالات --}}
    <form method="GET" class="mb-3">
        <div class="rounded border-0 p-2 bg-light">
            <label class="fw-bold d-block mb-1">⚡ Status Filter:</label>
            @php
                $statuses = [
                    'inactive' => 'Inactive',
                    'active' => 'Active',
                    'not_started' => 'Not Started',
                    'expired' => 'Expired',
                    'exhausted' => 'Exhausted',
                ];
            @endphp

            @foreach($statuses as $key => $label)
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox"
                           name="statuses[]" value="{{ $key }}"
                           {{ is_array(request('statuses')) && in_array($key, request('statuses')) ? 'checked' : '' }}
                           onchange="this.form.submit()">
                    <label class="form-check-label">{{ $label }}</label>
                </div>
            @endforeach
        </div>
    </form>

    {{-- 🔹 جدول الكوبونات --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped mb-0 align-middle">
                <thead class="table-light">
                    <tr>
            
        <th>Name</th>
        <th class="text-center">Discount</th>
        <th class="text-center">Usage Limit</th>
        <th class="text-center">Applies To</th>
        <th class="text-center">Start Date</th>
        <th class="text-center">End Date</th>
        <th class="text-center">Status</th>
        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($coupons as $coupon)
                        <tr>
                            
                            <td class="text-wrap"><span class="badge bg-secondary" >{{ $coupon->name }}</span></td>
                            <td class="text-center">
    {{ $coupon->discount }}
</td>
<td class="text-center">
    {{ $coupon->count }}
</td>
<td class="text-center">
    {{ ucfirst(str_replace('_', ' ', $coupon->details)) }}
</td>
                            {{-- تاريخ البدء --}}
                            <td class="text-center">
                                {{ $coupon->valid_from ? \Carbon\Carbon::parse($coupon->valid_from)->format('Y-m-d') : '-' }}
                                <button type="button" class="btn btn-sm btn-link text-primary p-0 border-0"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editDateModal-{{ $coupon->id }}">
                                    <i class="bi bi-calendar text-secondary" style="font-size: 12px;"></i>
                                </button>
                            </td>

                            {{-- تاريخ الانتهاء --}}
                            <td class="text-center">
                                {{ $coupon->valid_to ? \Carbon\Carbon::parse($coupon->valid_to)->format('Y-m-d') : '-' }}
                                <button type="button" class="btn btn-sm btn-link text-primary p-0 border-0"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editDateModal-{{ $coupon->id }}">
                                    <i class="bi bi-calendar text-secondary" style="font-size: 12px;"></i>
                                </button>
                            </td>

                            {{-- الحالة --}}
                            <td class="text-center">
                                @php
                                    $statusColors = [
                                        'active' => 'success',
                                        'inactive' => 'secondary',
                                        'not_started' => 'info',
                                        'expired' => 'warning',
                                        'exhausted' => 'dark',
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$coupon->status] ?? 'secondary' }}">
                                    {{ ucfirst($coupon->status) }}
                                </span>
                            </td>

                          {{-- الأزرار --}}
<td class="d-flex gap-1" style="justify-content: center;">

    {{-- عرض --}}
    {{-- تفعيل / إلغاء --}}
    <form action="{{ route('coupons.toggle', $coupon->id) }}" method="POST" class="d-inline">
        @csrf
        <button class="btn btn-sm btn-warning d-flex align-items-center" title="Toggle Active">
            <i class="bi bi-power me-1"></i> {{ $coupon->status === 'inactive' ? 'Activate' : 'Deactivate' }}
        </button>
    </form>

    {{-- تعديل --}}
    <a href="{{ route('coupons.edit', $coupon->id) }}" class="btn btn-sm btn-info d-flex align-items-center" title="Edit">
        <i class="bi bi-pencil me-1"></i> Edit
    </a>

    {{-- حذف --}}
    <form action="{{ route('coupons.destroy', $coupon->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this coupon?')">
        @csrf @method('DELETE')
        <button class="btn btn-sm btn-danger d-flex align-items-center" title="Delete">
            <i class="bi bi-trash me-1"></i> Delete
        </button>
    </form>
</td>

                        </tr>

                        {{-- نافذة تعديل التواريخ --}}
                        <div class="modal fade" id="editDateModal-{{ $coupon->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('coupons.updateDate', $coupon->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Update Dates for {{ $coupon->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Start Date:</label>
                                                <input type="datetime-local" name="valid_from" class="form-control"
                                                       value="{{ $coupon->valid_from }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">End Date:</label>
                                                <input type="datetime-local" name="valid_to" class="form-control"
                                                       value="{{ $coupon->valid_to }}">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-success">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-3">No coupons found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $coupons->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
