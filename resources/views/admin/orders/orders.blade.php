@extends('admin.layouts.master')

@section('content')
<div class="container-fluid py-2">
    {{-- رسائل النجاح والتحذير --}}
    @include('admin.partials.alerts')

    {{-- 🔹 Pending Orders --}}
    <div class="card my-4">
        <div class="card-header bg-gradient-dark shadow-dark">
            <h6 class="text-white ps-3">Orders Table</h6>
        </div>
        <div class="card-body px-0 pb-2">
            <div class="d-flex mb-3 px-3">
                <form action="{{ url()->current() }}" method="GET" class="d-flex flex-wrap gap-3 px-3 mb-3">
                    {{-- الاحتفاظ بالبحث --}}
                    <input type="hidden" name="search" value="{{ request('search') }}">

                    {{-- فلترة الحالة --}}
                    <div class="rounded border-0 p-2 me-3" style="background-color: rgba(210, 209, 209, 0);">
                        <label class="fw-bold d-block mb-1">⚡ Statuses:</label>
                        @php $statuses = ['0'=>'Pending','1'=>'In preparation','2'=>'On the way','3'=>'On site']; @endphp
                        @foreach($statuses as $key => $label)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="statuses[]" value="{{ $key }}"
                                    {{ is_array(request('statuses')) && in_array($key, request('statuses')) ? 'checked' : '' }}
                                    onchange="this.form.submit()">
                                <label class="form-check-label">{{ $label }}</label>
                            </div>
                        @endforeach
                    </div>

                    {{-- فلترة الدفع --}}
                    <div class="rounded border-0 p-2" style="background-color: rgba(210, 209, 209, 0);">
                        <label class="fw-bold d-block mb-1">⚡ Payment Method:</label>
                        @php $payment_method = ['0'=>'cash','1'=>'card']; @endphp
                        @foreach($payment_method as $key => $label)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="payment_method[]" value="{{ $label }}"
                                    {{ is_array(request('payment_method')) && in_array($label, request('payment_method')) ? 'checked' : '' }}
                                    onchange="this.form.submit()">
                                <label class="form-check-label">{{ $label }}</label>
                            </div>
                        @endforeach
                    </div>
                </form>
            </div>

            {{-- جدول الطلبات المعلقة --}}
            <div id="pending-orders-table">
                @include('admin.orders.partials.pending_table', ['pendingOrders'=>$pendingOrders])
            </div>
        </div>
    </div>

    {{-- 🔹 Completed Orders --}}
    <div class="card my-4">
        <div class="card-header bg-gradient-success shadow-dark">
            <h6 class="text-white ps-3">Completed Orders</h6>
        </div>
        <div class="card-body px-0 pb-2" id="completed-orders-table">
            @include('admin.orders.partials.completed_table', ['completedOrders'=>$completedOrders])
        </div>
    </div>

    {{-- 🔹 Rejected Orders --}}
    <div class="card my-4">
        {{-- <div class="card-header bg-gradient-danger shadow-dark">
            <h6 class="text-white ps-3">Rejected Orders</h6>
        </div> --}}
        <div class="card-header bg-gradient-danger shadow-dark d-flex justify-content-between align-items-center">
  <h6 class="text-white ps-3 mb-0">Rejected Orders</h6>
  @if($rejectedOrders->count() > 0)
  <button type="button" class="btn btn-sm btn-light text-danger fw-bold" 
          data-bs-toggle="modal" data-bs-target="#emptyTrashModal">
    <i class="bi bi-trash3 me-1"></i> Empty Trash
  </button>
  @endif
</div>

        <div class="card-body px-0 pb-2" id="rejected-orders-table">
            @include('admin.orders.partials.rejected_table', ['rejectedOrders'=>$rejectedOrders])
        </div>
    </div>
</div>

<!-- Empty Trash Modal -->
<div class="modal fade" id="emptyTrashModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form action="{{ route('orders.emptyTrash') }}" method="POST">
      @csrf
      @method('DELETE')
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title">تأكيد إفراغ السلة</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center">
          ⚠️ هل أنت متأكد أنك تريد حذف جميع الطلبات المرفوضة او المحذوفة نهائيًا؟  
          <br>
          <strong>لا يمكن التراجع عن هذه العملية!</strong>
        </div>
        <div class="modal-footer justify-content-center">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
          <button type="submit" class="btn btn-danger">نعم، احذف الكل</button>
        </div>
      </div>
    </form>
  </div>
</div>

@endsection

@section('scripts')
{{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
<script>

    function getCurrentFilters() {
    return $('form').serialize(); // بيجمع كل الفيلدات داخل الفورم
}

function setupAutoUpdate(tableId, routeUrl, noticeText, interval = 300000) {
    let lastHtml = $(tableId).html();
    setInterval(() => {
        $.get(routeUrl, getCurrentFilters(), function(response){
            const newHtml = $(response).find(tableId).html();
            if(lastHtml !== newHtml){
                $(tableId).html(newHtml);
                lastHtml = newHtml;
                showNotice(noticeText);
            }
        });
    }, interval);
}

function showNotice(msg, info=false){
    const notice = $('<div>').text(msg).css({
        position:'fixed', top:'20px', right:'20px',
        background: info?'#6c757d':'#28a745',
        color:'white', padding:'10px 20px', borderRadius:'8px',
        boxShadow:'0 0 10px rgba(0,0,0,0.3)', zIndex:9999,
        fontWeight:'bold'
    }).hide().appendTo('body').fadeIn(300);
    setTimeout(()=>notice.fadeOut(500,()=>notice.remove()), 2000);
}

// تفعيل التحديث التلقائي لكل جدول
setupAutoUpdate('#pending-orders-table', "{{ route('orders') }}", '🔄 تم تحديث الطلبات المعلقة');
setupAutoUpdate('#completed-orders-table', "{{ route('orders') }}", '🔄 تم تحديث الطلبات المكتملة');
setupAutoUpdate('#rejected-orders-table', "{{ route('orders') }}", '🔄 تم تحديث الطلبات المرفوضة');
</script>
@endsection
