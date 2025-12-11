@extends('admin.layouts.master')

@section('content')
<div class="container-fluid py-2">
  @include('admin.partials.alerts')
  <div class="card my-4">
    <div class="card-header bg-gradient-dark shadow-dark">
      <h6 class="text-white ps-3">stores Table</h6>
    </div>
    <div class="card-body px-0 pb-2">
      <div class="d-flex mb-3 px-3">
  <form action="{{ url()->current() }}" method="GET" class="d-flex flex-column gap-3 px-3 mb-3">

    {{-- ✅ الاحتفاظ بالبحث --}}
    <input type="hidden" name="search" value="{{ request('search') }}">

   {{-- فلترة حسب الصنف --}}
<div class="rounded border-0 p-2 " style="background-color: rgba(210, 209, 209, 0);">
    <label class="fw-bold d-block mb-1">📂 Category:</label>
    <select name="category_id" class="form-select" onchange="this.form.submit()">
        <option value="" selected>All Categories</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}" 
                {{ request('category_id') == $category->id ? 'selected' : '' }}>
                {{ $category->type }}
            </option>
        @endforeach
    </select>
</div>


    {{-- فلترة حسب الحالة --}}
    <div class="rounded border-0 p-2 me-12" style="background-color: rgba(210, 209, 209, 0);">
      <label class="fw-bold d-block mb-1">⚡ statuses:</label>
      @php
        $statuses = [
          '0' => 'Pending',
          '1' => 'Active',
          '2' => 'Banned',
        ];
      @endphp

      @foreach($statuses as $key => $label)
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="checkbox" 
                 name="statuses[]" 
                 value="{{ $key }}"
                 {{ is_array(request('statuses')) && in_array($key, request('statuses')) ? 'checked' : '' }}
                 onchange="this.form.submit()">
          <label class="form-check-label">{{ $label }}</label>
        </div>
      @endforeach
    </div>


  </form>
</div>
<div class="table-responsive p-0" id="pending-stores-table">
   <table class="table align-items-center mb-0">
        <thead>
          <tr>
            <th>Name & Avatar</th>
            <th>cover</th>
            <th class="text-center">Owner</th>
            <th class="text-center">phone</th>
            <th class="text-center">Category</th>
            <th class="text-center">Status</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($stores as $store)
          <tr>
            <td>
              <div class="d-flex px-2 py-1">
                <div>
                  <img src="{{ $store->image ? asset('storage/'.$store->image) : asset('assets/img/default-avatar.png') }}"
                       class="avatar avatar-sm me-3 bstore-radius-lg" alt="{{ $store->name }}">
                </div>
                <div class="d-flex flex-column justify-content-center">
                  <h6 class="mb-0 text-sm">
                    <a href="{{ route('stores.show', $store->id) }}">{{ $store->name }}</a>
                  </h6>
                  <p class="text-xs text-secondary mb-0">{{ $store->special }}</p>
                </div>
              </div>
            </td>
            <td class="text-center">
              <img src="{{ $store->cover ? asset('storage/'.$store->cover) : asset('assets/img/default-avatar.png') }}"
                   class="avatar avatar-sm me-3 bstore-radius-lg" alt="{{ $store->name }}">
            </td>
            <td class="text-center">{{ $store->user->name ?? 'حساب محذوف' }}</td>
            <td class="text-center">{{ $store->phone ?? '-' }}</td>
            <td class="text-center">{{ $store->category->type ?? '-' }}</td>
            <td class="align-middle text-center text-sm">
  @if($store->status=='1')
    <span class="badge badge-sm bg-gradient-success">Active</span>
  @elseif($store->status=='0')
    <span class="badge badge-sm bg-gradient-warning">Pending</span>
  @else
    <span class="badge badge-sm bg-gradient-secondary">Banned</span>
  @endif

  {{-- عرض عدد مرات الحظر إذا موجود --}}
  @if(isset($store->ban_count) && $store->ban_count > 0)
    <div class="text-xs text-muted mt-1">
      مرات الحظر : {{ $store->ban_count }}
    </div>
  @endif

  {{-- إذا المتجر محظور، عرض رسالة لايمت --}}
  @if($store->status == '2')
    <div class="text-xs text-danger mt-1">
      {{$store->ban_until ? 'محظور حتى: ' . \Carbon\Carbon::parse($store->ban_until)->format('Y-m-d') : 'محظور حتى إشعار آخر'}}
    </div>
  @endif
</td>


            <td class="align-middle text-center">

              {{-- زر قبول --}}
              @if ($store->status == '0')
              <form action="{{ route('stores.accept', $store->id) }}" method="POST" style="display:inline-block">
                @csrf
                @method('PATCH')
                <input type="hidden" name="last_seen_at" value="{{ $store->updated_at }}">
                    <button type="submit" class="btn btn-sm btn-success me-1" style="border-radius: 8px; font-weight: 500;">
    <i class="bi bi-check-circle me-1"></i> Accept
