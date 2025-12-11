@extends('admin.layouts.master')

@section('content')
<div class="container-fluid py-2">
  @include('admin.partials.alerts')
  <div class="row">
    <div class="col-12">
      <div class="card my-4">
        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
  <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex align-items-center justify-content-between">
    <h6 class="text-white text-capitalize ps-3 mb-0">Users Table</h6>
    @can('add users')
    {{-- زر إضافة مستخدم جديد --}}
      <button type="button" 
    class="btn btn-info" 
    style="padding: 8px 14px; margin: 6px 12px;"
    onclick="window.location='{{ route('users.create') }}'">
    <i class="bi bi-plus-lg"></i> Add New User
  </button>
    @endcan
    
  </div>
</div>


        <div class="card-body px-0 pb-2">
          <div class="table-responsive p-0">
            <div class="card-body px-0 pb-2">

  {{-- 🔍 فلترة المستخدمين فقط --}}
<div class="d-flex mb-3 px-3">
  <form action="{{ url()->current() }}" method="GET" class="d-flex flex-wrap gap-3 px-3 mb-3">

    {{-- ✅ الاحتفاظ بالبحث --}}
    <input type="hidden" name="search" value="{{ request('search') }}">

    {{-- فلترة حسب الدور --}}
    <div class="rounded border-0 p-2 me-12 " style="background-color: rgba(210, 209, 209, 0);">
      <label class="fw-bold d-block mb-1">🎭 roles:</label>
      @foreach($roles as $role)
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="checkbox" 
                 name="roles[]" 
                 value="{{ $role->id }}"
                 {{ is_array(request('roles')) && in_array($role->id, request('roles')) ? 'checked' : '' }}
                 onchange="this.form.submit()">
          <label class="form-check-label">{{ $role->name }}</label>
        </div>
      @endforeach
    </div>

    {{-- فلترة حسب الحالة --}}
    <div class="rounded border-0 p-2" style="background-color: rgba(210, 209, 209, 0);">
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


  <div class="table-responsive p-0" id="pending-users-table">
            <table class="table align-items-center mb-0">
              <thead>
                <tr>
                  <th>Author</th>
                  <th class="text-center">Role</th>
                  <th class="text-center">Status</th>
                  <th class="text-center">Created</th>
                  <th class="text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($users as $user)
                  <tr>
                    <td>
                      <div class="d-flex px-2 py-1">
                        <div>
                          <img src="{{$user->image ? asset('storage/'.$user->image) : asset('assets/img/default-avatar.png') }}"
                               class="avatar avatar-sm me-3 border-radius-lg" alt="{{ $user->name }}">
                        </div>
                        <div class="d-flex flex-column justify-content-center">
                          <h6 class="mb-0 text-sm">
  <a href="{{ route('users.show', $user->id) }}">
    {{ $user->name?? '-' }}
  </a>
</h6>
                          <p class="text-xs text-secondary mb-0">{{ $user->email }}</p>
                        </div>
                      </div>
                    </td>
                    <td class="text-center">
                      <p class="text-xs font-weight-bold mb-0">{{ $user->roles->pluck('name')->join(', ') }}</p>
                    </td>
                    <td class="align-middle text-center text-sm">
                      @if($user->status=='1')
                        <span class="badge badge-sm bg-gradient-success">Active</span>
                      @elseif($user->status=='0')
                        <span class="badge badge-sm bg-gradient-warning">Pending</span>
                      @else
                        <span class="badge badge-sm bg-gradient-secondary">Banned</span>
                                          {{-- عرض عدد مرات الحظر إذا موجود --}}
  @if(isset($user->ban_count) && $user->ban_count > 0)
    <div class="text-xs text-muted mt-1">
      مرات الحظر : {{ $user->ban_count }}
    </div>
  @endif

  {{-- إذا المستخدم محظور، عرض رسالة لايمت --}}
  @if($user->status == '2')
    <div class="text-xs text-danger mt-1">
      {{$user->ban_until ? 'محظور حتى: ' . \Carbon\Carbon::parse($user->ban_until)->format('Y-m-d') : 'محظور حتى إشعار آخر'}}
    </div>
  @endif
                      @endif
                    </td>
                    <td class="align-middle text-center">
                      <span class="text-secondary text-xs font-weight-bold">
                        {{ $user->created_at->format('d/m/Y') }}
                      </span>
                    </td>
                   <td class="align-middle text-center">
  {{-- 🔔 تنويه إذا البريد غير مؤكد --}}
  @if(!$user->email_verified_at)
    <div class="alert alert-warning py-1 px-2 mb-2" style="font-size: 0.75rem;">
      ⚠️ البريد الإلكتروني غير مؤكد
    </div>
  @endif

  {{-- زر قبول --}}
  @if ($user->status=='0')
    <form action="{{ route('users.accept', $user->id) }}" method="POST" style="display:inline-block">
      @csrf
      @method('PATCH')
      <button type="submit" class="btn btn-sm btn-success me-1" style="border-radius: 8px; font-weight: 500;"
              {{ !$user->email_verified_at ? 'disabled' : '' }}>
        <i class="bi bi-check-circle me-1"></i> Accept
      </button>
    </form>
  @endif

  {{-- زر حظر --}}
  @if ($user->status!='0')
    <button type="button" class="btn btn-sm btn-warning me-1 btn-ban-user" 
            data-id="{{ $user->id }}" data-status="{{ $user->status }}"
            data-last="{{ $user->updated_at }}">
      <i class="bi bi-power me-1"></i> 
      {{ $user->status=='1' ? 'Ban' : 'Unban' }}
    </button>
  @endif

  {{-- زر الحذف/رفض --}}
  @php
      $isReject = $user->status == '0';
      $btnClass = $isReject ?  'btn-danger':'btn-outline-danger';
      $btnIcon = $isReject ? 'bi-x-circle' : 'bi-trash';
      $btnText = $isReject ? 'Reject' : 'Delete';
  @endphp

  <button type="button" class="btn btn-sm {{ $btnClass }} btn-delete-user" 
          data-id="{{ $user->id }}" 
          data-status="{{ $user->status }}">
          <i class="bi {{ $btnIcon }} me-1"></i> {{ $btnText }}
  </button>
