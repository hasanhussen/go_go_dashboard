@extends('admin.layouts.master')

@section('content')
<div class="container py-4" id="user-details-container">
  @include('admin.partials.alerts')
  <div class="card">
    <div class="card-body text-center">
      <img src="{{ $user->image ? asset('storage/'.$user->image) :  asset('assets/img/default-avatar.png') }}"
           class="rounded-circle mb-3" width="120" height="120" alt="{{ $user->name }}">
      <h4>{{ $user->name?? '-' }}</h4>
      <p>{{ $user->email?? '-' }}</p>
      <p>{{ $user->phone ?? 'لا يوجد رقم هاتف' }}</p>
       <p class="mb-1"><strong>الحالة: </strong>
            @if ($user->deleted_at != null)
              <span class="badge bg-gradient-danger">محذوف</span>
              <p class="mt-2 text-danger">تم حذف هذا المستخدم في {{ $user->deleted_at }}. السبب: {{ $user->delete_reason ?? '-' }}</p>
              
            @else

            @if($user->status == '1')
              <span class="badge bg-gradient-success">مفعل</span>
            @elseif($user->status == '0')
              <span class="badge bg-gradient-warning">قيد المراجعة</span>
            @else
              <span class="badge bg-gradient-secondary">محظور</span>
            @endif
            @endif
          </p>
    </div>
  </div>


  

  

  @if($isdelivery && $user->deliveryManOrders->count() > 0)
    <div class="card mt-4">
      <div class="card-header">
        <h5>الطلبات التي استلمها</h5>
      </div>
      <div class="card-body">
        <ul class="list-group">
          @foreach($user->deliveryManOrders as $deliveredOrder)
<li class="list-group-item d-flex justify-content-between align-items-start">
  <div class="d-flex flex-column">
    <a href="{{ route('orders.show', $deliveredOrder->id) }}">
      طلب رقم: {{ $deliveredOrder->id }}
    </a>

    {{-- السعر --}}
    @if(isset($deliveredOrder->total_before_discount) && $deliveredOrder->total_before_discount != $deliveredOrder->total_price)
      <div class="mt-1">
        <strong>المجموع بعد الخصم:</strong> ${{ $deliveredOrder->total_price }}
        <div class="text-danger" style="font-size: 0.9rem;">
          السعر قبل الخصم: <s>${{ $deliveredOrder->total_before_discount }}</s>
        </div>
      </div>
    @else
      <div class="mt-1">
        <strong>المجموع:</strong> ${{ $deliveredOrder->total_price }}
      </div>
    @endif

    {{-- حالة الطلب --}}
    <span class="badge text-white mt-2
      @if($deliveredOrder->status=='0') bg-secondary
      @elseif($deliveredOrder->status=='1') bg-success
      @elseif($deliveredOrder->status=='2') bg-warning
      @elseif($deliveredOrder->status=='3') bg-info
      @elseif($deliveredOrder->status=='4') bg-success
      @else bg-dark @endif">
      @if($deliveredOrder->status=='0') قيد الموافقة
      @elseif($deliveredOrder->status=='1') قيد التحضير
      @elseif($deliveredOrder->status=='2') في الطريق
      @elseif($deliveredOrder->status=='3') في الموقع
      @elseif($deliveredOrder->status=='4') تم التوصيل
      @elseif($deliveredOrder->status=='5')تم إرساله إلى عمال التوصيل
      @else غير معروف @endif
    </span>
  </div>

  <span class="badge bg-info text-white ms-3" style="font-size:0.85rem;">
    {{ $deliveredOrder->updated_at->format('Y-m-d') }}
  </span>
</li>



          @endforeach
        </ul>
         {{-- @else
        <p class="text-muted">لا يوجد طلبات مستلمة من قبل هذا العامل</p> --}}
      @endif
      </div>
    </div>

    <div class="card mt-4">
    <div class="card-header">
      <h5>طلباته الشخصية</h5>
    </div>
    <div class="card-body">
      @if($user->orders->count() > 0)
        <ul class="list-group">
          @foreach($user->orders as $order)
       
            <li class="list-group-item">
                 <a href="{{ route('orders.show', $order->id) }}">
   طلب رقم: {{ $order->id }} - المجموع: ${{ $order->total_price }}
  </a>
              
            </li>
          @endforeach
        </ul>
      @else
        <p class="text-muted">لا يوجد طلبات سابقة</p>
      @endif
    </div>
  </div>

  <div class="card mt-4">
    <div class="card-header">
        <h5>إرسال إشعار للمستخدم</h5>
    </div>
    <div class="card-body">

        <form action="{{ route('notifications.send', $user->id) }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-control">عنوان الإشعار</label>
                <input type="text" name="title" class="form-control border" required placeholder="مثال: تحديث على حسابك">
            </div>

            <div class="mb-3">
                <label class="form-control">نص الإشعار</label>
                <textarea name="body" class="form-control border" rows="3" required placeholder="محتوى الإشعار..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary">
                📩 إرسال الإشعار
            </button>
        </form>

    </div>
</div>
@can('add users')
<div class="card mt-4">
    <div class="card-header">
        <h5>تغيير دور المستخدم</h5>
    </div>

    <div class="card-body">
        <form action="{{ route('users.updateRole', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">الدور</label>
                <select name="role" class="form-control">
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button class="btn btn-primary mt-2" style="background:#003f8a;border-color:#003f8a;">💾 حفظ</button>

        </form>
    </div>
</div>
@endcan

</div>
@endsection

@section('scripts')
{{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
<script>
let lastUserHtml = $('#user-details-container').html(); 

setInterval(updateUserDetails, 3600000);

function updateUserDetails() {
  $.ajax({
    url: "{{ route('users.show', $user->id) }}",
    type: 'GET',
    dataType: 'html',
    success: function(response) {
      const newBody = $(response).find('#user-details-container').html();

      if (lastUserHtml !== newBody) {
        $('#user-details-container').html(newBody);
        lastUserHtml = newBody;
        showNotice('🔄 تم تحديث تفاصيل المستخدم');
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
