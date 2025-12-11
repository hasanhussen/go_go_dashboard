@php
    $isReject = $order->status == '0';
    $btnClass = $isReject ?  'btn-danger':'btn-outline-danger w-100';
    $btnIcon = $isReject ? 'bi-x-circle' : 'bi-trash';
    $btnText = $isReject ? 'Reject' : 'Delete';
@endphp

<button type="button" class="btn btn-sm {{ $btnClass }}" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $order->id }}">
    <i class="bi {{ $btnIcon }} me-1"></i> {{ $btnText }}
</button>


<div class="modal fade" id="deleteModal{{ $order->id }}" tabindex="-1" aria-hidden="true">
<div class="modal-dialog">
<form action="{{ $order->status=='0'?route('orders.destroy',$order->id):route('orders.destroy',$order->id) }}" method="POST">
    @csrf
    @method('DELETE')
    <input type="hidden" name="last_seen_at" value="{{ $order->updated_at }}">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">{{ $order->status=='0'?'سبب رفض الطلب':'سبب حذف الطلب' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <label>اختر سبب سريع:</label>
            <select class="form-select mb-2" name="quick_reason" aria-placeholder="اختر سبب سريع"
                onchange="document.getElementById('custom_reason_{{ $order->id }}').value = this.value">
                @if($order->status=='0')
                <option value="" selected hidden>اختر سبب سريع</option>
                <option value="مخالفة الشروط">مخالفة الشروط</option>
                <option value="محتوى غير مناسب">محتوى غير مناسب</option>
                <option value="معلومات غير صحيحة">معلومات غير صحيحة</option>
                @else
                <option value="" selected hidden>اختر سبب سريع</option>
                <option value="بناء على طلب الزبون">بناء على طلب الزبون</option>
                <option value="مخالفة الشروط">مخالفة الشروط</option>
                @endif
            </select>

            <label>أو اكتب سبب مخصص:</label>
            <textarea id="custom_reason_{{ $order->id }}" name="delete_reason" class="form-control" placeholder="اكتب السبب هنا..." required></textarea>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
            <button type="submit" class="btn btn-danger">{{ $order->status=='0'?'رفض':'حذف' }}</button>
        </div>
    </div>
</form>
</div>
</div>