</td>


      </tr>
    @endforeach
  </tbody>
</table>


            {{--Ban Modal واحد لكل مستخدم --}}
  <div class="modal fade" id="banUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form id="banUserForm" method="POST">
        @csrf
        @method('PATCH')
        <input type="hidden" name="last_seen_at" id="banUserLastSeen">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="banUserModalTitle"></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <label>اختر سبب سريع:</label>
            <select class="form-select mb-2" id="banUserQuickReason" aria-placeholder="اختر سبب سريع" onchange="document.getElementById('banUserCustomReason').value=this.value">
            </select>
            <label>أو اكتب سبب مخصص:</label>
            <textarea id="banUserCustomReason" name="ban_reason" class="form-control" placeholder="اكتب السبب هنا..." required></textarea>
          <label>مدة الحظر (أيام):</label>
<input type="number" id="banUseruntil" name="ban_until" class="form-control" placeholder="أيام" required>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
            <button type="submit" class="btn btn-danger" id="banUserModalBtn"></button>
          </div>
        </div>
      </form>
    </div>
  </div>



            <!-- Modal واحد فقط -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="deleteUserForm" method="POST">
      @csrf
      @method('DELETE')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteUserModalTitle"></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <label>اختر سبب سريع:</label>
          <select class="form-select mb-2" id="quickReasonSelect" aria-placeholder="اختر سبب سريع" onchange="document.getElementById('customReason').value = this.value">
          </select>

          <label>أو اكتب سبب مخصص:</label>
          <textarea id="customReason" name="delete_reason" class="form-control" placeholder="اكتب السبب هنا..." required></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
          <button type="submit" class="btn btn-danger" id="deleteUserModalBtn"></button>
        </div>
      </div>
    </form>
  </div>
</div>


            <div class="mt-3 px-3">
  {{ $users->appends(request()->query())->links('pagination::bootstrap-5') }}
</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
  {{-- ✅ جدول المستخدمين التي تنتظر الحذف --}}
  <div class="card my-4">
    {{-- <div class="card-header bg-gradient-danger shadow-dark">
      <h6 class="text-white ps-3">Users Pending Deletion</h6>
    </div> --}}
    <div class="card-header bg-gradient-danger shadow-dark d-flex justify-content-between align-items-center">
  <h6 class="text-white ps-3 mb-0">Users Pending Deletion</h6>
  @if($trashedusers->count() > 0)
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
            <th class="text-center">Author</th>
            <th class="text-center">Role</th>
             <th class="text-center">Deletion date</th>
            <th class="text-center">Status at delete</th>
            <th class="text-center">Reason</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($trashedusers as $trasheduser)
          <tr>
            <td>
                      <div class="d-flex px-2 py-1">
                        <div>
                          <img src="{{$trasheduser->image ? asset('storage/'.$trasheduser->image) : asset('assets/img/default-avatar.png') }}"
                               class="avatar avatar-sm me-3 border-radius-lg" alt="{{ $trasheduser->name }}">
                        </div>
                        <div class="d-flex flex-column justify-content-center">
                          <h6 class="mb-0 text-sm">
  <a href="{{ route('users.show', $trasheduser->id) }}">
    {{ $trasheduser->name ?? '-'}}
  </a>
