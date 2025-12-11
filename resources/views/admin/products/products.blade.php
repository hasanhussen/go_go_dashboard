@extends('admin.layouts.master')

@section('content')
<div class="container-fluid py-2">
  {{-- رسائل النجاح --}}
  @include('admin.partials.alerts')

  {{-- جدول المنتجات --}}
  <div class="card my-4">
    <div class="card-header bg-gradient-dark shadow-dark">
      <h6 class="text-white ps-3">Products Table</h6>
    </div>
    <div class="card-body px-0 pb-2">

      {{-- فلترة المنتجات --}}
      <div class="d-flex mb-3 px-3">
        <form action="{{ url()->current() }}" method="GET" class="d-flex flex-wrap gap-3 px-3 mb-3">
          <input type="hidden" name="search" value="{{ request('search') }}">

          <div class="rounded border-0 p-2" style="background-color: rgba(210, 209, 209, 0);">
            <label class="fw-bold d-block mb-1">⚡ statuses:</label>
            @php
              $statuses = ['0'=>'Pending','1'=>'Active','2'=>'Banned'];
            @endphp
            @foreach($statuses as $key => $label)
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="statuses[]" value="{{ $key }}"
                  {{ is_array(request('statuses')) && in_array($key, request('statuses')) ? 'checked' : '' }}
                  onchange="this.form.submit()">
                <label class="form-check-label">{{ $label }}</label>
              </div>
            @endforeach
          </div>
        </form>
      </div>

      <div class="table-responsive">
        <table class="table align-items-center mb-0" id="pending-products-table">
          <thead>
            <tr>
              <th>Name</th>
              <th class="text-center">Image</th>
              <th class="text-center">Description</th>
              <th class="text-center">Price</th>
              <th class="text-center">Store</th>
              <th class="text-center">Status</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($products as $product)
            <tr>
              <td>
                <h6 class="mb-0 text-sm">
                  <a href="{{ route('products.show', $product->id) }}">{{ $product->name }}</a>
                </h6>
                <p class="text-xs text-secondary mb-0">{{ $product->note ?? '-' }}</p>
              </td>
              <td class="text-center">
                <img src="{{ $product->image ? asset('storage/'.$product->image) : asset('assets/img/default-avatar.png') }}"
                  class="avatar avatar-sm me-3 bstore-radius-lg" alt="{{ $product->name }}">
              </td>
              <td class="text-center" style="max-width:200px; word-wrap: break-word; white-space: normal;">
                {{ $product->description??'-' }}
              </td>
              <td class="text-center">{{ $product->price }}</td>
              <td class="text-center" style="max-width:180px; word-wrap: break-word; white-space: normal;">
              <a href="{{ $product->store?->id ? route('stores.show', $product->store->id) : '#' }}">
    {{ $product->store?->name ?? 'غير محدد' }}
</a>

              </td>
              <td class="align-middle text-center text-sm">
                @if($product->status=='1')
                  <span class="badge badge-sm bg-gradient-success">Active</span>
                @elseif($product->status=='0')
                  <span class="badge badge-sm bg-gradient-warning">Pending</span>
                @else
                  <span class="badge badge-sm bg-gradient-secondary">Banned</span>
                  {{-- عرض عدد مرات الحظر إذا موجود --}}
  @if(isset($product->ban_count) && $product->ban_count > 0)
    <div class="text-xs text-muted mt-1">
      مرات الحظر : {{ $product->ban_count }}
    </div>
  @endif

  {{-- إذا المتجر محظور، عرض رسالة لايمت --}}
  @if($product->status == '2')
    <div class="text-xs text-danger mt-1">
      {{$product->ban_until ? 'محظور حتى: ' . \Carbon\Carbon::parse($product->ban_until)->format('Y-m-d') : 'محظور حتى إشعار آخر'}}
    </div>
  @endif
                @endif
              </td>
              <td class="align-middle text-center">

                {{-- زر قبول --}}
                @if($product->status=='0')
                <form action="{{ route('products.accept', $product->id) }}" method="POST" style="display:inline-block">
                  @csrf
                  @method('PATCH')
                  <input type="hidden" name="last_seen_at" value="{{ $product->updated_at }}">
                
                     <button type="submit" class="btn btn-sm btn-success me-1" style="border-radius: 8px; font-weight: 500;">
    <i class="bi bi-check-circle me-1"></i> Accept
</button>
                </form>
                @endif

                {{-- زر حظر/إلغاء حظر --}}
                @if($product->status!='0')
                    <button type="button" class="btn btn-sm btn-warning me-1 btn-ban-product" 
        data-id="{{ $product->id }}" data-status="{{ $product->status }}"
                        data-last="{{ $product->updated_at }}">
        <i class="bi bi-power me-1"></i> 
        {{ $product->status=='1' ? 'Ban' : 'Unban' }}
</button>
                  
   
              
                @endif

  
                                @php
    $isReject = $product->status == '0';
    $btnClass = $isReject ?  'btn-danger':'btn-outline-danger';
    $btnIcon = $isReject ? 'bi-x-circle' : 'bi-trash';
    $btnText = $isReject ? 'Reject' : 'Delete';
