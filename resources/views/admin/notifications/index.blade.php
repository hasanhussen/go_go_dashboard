@extends('admin.layouts.master')



@section('content')
<div class="container-fluid py-4" id="notification-details-container">

    @include('admin.partials.alerts')

    {{-- العنوان --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">إشعارات النظام 🔔</h4>
    </div>

    {{-- عرض الإشعارات --}}
    <div class="row">
        <div class="col-lg-8">
            @forelse($notifications as $notification)
                @php
                    $type = $notification->data['type'] ?? '';
                    $icon = '🔔';
                    $color = 'secondary';
                    $title = '';
                    $message = '';
                    $link = '#';

                    switch ($type) {
                        case 'store_edit':
                            $icon = '🏪';
                            $color = 'primary';
                            $title = 'تعديل متجر';
                            $message = $notification->data['body'] ?? '';
                            $link = route('stores.show', $notification->data['store_id'] ?? 0);
                            break;

                        case 'product_edit':
                            $icon = '🛠️';
                            $color = 'success';
                            $title = 'تعديل منتج';
                            $message = $notification->data['body'] ?? '';
                            $link = route('products.show', $notification->data['product_id'] ?? 0);
                            break;

                        case 'admin_support':
                            $icon = '📩';
                            $color = 'warning';
                            $title = 'شكوى جديدة';
                            $message = $notification->data['body'] ?? '';
                            $link = route('admin.supports.show', $notification->data['support_id'] ?? 0);
                            break;

                        default:
                            $title = $notification->data['title'] ?? 'إشعار جديد';
                            $message = $notification->data['body'] ?? '';
                            break;
                    }
                @endphp

                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-start">
                            <div class="me-3 fs-3">{{ $icon }}</div>
                            <div>
                                <h6 class="mb-1 text-{{ $color }}">{{ $title }}</h6>
                                <p class="mb-0 text-muted">{{ $message }}</p>
                                <small class="text-secondary">{{ $notification->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        @if($link && $link !== '#')
                            <a href="{{ $link }}" class="btn btn-sm btn-outline-{{ $color }}">
                                عرض التفاصيل
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="alert alert-info text-center">
                    لا توجد إشعارات حالياً 😊
                </div>
            @endforelse

            {{-- Pagination --}}
            <div class="mt-4">
                {{ $notifications->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>

        {{-- إرسال إشعار جماعي --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <h5 class="mb-0 fw-bold">إرسال إشعار جماعي 🚀</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('notifications.sendtoall') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">الفئة المستهدفة</label>
                            <select name="type" class="form-select" required>
                                <option value="">اختر الفئة</option>
                                <option value="users">كل المستخدمين</option>
                                <option value="workers">عمال التوصيل</option>
                                <option value="owners">التجار</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">عنوان الإشعار</label>
                            <input type="text" name="title" class="form-control" placeholder="مثلاً: تحديث جديد في التطبيق" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">محتوى الإشعار</label>
                            <textarea name="body" class="form-control" rows="3" placeholder="نص الإشعار..." required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">إرسال الإشعار</button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let lastNotificationHtml = $('#notification-details-container').html(); 

setInterval(updateNotificationDetails, 60000);

function updateNotificationDetails() {
  $.ajax({
    url: "{{ route('notifications.index') }}",
    type: 'GET',
    dataType: 'html',
    success: function(response) {
      const newBody = $(response).find('#notification-details-container').html();
      if (lastNotificationHtml !== newBody) {
        $('#notification-details-container').html(newBody);
        lastNotificationHtml = newBody;
      } else {
        console.log('لا يوجد تحديثات جديدة');
      }
    }
  });
}


</script>
@endsection