</h6>
                          <p class="text-xs text-secondary mb-0">{{ $trasheduser->email }}</p>
                        </div>
                      </div>
                    </td>
                    <td class="text-center">
                      <p class="text-xs font-weight-bold mb-0">{{ $trasheduser->roles->pluck('name')->join(', ') }}</p>
                    </td>
                    <td class="text-center">{{ $trasheduser->deleted_at ?? '-' }}</td>
                    <td class="text-center">{{ $trasheduser->status ?? '-' }}</td>
            <td class="text-center">{{ $trasheduser->delete_reason ?? '-' }}</td>
            <td class="text-center">
              @can('confirm deletion')
              <form action="{{ route('users.forceDelete', $trasheduser->id) }}" method="POST" style="display:inline-block">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="حذف نهائي">
                     <i class="bi bi-trash me-1"></i> Delete
                </button>
              </form>
              @endcan
              <form action="{{ route('users.restore', $trasheduser->id) }}" method="POST" style="display:inline-block">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-sm btn-warning me-1" style="border-radius: 8px; font-weight: 500;" data-bs-toggle="tooltip" data-bs-placement="top" title="استعادة المستخدم">
                <i class="bi bi-power me-1"></i> Restore
                </button>
              </form>
            </td>
          </tr>
          @empty
          <tr><td colspan="4" class="text-center">لا يوجد مستخدمين بانتظار الحذف</td></tr>
          @endforelse
        </tbody>
      </table>
      <div class="mt-3 px-3">
  {{ $trashedusers->appends(request()->query())->links('pagination::bootstrap-5') }}
</div>
    </div>
  </div>

</div>

<!-- Empty Trash Modal -->
<div class="modal fade" id="emptyTrashModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form action="{{ route('users.emptyTrash') }}" method="POST">
      @csrf
      @method('DELETE')
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title">تأكيد إفراغ السلة</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center">
          ⚠️ هل أنت متأكد أنك تريد حذف جميع المستخدمين المحذوفين نهائيًا؟  
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
let lastUserHtml = $('#pending-users-table').html(); 

setInterval(updateUserDetails, 3600000);

function getCurrentFilters() {
    return $('form').serialize(); // بيجمع كل الفيلدات داخل الفورم
}
function updateUserDetails(skipNotice = false) {
  $.ajax({
    url: "{{ route('users') }}",
    type: 'GET',
    data: getCurrentFilters(),
    dataType: 'html',
    success: function(response) {
      const newBody = $(response).find('#pending-users-table').html();

      if (lastUserHtml !== newBody) {
        $('#pending-users-table').html(newBody);
        lastUserHtml = newBody;
       if(!skipNotice) showNotice('🔄 تم تحديث تفاصيل الطلب');
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

<script>

   $(document).on('click','.btn-delete-user' ,function() {
        const userId = $(this).data('id');
        const status = $(this).data('status');
        const form = $('#deleteUserForm');
        const modalTitle = $('#deleteUserModalTitle');
        const modalBtn = $('#deleteUserModalBtn');
        const quickSelect = $('#quickReasonSelect');
        const customReason = $('#customReason');

        // إعداد الفورم حسب حالة المستخدم
        if(status == '0'){ // Pending
            modalTitle.text('سبب رفض المستخدم');
            modalBtn.text('رفض');
            quickSelect.html(`
            <option value="" selected hidden>اختر سبب سريع</option>
                <option value="مخالفة الشروط">مخالفة الشروط</option>
                <option value="محتوى غير مناسب">محتوى غير مناسب</option>
                <option value="معلومات غير صحيحة">معلومات غير صحيحة</option>
            `);
        } else { // Active / Banned
            modalTitle.text('سبب حذف المستخدم');
            modalBtn.text('حذف');
            quickSelect.html(`
            <option value="" selected hidden>اختر سبب سريع</option>
                <option value="مستخدم مكرر">مستخدم مكرر</option>
                <option value="مخالفة الشروط">مخالفة الشروط</option>
                <option value="نشاط منخفض">نشاط منخفض</option>
            `);
        }

        // تحديث action للفورم
        form.attr('action', 'users/' + userId);
        customReason.val('');
        $('#deleteUserModal').modal('show');
    });


// أزرار الحظر
$(document).on('click', '.btn-ban-user', function(){
    const id = $(this).data('id');
    const status = $(this).data('status');
    const last = $(this).data('last');

    const form = $('#banUserForm');
    const modal = $('#banUserModal');
    const title = $('#banUserModalTitle');
    const quick = $('#banUserQuickReason');
    const custom = $('#banUserCustomReason');
    const until = $('#banUseruntil');
    const btn = $('#banUserModalBtn');

    if(status=='1'){ 
      // Ban → افتح المودال لاختيار السبب
      title.text('سبب حظر المستخدم');
      btn.text('حظر');
      quick.html(`
        <option value="" selected hidden>اختر سبب سريع</option>
        <option value="مخالفة الشروط">مخالفة الشروط</option>
        <option value="محتوى غير مناسب">محتوى غير مناسب</option>
        <option value="موقع غير مناسب">موقع غير مناسب</option>
      `);
      form.attr('action','users/'+id+'/ban');
      $('#banUserLastSeen').val(last);
      custom.val('');
      until.val('');
      modal.modal('show');
    } else { 
      // Unban → أرسل الطلب مباشرة بدون مودال
      $.ajax({
        url: 'users/'+id+'/ban',
        type: 'PATCH',
        data: {
          last_seen_at: last,
          _token: '{{ csrf_token() }}'
        },
        success: function(response){
          showNotice('✅ تم إلغاء الحظر');
         updateUserDetails(true); // true يعني ما تظهر إشعار التحديث 
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