</button>
              </form>
              @endif

              {{-- زر حظر --}}
              @if ($store->status !='0')
             

      <button type="button" class="btn btn-sm btn-warning me-1 btn-ban-store" 
        data-id="{{ $store->id }}" data-status="{{ $store->status }}"
                        data-last="{{ $store->updated_at }}">
        <i class="bi bi-power me-1"></i> 
        {{ $store->status=='1' ? 'Ban' : 'Unban' }}
</button>
                
           
              @endif


                @php
    $isReject = $store->status == '0';
    $btnClass = $isReject ?  'btn-danger':'btn-outline-danger';
    $btnIcon = $isReject ? 'bi-x-circle' : 'bi-trash';
    $btnText = $isReject ? 'Reject' : 'Delete';
@endphp


<button type="button" class="btn btn-sm {{ $btnClass }} btn-delete-store" 
        data-id="{{ $store->id }}" data-status="{{ $store->status }}"
                        data-last="{{ $store->updated_at }}">
        <i class="bi {{ $btnIcon }} me-1"></i> {{ $btnText }}
</button>

            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
       <div class="mt-3 px-3">
  {{ $stores->appends(request()->query())->links('pagination::bootstrap-5') }}
</div>
    </div>
    </div>
  </div>
</div>

{{--Ban Modal واحد لكل المتاجر --}}
  <div class="modal fade" id="banStoreModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form id="banStoreForm" method="POST">
        @csrf
        @method('PATCH')
        <input type="hidden" name="last_seen_at" id="banStoreLastSeen">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="banStoreModalTitle"></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <label>اختر سبب سريع:</label>
            <select class="form-select mb-2" id="banStoreQuickReason" aria-placeholder="اختر سبب سريع" onchange="document.getElementById('banStoreCustomReason').value=this.value">
            </select>
            <label>أو اكتب سبب مخصص:</label>
            <textarea id="banStoreCustomReason" name="ban_reason" class="form-control" placeholder="اكتب السبب هنا..." required></textarea>
          <label>مدة الحظر (أيام):</label>
<input type="number" id="banStoreuntil" name="ban_until" class="form-control" placeholder="أيام" required>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
            <button type="submit" class="btn btn-danger" id="banStoreModalBtn"></button>
          </div>
        </div>
      </form>
    </div>
  </div>

{{-- Delete Modal واحد لكل المتاجر --}}
  <div class="modal fade" id="deleteStoreModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form id="deleteStoreForm" method="POST">
        @csrf
        @method('DELETE')
        <input type="hidden" name="last_seen_at" id="deleteStoreLastSeen">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="deleteStoreModalTitle"></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <label>اختر سبب سريع:</label>
            <select class="form-select mb-2" id="deleteStoreQuickReason" aria-placeholder="اختر سبب سريع" onchange="document.getElementById('deleteStoreCustomReason').value=this.value">
            </select>
            <label>أو اكتب سبب مخصص:</label>
            <textarea id="deleteStoreCustomReason" name="delete_reason" class="form-control" placeholder="اكتب السبب هنا..." required></textarea>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
            <button type="submit" class="btn btn-danger" id="deleteStoreModalBtn"></button>
          </div>
        </div>
      </form>
    </div>
  </div>

  {{-- ✅ جدول المتاجر التي تنتظر الحذف --}}
  <div class="card my-4">
    <div class="card-header bg-gradient-danger shadow-dark d-flex justify-content-between align-items-center">
  <h6 class="text-white ps-3 mb-0">Stores Pending Deletion</h6>
  @if($trashedstores->count() > 0)
  <button type="button" class="btn btn-sm btn-light text-danger fw-bold" 
          data-bs-toggle="modal" data-bs-target="#emptyTrashModal">
    <i class="bi bi-trash3 me-1"></i> Empty Trash
  </button>
  @endif
