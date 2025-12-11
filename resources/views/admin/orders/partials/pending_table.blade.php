<table class="table align-items-center mb-0">
<thead>
<tr>
    <th class="text-center">ID</th>
    <th class="text-center">User</th>
    <th class="text-center">Payment method</th>
    <th class="text-center">Address</th>
    <th class="text-center">Delivery man</th>
    <th class="text-center">Total</th>
    <th class="text-center">Status</th>
    <th class="text-center">Actions</th>
</tr>
</thead>
<tbody>
@forelse($pendingOrders as $order)
<tr>
    <td class="text-center"><a href="{{ route('orders.show',$order->id) }}">{{ $order->id }}</a></td>
    <td class="text-center">
           <a href="{{ route('users.show', $order->user->id) }}">{{ $order->user->name ?? '-' }}</a>
    </td>
    <td class="text-center">
        @if($order->payment_method == 'card')
            <span class="badge bg-success text-white">card</span>
        @else
            <span class="badge bg-secondary text-white">cash</span>
        @endif
    </td>
    <td class="text-center" style="max-width: 180px; word-wrap: break-word;">
    {{ $order->address ?? '-' }}
    @if($order->linked_order_id)
       <span class="badge bg-warning text-white d-block mt-1" 
      style="white-space: normal; word-break: break-word; word-spacing: 2px;">
    {{$order->linked_order_id}} مرتبط مع طلب رقم
</span>

    @endif
</td>

    
    {{-- <td class="text-center">
    @if ($order->delivery)
        <a href="{{ route('users.show', $order->delivery->id) }}">
            {{ $order->delivery->name ?? '-' }}
        </a>
    @else
        غير محدد
    @endif
</td> --}}

<td class="text-center">
    <form action="{{ route('orders.assignDelivery', $order->id) }}" method="POST" class="d-inline">
        @csrf
        @method('PATCH')
        <div class="d-flex align-items-center justify-content-center gap-2">
            {{-- قائمة اختيار عامل التوصيل --}}
            <select name="delivery_id" class="form-select form-select-sm" style="width: 180px;" onchange="this.form.submit()">
    <option value="">غير محدد</option>
    @foreach($deliveryMen as $man)
        @php
            $activeOrders = $man->activeOrders;
            $busyText = $activeOrders->count() > 0 
                ? ' (مشغول بالطلبات: ' . $activeOrders->pluck('id')->join(', ') . ')'
                : '';
        @endphp

        <option 
            value="{{ $man->id }}" 
            {{ $order->delivery_id == $man->id ? 'selected' : '' }}>
            {{ $man->name }}{{ $busyText }}
        </option>
    @endforeach
</select>


            {{-- رابط ملف عامل التوصيل (إذا تم التعيين) --}}
            @if($order->delivery)
<a href="{{ route('users.show', $order->delivery->id) }}" 
   class="btn btn-outline-primary p-1" 
   title="عرض صفحة العامل">
    <i class="bi bi-person-circle fs-5"></i>
</a>

            @endif
        </div>
    </form>
</td>



    <td class="text-center">
    {{ $order->total_price ?? '-' }}
    @if(isset($order->total_before_discount) && $order->total_before_discount != $order->total_price)
        <div>
            <small class="text-muted"><s>{{ $order->total_before_discount }}</s></small>
        </div>
    @endif
</td>

    <td class="text-center">
        <span class="badge text-white
            @if($order->status=='0') bg-secondary
            {{-- @elseif($order->status=='1') bg-primary   --}}
            @elseif($order->status=='1') bg-success  
            @elseif($order->status=='2') bg-warning
            @elseif($order->status=='3') bg-info
            @elseif($order->status=='4') bg-success
            @else bg-dark @endif">
            @if($order->status=='0') قيد الموافقة
            @elseif($order->status=='1') قيد التحضير
            @elseif($order->status=='2') في الطريق
            @elseif($order->status=='3') في الموقع
            @elseif ($order->status=='5') تم إرساله إلى عمال التوصيل
            @else غير معروف @endif
        </span>
        @if($order->status !='0' && $order->status !='5')
            {{-- زر تغيير الحالة يدويًا --}}
    <form action="{{ route('orders.forceStatusChange', $order->id) }}" method="POST" class="d-inline">
        @csrf
        @method('PATCH')
        <button type="submit" class="btn btn-sm btn-outline-secondary ms-1"
                title="تغيير الحالة يدويًا">
            <i class="bi bi-arrow-repeat"></i>
        </button>
    </form>
    @endif
    </td>
    <td class="text-center">
        @if($order->status=='0')
        <form action="{{ route('orders.accept', $order->id) }}" method="POST" style="display:inline-block">
            @csrf @method('PATCH')
            <input type="hidden" name="last_seen_at" value="{{ $order->updated_at }}">
            <button class="btn btn-sm btn-success me-1" style="border-radius: 8px; font-weight: 500;">
    <i class="bi bi-check-circle me-1"></i> Accept
</button>

        </form>
        @endif

        {{-- زر الحذف/الرفض --}}
        @include('admin.orders.partials.order_delete_modal', ['order'=>$order])
    </td>
</tr>
@empty
<tr><td colspan="6" class="text-center">لا يوجد طلبات حالياً</td></tr>
@endforelse
</tbody>
</table>
{{ $pendingOrders->appends(request()->query())->links('pagination::bootstrap-5') }}
