<table class="table align-items-center mb-0">
<thead>
<tr>
    <th class="text-center">ID</th>
    <th class="text-center">User</th>
    <th class="text-center">Deliveried by</th>
    <th class="text-center">Actions</th>
</tr>
</thead>
<tbody>
@forelse($completedOrders as $order)
<tr>
    <td class="text-center"><a href="{{ route('orders.show',$order->id) }}">{{ $order->id }}</a></td>
    <td class="text-center">{{ $order->user->name ?? '-'}}</td>
    <td class="text-center">{{ $order->delivery->name ?? '-' }}</td>
    <td class="text-center">
        @can('confirm deletion')
        <form action="{{ route('orders.forceDelete',$order->id) }}" method="POST" style="display:inline-block"
            onsubmit="return confirm('هل أنت متأكد أنك تريد حذف هذا الطلب نهائياً؟');">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="حذف نهائي">
    <i class="bi bi-trash me-1"></i> Delete
</button>

        </form>
        @endcan
    </td>
</tr>
@empty
<tr><td colspan="4" class="text-center">لا يوجد طلبات مكتملة</td></tr>
@endforelse
</tbody>
</table>
{{ $completedOrders->appends(request()->query())->links('pagination::bootstrap-5') }}