</div>
    <div class="card-body px-0 pb-2">
      <table class="table align-items-center mb-0">
        <thead>
          <tr>
            <th class="text-center">Store</th>
            <th class="text-center">Owner</th>
            <th class="text-center">Deletion date</th>
            <th class="text-center">Status at delete</th>
            <th class="text-center">Deleted by</th>
            <th class="text-center">Reason</th>
            <th class="text-center">Related orders</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($trashedstores as $trashedstore)
          <tr>
            <td class="text-center">
             <a href="{{ route('stores.show', $trashedstore->id) }}">
    {{ $trashedstore->name }}
  </a>
            </td>
            <td class="text-center">{{ $trashedstore->user->name?? '-' }}</td>
             <td class="text-center">{{ $trashedstore->deleted_at }}</td>
            <td class="text-center">{{ $trashedstore->status }}</td>
            <td class="text-center">{{ $trashedstore->deleted_by ?? '-' }}</td>
            <td class="text-center">
              {{ $trashedstore->delete_reason ?? '-' }}
              @if($trashedstore->appeal)
  <div class="mt-1">
    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#appealModal{{ $trashedstore->id }}">
      يوجد طلب استئناف
    </button>

    <!-- Modal -->
    <div class="modal fade" id="appealModal{{ $trashedstore->id }}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-info text-white">
            <h5 class="modal-title">تفاصيل طلب الاستئناف</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="إغلاق"></button>
          </div>
          <div class="modal-body">
            <div class="p-2 border rounded" style="background-color: #f0f8ff;">
              {{ $trashedstore->appeal?? 'لا يوجد سبب محدد' }}
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
          </div>
        </div>
      </div>
    </div>
  </div>
@endif

            </td>
            <td class="text-center">
  @if(isset($pendingStoreOrders[$store->id]) && count($pendingStoreOrders[$store->id]) > 0)
    <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#ordersModal{{ $store->id }}">
      عرض الطلبات ({{ count($pendingStoreOrders[$store->id]) }})
    </button>
  @else
    <span class="text-success">لا يوجد</span>
  @endif
</td>

            <td class="text-center">
              @can('confirm deletion')
              <form action="{{ route('stores.forceDelete', $trashedstore->id) }}" method="POST" style="display:inline-block">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="حذف نهائي">
                     <i class="bi bi-trash me-1"></i> Delete
                </button>
              </form>
              @endcan
              <form action="{{ route('stores.restore', $trashedstore->id) }}" method="POST" style="display:inline-block">
                @csrf
                @method('PATCH')
               <button type="submit" class="btn btn-sm btn-warning me-1" style="border-radius: 8px; font-weight: 500;" data-bs-toggle="tooltip" data-bs-placement="top" title="استعادة المتجر">
                <i class="bi bi-power me-1"></i> Restore
                </button>
              </form>
            </td>
          </tr>
          @empty
          <tr><td colspan="4" class="text-center">لا يوجد متاجر بانتظار الحذف</td></tr>
          @endforelse
        </tbody>
      </table>
      <div class="mt-3 px-3">
  {{ $trashedstores->appends(request()->query())->links('pagination::bootstrap-5') }}
</div>
    </div>
  </div>

</div>

<!-- Empty Trash Modal -->
<div class="modal fade" id="emptyTrashModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form action="{{ route('stores.emptyTrash') }}" method="POST">
      @csrf
      @method('DELETE')
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title">تأكيد إفراغ السلة</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center">
  ⚠️ سيتم حذف جميع المتاجر المحذوفة نهائيًا  
  <br>
  <strong>باستثناء المتاجر التي لديها طلبات قيد المعالجة.</strong>
  <br><br>
  هل أنت متأكد من المتابعة؟
</div>

        <div class="modal-footer justify-content-center">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
          <button type="submit" class="btn btn-danger">نعم، احذف </button>
        </div>
      </div>
    </form>
  </div>
</div>

@foreach ($trashedstores as $store)
  @if(isset($pendingStoreOrders[$store->id]) && count($pendingStoreOrders[$store->id]) > 0)
  <div class="modal fade" id="ordersModal{{ $store->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title">الطلبات المرتبطة بالمتجر "{{ $store->name }}"</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="mb-2 text-danger fw-bold">
            ⚠️ لا يمكن حذف هذا المتجر نهائيًا لأنه مرتبط بطلبات قيد المعالجة:
          </p>
          <table class="table table-bordered align-middle text-center">
            <thead class="table-warning">
              <tr>
                <th>#</th>
                <th>رقم الطلب</th>
                <th>تاريخ الإنشاء</th>
                <th>حالة الطلب</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($pendingStoreOrders[$store->id] as $index => $order)
                <tr>
                  <td>{{ $index + 1 }}</td>
                  <td>{{ $order->id }}</td>
                  <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                  <td>
                    @switch($order->status)
                      @case('1')
                        <span class="badge bg-info">قيد التجهيز</span>
                        @break
                      @case('2')
                        <span class="badge bg-primary">قيد التوصيل</span>
                        @break
                      @case('3')
                        <span class="badge bg-success">مكتمل</span>
                        @break
                      @default
                        <span class="badge bg-warning text-dark">غير معروف</span>
                    @endswitch
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
        </div>
      </div>
    </div>
  </div>
  @endif
@endforeach


@endsection

