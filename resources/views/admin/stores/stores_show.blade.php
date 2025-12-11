@extends('admin.layouts.master')

@section('content')
<div class="container-fluid py-4" id="store-details-container">
    @include('admin.partials.alerts')
    <div class="card mb-4 shadow">

        {{-- Cover --}}
        <div class="position-relative" style="height: 400px;">
            <div style="
        background-image: url('{{ $store->cover ? asset('storage/'.$store->cover) : asset('assets/img/default-cover.jpg') }}');
        background-size: cover;
        background-position: center;
        width: 100%;
        height: 100%;
    "></div>
            <div class="position-absolute top-100 start-50 translate-middle">
                <img src="{{ $store->image ? asset('storage/'.$store->image) : asset('assets/img/default-avatar.png') }}"
                     class="rounded-circle border border-3 border-white" width="120" height="120" alt="{{ $store->name }}">
            </div>
        </div>

        <div class="card-body text-center mt-5">
            <h4 class="mb-0">{{ $store->name ?? '-'}}</h4>
            <p class="text-muted">{{ $store->special ?? '-'}}</p>
            
            {{-- معلومات سريعة --}}
            <div class="row mt-4">
                <div class="col-md-4">
                    <h6>المالك</h6>
                    <p>{{ $store->user->name?? '-' }}</p>
                </div>
                <div class="col-md-4">
                    <h6>العنوان</h6>
                    <p>{{ $store->address ?? '-'}}</p>
                </div>
                <div class="col-md-4">
                    <h6>الهاتف</h6>
                    <p>{{ $store->phone ?? '-'}}</p>
                </div>
            </div>

            {{-- 🔥 أوقات العمل --}}
            @include('admin.stores.partials.work_times')

            {{-- حالة المتجر وأزرار الموافقة --}}
            @if ($store->deleted_at == null)
                <div class="mt-3">
                    @if($store->status=='1')
                        <span class="badge bg-success">Active</span>
                    @elseif($store->status=='0')
                        <span class="badge bg-warning">Pending</span>
                        <div class="mt-2 d-flex justify-content-center gap-2">
                            <form action="{{ route('stores.accept', $store->id) }}" method="POST" style="display:inline-block">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="last_seen_at" value="{{ $store->updated_at }}">
                                <button type="submit" class="btn btn-sm btn-success">قبول</button>
                            </form>

                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $store->id }}">
                                Reject
                            </button>

                            <!-- Modal -->
                            <div class="modal fade" id="deleteModal{{ $store->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <form action="{{ route('stores.destroy', $store->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="last_seen_at" value="{{ $store->updated_at }}">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">{{ $store->status == '0' ? 'سبب رفض المتجر' : 'سبب حذف المتجر' }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <label>اختر سبب سريع:</label>
                                                <select class="form-select mb-2" name="quick_reason" aria-placeholder="اختر سبب سريع" onchange="document.getElementById('custom_reason_{{ $store->id }}').value = this.value">
                                                    @if ($store->status == '0')
                                                    <option value="" selected hidden>اختر سبب سريع</option>
                                                    <option value="مخالفة الشروط">مخالفة الشروط</option>
                                                    <option value="محتوى غير مناسب">محتوى غير مناسب</option>
                                                    <option value="موقع غير مناسب">موقع غير مناسب</option>
                                                    @else
                                                    <option value="" selected hidden>اختر سبب سريع</option>
                                                    <option value="اغلاق المتجر">اغلاق المتجر</option>
                                                    <option value="انتهاء العقد">انتهاء العقد</option>
                                                    <option value="طلبات منخفضة">طلبات منخفضة</option>
                                                    @endif
                                                </select>

                                                <label>أو اكتب سبب مخصص:</label>
                                                <textarea id="custom_reason_{{ $store->id }}" name="delete_reason" class="form-control" placeholder="اكتب السبب هنا..." required></textarea>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                <button type="submit" class="btn btn-danger">{{ $store->status == '0' ? 'رفض' : 'حذف' }}</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="mt-3">
                            <span class="badge bg-secondary">Banned</span>
                            <p class="mt-2 text-danger">هذا المتجر محظور حتى {{ $store->ban_until }}. السبب: {{ $store->ban_reason ?? '-' }}</p>
                        </div>
                    @endif
                </div>
            @else
                <div class="mt-3">
                    <span class="badge bg-danger">Deleted</span>
                    <p class="mt-2 text-danger">تم حذف هذا المتجر في {{ $store->deleted_at }}. السبب: {{ $store->delete_reason ?? '-' }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- المنتجات المرتبطة بالمتجر --}}
    <div class="card shadow">
        <div class="card-header bg-gradient-dark text-white">
            <h6 class="mb-0">المنتجات</h6>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($store->meals as $meal)
                <div class="col-md-3 mb-3">
                    <div class="card h-100 text-center">
                        <a href="{{ route('products.show', $meal->id) }}">
                            <img src="{{ $meal->image ? asset('storage/'.$meal->image) : asset('assets/img/meal-placeholder.png') }}"
                                 class="card-img-top" style="height:150px; object-fit:cover;">
                        </a>
                        <div class="card-body">
                            <h6>
                                <a href="{{ route('products.show', $meal->id) }}">{{$meal->name}}</a>
                            </h6>
                            <p class="text-muted mb-0">{{ $meal->price }} ل.س</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
.store-cover {
    width: 100%;
    height: 200px; /* ارتفاع الغلاف */
    background-position: center;
    background-size: cover;
    background-repeat: no-repeat;
}
</style>
@endsection