@endphp


<button type="button" class="btn btn-sm {{ $btnClass }} btn-delete-product" 
         data-id="{{ $product->id }}" data-status="{{ $product->status }}" data-last="{{ $product->updated_at }}">
        <i class="bi {{ $btnIcon }} me-1"></i> {{ $btnText }}
</button>


              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
        <div class="mt-3 px-3">
          {{ $products->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
      </div>
    </div>
  </div>

  {{-- ✅ جدول المنتجات التي تنتظر الحذف --}}
  <div class="card my-4">
        <div class="card-header bg-gradient-danger shadow-dark d-flex justify-content-between align-items-center">
  <h6 class="text-white ps-3 mb-0">Products Pending Deletion</h6>
  @if($trashedproducts->count() > 0)
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
            <th class="text-center">Product</th>
            <th class="text-center">Store</th>
            <th class="text-center">Deletion date</th>
            <th class="text-center">Status at delete</th>
            <th class="text-center">Reason</th>
            <th class="text-center">Related orders</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($trashedproducts as $trashedproduct)
          <tr>
            <td class="text-center"><a href="{{ route('products.show', $trashedproduct->id) }}">{{ $trashedproduct->name }}</a></td>
            <td class="text-center">{{ $trashedproduct->store->name }}</td>
            <td class="text-center">{{ $trashedproduct->deleted_at }}</td>
            <td class="text-center">{{ $trashedproduct->status }}</td>
            <td class="text-center">
              {{ $trashedproduct->delete_reason ?? '-' }}
@if($trashedproduct->appeal)
  <div class="mt-1">
    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#appealModal{{ $trashedproduct->id }}">
      يوجد طلب استئناف
    </button>

    <!-- Modal -->
    <div class="modal fade" id="appealModal{{ $trashedproduct->id }}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-info text-white">
            <h5 class="modal-title">تفاصيل طلب الاستئناف</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="إغلاق"></button>
          </div>
          <div class="modal-body">
            <div class="p-2 border rounded" style="background-color: #f0f8ff;">
              {{ $trashedproduct->appeal?? 'لا يوجد سبب محدد' }}
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
  @if(isset($pendingProductOrders[$product->id]) && count($pendingProductOrders[$product->id]) > 0)
    <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#ordersModal{{ $product->id }}">
      عرض الطلبات ({{ count($pendingProductOrders[$product->id]) }})
    </button>
  @else
    <span class="text-success">لا يوجد</span>
  @endif
</td>
            <td class="text-center">
              @can('confirm deletion')
              <form action="{{ route('products.forceDelete', $trashedproduct->id) }}" method="POST" style="display:inline-block">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="حذف نهائي">
                     <i class="bi bi-trash me-1"></i> Delete
                </button>
              </form>
              @endcan
              <form action="{{ route('products.restore', $trashedproduct->id) }}" method="POST" style="display:inline-block">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-sm btn-warning me-1" style="border-radius: 8px; font-weight: 500;" data-bs-toggle="tooltip" data-bs-placement="top" title="استعادة المنتج">
                <i class="bi bi-power me-1"></i> Restore
                </button>
              
        
        
              </form>
            </td>
          </tr>
          @empty
          <tr><td colspan="6" class="text-center">لا يوجد منتجات بانتظار الحذف</td></tr>
          @endforelse
        </tbody>
      </table>
      <div class="mt-3 px-3">
        {{ $trashedproducts->appends(request()->query())->links('pagination::bootstrap-5') }}
      </div>
    </div>
  </div>

</div>

{{--Ban Modal واحد لكل المتاجر --}}
  <div class="modal fade" id="banProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form id="banProductForm" method="POST">
        @csrf
        @method('PATCH')
        <input type="hidden" name="last_seen_at" id="banProductLastSeen">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="banProductModalTitle"></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <label>اختر سبب سريع:</label>
            <select class="form-select mb-2" id="banProductQuickReason" aria-placeholder="اختر سبب سريع" onchange="document.getElementById('banProductCustomReason').value=this.value">
            </select>
            <label>أو اكتب سبب مخصص:</label>
            <textarea id="banProductCustomReason" name="ban_reason" class="form-control" placeholder="اكتب السبب هنا..." required></textarea>
          <label>مدة الحظر (أيام):</label>
<input type="number" id="banProductuntil" name="ban_until" class="form-control" placeholder="أيام" required>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
            <button type="submit" class="btn btn-danger" id="banProductModalBtn"></button>
          </div>
        </div>
      </form>
    </div>
  </div>

{{-- ✅ مودال ديناميكي للحذف/رفض --}}
<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="deleteProductForm" method="POST">
      @csrf
      @method('DELETE')
      <input type="hidden" name="last_seen_at" id="deleteProductLastSeen">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteProductModalTitle"></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <label>اختر سبب سريع:</label>
          <select class="form-select mb-2" id="deleteProductQuickReason" aria-placeholder="اختر سبب سريع" onchange="document.getElementById('deleteProductCustomReason').value=this.value">
          </select>
          <label>أو اكتب سبب مخصص:</label>
          <textarea id="deleteProductCustomReason" name="delete_reason" class="form-control" placeholder="اكتب السبب هنا..." required></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
          <button type="submit" class="btn btn-danger" id="deleteProductModalBtn"></button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Empty Trash Modal -->
<div class="modal fade" id="emptyTrashModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form action="{{ route('products.emptyTrash') }}" method="POST">
      @csrf
      @method('DELETE')
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title">تأكيد إفراغ السلة</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center">
  ⚠️ سيتم حذف جميع المنتجات المحذوفة نهائيًا  
  <br>
  <strong>باستثناء المنتجات الموجودة في طلبات قيد المعالجة.</strong>
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

@foreach ($trashedproducts as $product)
  @if(isset($pendingProductOrders[$product->id]) && count($pendingProductOrders[$product->id]) > 0)
  <div class="modal fade" id="ordersModal{{ $product->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title">الطلبات المرتبطة بالمنتج "{{ $product->name }}"</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="mb-2 text-danger fw-bold">
            ⚠️ لا يمكن حذف هذا المنتج نهائيًا لأنه مرتبط بطلبات قيدالمعالجة:
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
              
              @foreach ($pendingProductOrders[$product->id] as $index => $order)
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
let lastProductHtml = $('#pending-products-table tbody').html();


setInterval(updateProductTable, 3600000);

function getCurrentFilters() {
    return $('form').serialize(); // بيجمع كل الفيلدات داخل الفورم
}

function updateProductTable(skipNotice = false) {
  $.ajax({
    url: "{{ route('products') }}",
    type: 'GET',
    data: getCurrentFilters(),
    dataType: 'html',
    success: function(response){
      const newTbody = $(response).find('#pending-products-table tbody').html();
      if(lastProductHtml !== newTbody){
        $('#pending-products-table tbody').html(newTbody);
        lastProductHtml = newTbody;
        if(!skipNotice) showNotice('🔄 تم تحديث تفاصيل المنتجات');
      }
    },
    error: function(err){ console.error(err); }
  });
}

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

// Dynamic delete modal

  $(document).on('click','.btn-delete-product', function(){
    const id = $(this).data('id');
    const status = $(this).data('status');
    const last = $(this).data('last');

    const form = $('#deleteProductForm');
    const modal = $('#deleteProductModal');
    const title = $('#deleteProductModalTitle');
    const quick = $('#deleteProductQuickReason');
    const custom = $('#deleteProductCustomReason');
    const btn = $('#deleteProductModalBtn');

    if(status=='0'){
      title.text('سبب رفض المنتج');
      btn.text('رفض');
      quick.html(`
      <option value="" selected hidden>اختر سبب سريع</option>
        <option value="مخالفة الشروط">مخالفة الشروط</option>
        <option value="محتوى غير مناسب">محتوى غير مناسب</option>
        <option value="معلومات غير صحيحة">معلومات غير صحيحة</option>
      `);
    } else {
      title.text('سبب حذف المنتج');
      btn.text('حذف');
      quick.html(`
      <option value="" selected hidden>اختر سبب سريع</option>
        <option value="منتج مكرر">منتج مكرر</option>
        <option value="مخالفة الشروط">مخالفة الشروط</option>
        <option value="طلبات منخفضة">طلبات منخفضة</option>
      `);
    }

    form.attr('action','products/'+id);
    $('#deleteProductLastSeen').val(last);
    custom.val('');
    modal.modal('show');
  });


// أزرار الحظر
$(document).on('click', '.btn-ban-product', function(){
    const id = $(this).data('id');
    const status = $(this).data('status');
    const last = $(this).data('last');

    const form = $('#banProductForm');
    const modal = $('#banProductModal');
    const title = $('#banProductModalTitle');
    const quick = $('#banProductQuickReason');
    const custom = $('#banProductCustomReason');
    const until = $('#banProductuntil');
    const btn = $('#banProductModalBtn');

    if(status=='1'){ 
      // Ban → افتح المودال لاختيار السبب
      title.text('سبب حظر المنتج');
      btn.text('حظر');
      quick.html(`
        <option value="" selected hidden>اختر سبب سريع</option>
        <option value="مخالفة الشروط">مخالفة الشروط</option>
        <option value="محتوى غير مناسب">محتوى غير مناسب</option>
        <option value="موقع غير مناسب">موقع غير مناسب</option>
      `);
      form.attr('action','products/'+id+'/ban');
      $('#banProductLastSeen').val(last);
      custom.val('');
      until.val('');
      modal.modal('show');
    } else { 
      // Unban → أرسل الطلب مباشرة بدون مودال
      $.ajax({
        url: 'products/'+id+'/ban',
        type: 'PATCH',
        data: {
          last_seen_at: last,
          _token: '{{ csrf_token() }}'
        },
        success: function(response){
          showNotice('✅ تم إلغاء الحظر');
         updateProductTable(true); // true يعني ما تظهر إشعار التحديث 
        },
        error: function(err){
          console.error(err);
          showNotice('❌ حدث خطأ أثناء إلغاء الحظر', true);
        }
      });
    }
});


</script>
@endsection
