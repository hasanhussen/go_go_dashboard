@extends('admin.layouts.master')

@section('content')
<div class="container" id="order-details-container">
{{-- 🔹 رسائل النجاح أو التحذير --}}
    @include('admin.partials.alerts')
    <h2 class="mb-4 text-danger">تفاصيل الطلب #{{ $order->id }}</h2>

    {{-- المنتجات --}}
    <div class="card mb-4">
        <div class="card-header bg-light">
            <strong>المنتجات</strong>
        </div>
        <div class="card-body">
            @foreach($order->carts as $item)
                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                    <div>
                        <strong>{{ $item->meal->name?? '' }}</strong> × {{ $item->quantity??'' }}
                        @if($item->variant)
                        <br>
                        <small class="text-primary">
                            المقاس: {{ $item->variant->name ?? '' }}
                        </small>
                        @endif
                        <br>
                        <small class="text-muted">المتجر: {{ $item->meal->store->name ?? '' }}</small>

                        {{-- الإضافات --}}
                        @if($item->additionalItems && count($item->additionalItems) > 0)
                            <ul class="mt-2 ps-3 text-muted" style="font-size: 14px;">
                                @foreach($item->additionalItems as $add)
                                    <li>{{ $add->name ?? '' }} × {{ $add->pivot->quantity ?? '' }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    <div class="text-danger">${{ $item->price ?? '' }}</div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- تفاصيل الطلب --}}
    <div class="card mb-4">
        <div class="card-header bg-light">
            <strong>تفاصيل الطلب</strong>
        </div>
        <div class="card-body">
            <p><strong>العنوان:</strong> {{ $order->address }}</p>
            <p><strong>سعر المنتجات:</strong> ${{ $order->price }}</p>
            <p>
    <strong>سعر التوصيل:</strong> 
    $<span id="delivery-price">{{ $order->delivery_price }}</span>
</p>

@if($order->status != '4') {{-- غير مكتمل --}}
<form action="{{ route('orders.reduceDelivery', $order->id) }}" method="POST" class="d-flex gap-2 align-items-center mb-3">
    @csrf
    <input type="number" name="new_delivery_price" class="form-control" 
           placeholder="أدخل سعر أقل" min="0" max="{{ $order->delivery_price }}" step="0.01" style="width:150px;">
    <button type="submit" class="btn btn-warning">خفض التوصيل</button>
</form>
@endif

            <p><strong>الكوبون:</strong> {{ $order->coupon ?? 'لا يوجد' }}</p>
            <p><strong>طريقة الدفع:</strong> {{ $order->payment_method }}</p>
             <p><strong>ملاحظات:</strong> {{ $order->notes??'' }}</p>
            <p><strong>الحالة:</strong> 
               <span class="badge text-white
                   @if($order->status=='0') bg-secondary
                   @elseif($order->status=='1') bg-primary
                   @elseif($order->status=='2') bg-warning
                   @elseif($order->status=='3') bg-info
                   @elseif($order->status=='4') bg-success
                   @else bg-dark @endif">
                   @if ($order->deleted_at == null)
                   @if($order->status=='0') قيد الموافقة
                   @elseif ($order->status=='1') قيد التحضير
                   @elseif ($order->status=='2') في الطريق
                   @elseif ($order->status=='3') في الموقع
                   @elseif ($order->status=='4') تم التوصيل
                   @elseif ($order->status=='5') تم إرساله إلى عمال التوصيل
                   @else غير معروف @endif
        
                  @endif
               </span>
            </p>
        </div>
    </div>

    {{-- سجل تغييرات الحالة --}}
<div class="card mb-4">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h5 class="mb-0">📜 سجل تغييرات الحالة</h5>
    </div>
    <div class="card-body">
        @if ($order->logs->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered text-center align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>من الحالة</th>
                            <th>إلى الحالة</th>
                            <th>المسؤول</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->logs->sortByDesc('created_at') as $index => $log)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    @switch($log->old_status)
                                        @case('0') قيد الموافقة @break
                                        @case('1') قيد التحضير @break
                                        @case('2') في الطريق @break
                                        @case('3') في الموقع @break
                                        @case('4') تم التوصيل @break
                                        @case('5') تم الإرسال إالى عمال التوصيل @break
                                        @default - 
                                    @endswitch
                                </td>
                                <td>
                                    @switch($log->new_status)
                                        @case('0') قيد الموافقة @break
                                        @case('1') قيد التحضير @break
                                        @case('2') في الطريق @break
                                        @case('3') في الموقع @break
                                        @case('4') تم التوصيل @break
                                        @case('5') تم الإرسال إالى عمال التوصيل @break
                                        @default - 
                                    @endswitch
                                </td>
                                <td>{{ $log->admin?->name ?? '-' }}</td>
                                <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted text-center mb-0">لا يوجد تغييرات مسجلة بعد.</p>
        @endif
    </div>
