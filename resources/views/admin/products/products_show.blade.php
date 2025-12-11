@extends('admin.layouts.master')

@section('content')
<div class="container-fluid py-2" id="product-details-container">
  @include('admin.partials.alerts')
  <div class="card my-4">
    <div class="card-header bg-gradient-dark shadow-dark">
      <h6 class="text-white ps-3">تفاصيل المنتج</h6>
    </div>
    <div class="card-body px-4 pb-4">

      {{-- صورة المنتج + الاسم + السعر --}}
      <div class="row mb-4">
        <div class="col-md-4 text-center">
          <img src="{{ $product->image ? asset('storage/'.$product->image) : asset('assets/img/product-placeholder.png') }}"
               class="img-fluid rounded shadow-sm mb-3"
               style="max-height:200px; object-fit:cover;"
               alt="{{ $product->name }}">
        </div>
        <div class="col-md-8">
          <h5>{{ $product->name }}</h5>
          <p class="mb-1"><strong>النقاط: </strong>{{ $product->points ?? 0 }} نقطة</p>
          <p class="mb-1"><strong>السعر: </strong>{{ $product->price }} ل.س</p>
          <p class="mb-1"><strong>المتجر: </strong>
            <a href="{{ route('stores.show', $product->store->id) }}">
              {{ $product->store->name }}
            </a>
          </p>
          <p class="mb-1"><strong>الحالة: </strong>
            @if ($product->deleted_at != null)
              <span class="badge bg-gradient-danger">محذوف</span>
              <p class="mt-2 text-danger">تم حذف هذا المنتج في {{ $product->deleted_at }}. السبب: {{ $product->delete_reason ?? '-' }}</p>
              
            @else

            @if($product->status == '1')
              <span class="badge bg-gradient-success">مفعل</span>
            @elseif($product->status == '0')
              <span class="badge bg-gradient-warning">قيد المراجعة</span>
            @else
              <span class="badge bg-gradient-secondary">موقوف</span>
            @endif
            @endif
          </p>
        </div>
      </div>

            {{-- المقاسات --}}
      <div class="card shadow-sm mb-4">
        <div class="card-header bg-gradient-dark text-white">
          <h6 class="text-white ps-3">المقاسات</h6>
        </div>
        <div class="card-body">
          @if($product->variants && $product->variants->count() > 0)
            <ul class="list-group">
              @foreach($product->variants as $variant)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  {{ $variant->name }}
                  <span class="badge bg-primary rounded-pill">{{ $variant->price }} ل.س</span>
                </li>
              @endforeach
            </ul>
          @else
            <p class="text-muted">لا توجد مقاسات لهذا المنتج.</p>
          @endif
        </div>
      </div>


      {{-- الإضافات --}}
      <div class="card shadow-sm mb-4">
        <div class="card-header bg-gradient-dark text-white">
          <h6 class="text-white ps-3">الإضافات</h6>
        </div>
        <div class="card-body">
          @if($product->additionals && $product->additionals->count() > 0)
            <ul class="list-group">
              @foreach($product->additionals as $addition)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  {{ $addition->name }}
                  <span class="badge bg-primary rounded-pill">{{ $addition->price }} ل.س</span>
                </li>
              @endforeach
            </ul>
          @else
            <p class="text-muted">لا توجد إضافات مرتبطة بهذا المنتج.</p>
          @endif
        </div>
      </div>



      @if ($product->deleted_at == null)
                
            
            {{-- حالة المتجر مع أزرار الموافقة --}}
            <div class="mt-3">
                @if($product->status=='1')
                    <span class="badge bg-success d-inline-block mb-2">Active</span>
                @elseif($product->status=='0')
                    <span class="badge bg-warning d-inline-block mb-2">Pending</span>

                    {{-- أزرار قبول ورفض --}}
                    <div class="d-flex gap-2">
                        {{-- زر موافقة --}}
                        <form action="{{ route('products.accept', $product->id) }}" method="POST" onsubmit="return confirm('هل تريد قبول هذا المنتج؟');">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="last_seen_at" value="{{ $product->updated_at }}">
                            <button type="submit" class="btn btn-sm btn-success" style="border-radius: 8px; font-weight: 500;">
                                <i class="bi bi-check-circle me-1"></i> Accept
                            </button>
                        </form>

                        {{-- زر رفض --}}
                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $product->id }}">
                            Reject
                        </button>
                    </div>

                    <!-- Modal -->
                    <div class="modal fade" id="deleteModal{{ $product->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <form action="{{ route('products.destroy', $product->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="last_seen_at" value="{{ $product->updated_at }}">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">{{ $product->status == '0' ? 'سبب رفض المنتج' : 'سبب حذف المنتج' }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <label>اختر سبب سريع:</label>
                                        <select class="form-select mb-2" name="quick_reason" aria-placeholder="اختر سبب سريع" onchange="document.getElementById('custom_reason_{{ $product->id }}').value = this.value">
                                            @if ($product->status == '0')
                                            <option value="" selected hidden>اختر سبب سريع</option>
                                            <option value="مخالفة الشروط">مخالفة الشروط</option>
                                            <option value="محتوى غير مناسب">محتوى غير مناسب</option>
                                            <option value="معلومات غير صحيحة">معلومات غير صحيحة</option>
                                            @endif
                                        </select>

                                        <label>أو اكتب سبب مخصص:</label>
                                        <textarea id="custom_reason_{{ $product->id }}" name="delete_reason" class="form-control" placeholder="اكتب السبب هنا..." required></textarea>
                                    </div>
                                    <div class="modal-footer d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                        <button type="submit" class="btn btn-danger">{{ $product->status == '0' ? 'رفض' : 'حذف' }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                @else
                    <span class="badge bg-secondary d-inline-block mb-2">Banned</span>
                @endif
            </div>

            @else
            <div class="mt-3">
                <span class="badge bg-danger">Deleted</span>
                <p class="mt-2 text-danger">تم حذف هذا المنتج في {{ $product->deleted_at }}. السبب: {{ $product->delete_reason ?? '-' }}</p>
            </div>
                @endif

    </div>
  </div>
</div>
@endsection

@section('scripts')
{{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
<script>
let lastProductHtml = $('#product-details-container').html(); 

setInterval(updateProductDetails, 3600000);

function updateProductDetails() {
  $.ajax({
    url: "{{ route('products.show', $product->id) }}",
    type: 'GET',
    dataType: 'html',
    success: function(response) {
      const newBody = $(response).find('#product-details-container').html();

      if (lastProductHtml !== newBody) {
        $('#product-details-container').html(newBody);
        lastProductHtml = newBody;
        showNotice('🔄 تم تحديث تفاصيل المنتج');
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