@section('scripts')
{{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
<script>
let lastTableHtml = $('#pending-stores-table tbody').html();

// تحديث الجدول كل 30 ثانية
setInterval(updateStoreTable, 3600000);

function updateStoreTable(skipNotice = false) {
  $.ajax({
    url: "{{ route('stores') }}",
    type: 'GET',
    dataType: 'html',
    success: function(response){
      const newTbody = $(response).find('#pending-stores-table tbody').html();
      if(lastTableHtml !== newTbody){
        $('#pending-stores-table tbody').html(newTbody);
        lastTableHtml = newTbody;
        if(!skipNotice) showNotice('🔄 تم تحديث تفاصيل المتاجر');
      }
    },
    error: function(err){ console.error(err); }
  });
}

// دالة عرض التنبيه
function showNotice(msg, info=false){
  const notice = $('<div>').text(msg).css({
    position:'fixed', top:'20px', right:'20px',
    background: info ? '#6c757d':'#007bff', color:'white',
    padding:'10px 20px', borderRadius:'8px', boxShadow:'0 0 10px rgba(0,0,0,0.3)',
    zIndex:9999, fontWeight:'bold'
  }).hide().appendTo('body').fadeIn(300);
  setTimeout(()=>notice.fadeOut(500,()=>notice.remove()),3000);
}

// ================== Event Delegation ==================

// أزرار الحذف
$(document).on('click', '.btn-delete-store', function(){
    const id = $(this).data('id');
    const status = $(this).data('status');
    const last = $(this).data('last');

    const form = $('#deleteStoreForm');
    const modal = $('#deleteStoreModal');
    const title = $('#deleteStoreModalTitle');
    const quick = $('#deleteStoreQuickReason');
    const custom = $('#deleteStoreCustomReason');
    const btn = $('#deleteStoreModalBtn');

    if(status=='0'){
      title.text('سبب رفض المتجر');
      btn.text('رفض');
      quick.html(`
      <option value="" selected hidden>اختر سبب سريع</option>
        <option value="مخالفة الشروط">مخالفة الشروط</option>
        <option value="محتوى غير مناسب">محتوى غير مناسب</option>
        <option value="موقع غير مناسب">موقع غير مناسب</option>
      `);
    } else {
      title.text('سبب حذف المتجر');
      btn.text('حذف');
      quick.html(`
      <option value="" selected hidden>اختر سبب سريع</option>
        <option value="اغلاق المتجر">اغلاق المتجر</option>
        <option value="انتهاء العقد">انتهاء العقد</option>
        <option value="طلبات منخفضة">طلبات منخفضة</option>
      `);
    }

    form.attr('action','stores/'+id);
    $('#deleteStoreLastSeen').val(last);
    custom.val('');
    modal.modal('show');
});

// أزرار الحظر
$(document).on('click', '.btn-ban-store', function(){
    const id = $(this).data('id');
    const status = $(this).data('status');
    const last = $(this).data('last');

    const form = $('#banStoreForm');
    const modal = $('#banStoreModal');
    const title = $('#banStoreModalTitle');
    const quick = $('#banStoreQuickReason');
    const custom = $('#banStoreCustomReason');
    const until = $('#banStoreuntil');
    const btn = $('#banStoreModalBtn');

    if(status=='1'){ 
      // Ban → افتح المودال لاختيار السبب
      title.text('سبب حظر المتجر');
      btn.text('حظر');
      quick.html(`
        <option value="" selected hidden>اختر سبب سريع</option>
        <option value="مخالفة الشروط">مخالفة الشروط</option>
        <option value="محتوى غير مناسب">محتوى غير مناسب</option>
        <option value="موقع غير مناسب">موقع غير مناسب</option>
      `);
      form.attr('action','stores/'+id+'/ban');
      $('#banStoreLastSeen').val(last);
      custom.val('');
      until.val('');
      modal.modal('show');
    } else { 
      // Unban → أرسل الطلب مباشرة بدون مودال
      $.ajax({
        url: 'stores/'+id+'/ban',
        type: 'PATCH',
        data: {
          last_seen_at: last,
          _token: '{{ csrf_token() }}'
        },
        success: function(response){
          showNotice('✅ تم إلغاء الحظر');
         updateStoreTable(true); // true يعني ما تظهر إشعار التحديث 
        },
        error: function(err){
          console.error(err);
          showNotice('❌ حدث خطأ أثناء إلغاء الحظر', true);
        }
      });
    }
});

// عند اختيار سبب سريع، انسخه للمودال
// $(document).on('change', '#banStoreQuickReason', function(){
//     $('#banStoreCustomReason').val($(this).val());
// });
// $(document).on('change', '#deleteStoreQuickReason', function(){
//     $('#deleteStoreCustomReason').val($(this).val());
// });
</script>

@endsection