</div>


    @if ($order->deleted_at != null)
        <div class="alert alert-danger">
            <strong>ملاحظة:</strong> تم {{ $order->status == '0' ? 'رفض' : 'حذف' }} هذا الطلب في {{ $order->deleted_at ?? $order->updated_at }}. السبب: {{ $order->delete_reason ?? '-' }}
        </div>  
    @else
    {{-- أزرار التحكم --}}
    @if ($order->status == '0')
    <div class="d-flex gap-2">
       <form action="{{ route('orders.accept', $order->id) }}" method="POST" class="w-50">
            @csrf @method('PATCH')
            <input type="hidden" name="last_seen_at" value="{{ $order->updated_at }}">
            <button type="submit" class="btn btn-success w-100">
                <i class="bi bi-check"></i> قبول الطلب
            </button>
       </form> 
       {{-- <form action="{{ route('orders.destroy', $order->id) }}" method="POST" class="w-50" 
             onsubmit="return confirm('هل أنت متأكد أنك تريد رفض هذا الطلب؟');">
            @csrf @method('PATCH')
            <input type="hidden" name="last_seen_at" value="{{ $order->updated_at }}">
            <button type="submit" class="btn btn-danger w-100">
                <i class="bi bi-x"></i> رفض الطلب
            </button>
       </form> --}}

       <button type="button" class="btn btn-sm btn-danger" 
       data-bs-toggle="modal" data-bs-target="#deleteModal{{ $order->id }}">
                {{' رفض الطلب'}}
              </button>

              <!-- Modal -->
              <div class="modal fade" id="deleteModal{{ $order->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                  <form action="{{ route('orders.destroy', $order->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="last_seen_at" value="{{ $order->updated_at }}">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">{{ $order->status == '0' ? 'سبب رفض المتجر' : 'سبب حذف المتجر' }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body">
                        <label>اختر سبب سريع:</label>
                        <select class="form-select mb-2" name="quick_reason" aria-placeholder="اختر سبب سريع" onchange="document.getElementById('custom_reason_{{ $order->id }}').value = this.value">
                          @if ($order->status == '0')
                          <option value="" selected hidden>اختر سبب سريع</option>
                            <option value="مخالفة الشروط">مخالفة الشروط</option>
                            <option value="محتوى غير مناسب">محتوى غير مناسب</option>
                            <option value="معلومات غير صحيحة">معلومات غير صحيحة</option>
                          @endif
                        </select>

                        <label>أو اكتب سبب مخصص:</label>
                        <textarea id="custom_reason_{{ $order->id }}" name="delete_reason" class="form-control" placeholder="اكتب السبب هنا..." required></textarea>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-danger">{{ $order->status == '0' ? 'رفض' : 'حذف' }}</button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
    </div>
    @endif
    @endif
</div>
@endsection

@section('scripts')
{{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
<script>
let lastOrderHtml = $('#order-details-container').html(); 

setInterval(updateOrderDetails, 300000);

function updateOrderDetails() {
  $.ajax({
    url: "{{ route('orders.show', $order->id) }}",
    type: 'GET',
    dataType: 'html',
    success: function(response) {
      const newBody = $(response).find('#order-details-container').html();

      if (lastOrderHtml !== newBody) {
        $('#order-details-container').html(newBody);
        lastOrderHtml = newBody;
        showNotice('🔄 تم تحديث تفاصيل الطلب');
      } else {
        console.log('لا يوجد تحديثات جديدة');
      }
    }
  });
}

function showNotice(msg, info = false) {
  const notice = $('<div>').text(msg).css({
    position: 'fixed',
    top: '20px',
    right: '20px',
    background: info ? '#6c757d' : '#007bff',
    color: 'white',
    padding: '10px 20px',
    borderRadius: '8px',
    boxShadow: '0 0 10px rgba(0,0,0,0.3)',
    zIndex: 9999,
    fontWeight: 'bold'
  }).hide().appendTo('body').fadeIn(300);

  setTimeout(() => notice.fadeOut(500, () => notice.remove()), 3000);
}
</script>
@endsection
