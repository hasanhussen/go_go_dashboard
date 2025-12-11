@extends('admin.layouts.master')

@section('content')
<div class="container-fluid py-2">
  @include('admin.partials.alerts')
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card my-4 shadow">
        <div class="card-header bg-gradient-dark text-white">
          <h6 class="mb-0" style="color: white">إضافة مستخدم جديد</h6>
        </div>
        <div class="card-body">
          <form action="{{ route('users.store') }}" method="POST">
            @csrf

            {{-- الاسم --}}
            <div class="mb-3">
              <label for="name" class="form-label fw-bold">الاسم</label>
              <input type="text" name="name" id="name" 
                     class="form-control @error('name') is-invalid @enderror" 
                     value="{{ old('name') }}" required>
              @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- الايميل --}}
            <div class="mb-3">
              <label for="email" class="form-label fw-bold">البريد الإلكتروني</label>
              <input type="email" name="email" id="email" 
                     class="form-control @error('email') is-invalid @enderror" 
                     value="{{ old('email') }}" required>
              @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- رقم الهاتف --}}
            <div class="mb-3">
              <label for="phone" class="form-label fw-bold">الهاتف</label>
              <input type="text" name="phone" id="phone" 
                     class="form-control @error('phone') is-invalid @enderror" 
                     value="{{ old('phone') }}" required>
              @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- الجنس --}}
            <div class="mb-3">
              <label class="form-label fw-bold">الجنس</label>
              <select name="gender" class="form-select">
                <option value="">اختر</option>
                <option value="0" {{ old('gender') === '0' ? 'selected' : '' }}>ذكر</option>
                <option value="1" {{ old('gender') === '1' ? 'selected' : '' }}>أنثى</option>
              </select>
            </div>

            {{-- الدور --}}
            <div class="mb-3">
              <label class="form-label fw-bold">الدور</label>
              <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                <option value="">اختر الدور</option>
                @foreach($roles as $role)
                  <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                    {{$role->name}}
                  </option>
                @endforeach
              </select>
              @error('role')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- كلمة المرور --}}
            <div class="mb-3">
              <label for="password" class="form-label fw-bold">كلمة المرور</label>
              <input type="password" name="password" id="password" 
                     class="form-control @error('password') is-invalid @enderror" required>
              @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- تأكيد كلمة المرور --}}
            <div class="mb-3">
              <label for="password_confirmation" class="form-label fw-bold">تأكيد كلمة المرور</label>
              <input type="password" name="password_confirmation" id="password_confirmation" 
                     class="form-control" required>
            </div>

            {{-- الحالة --}}
            <div class="mb-3">
              <label class="form-label fw-bold">الحالة</label>
              <select name="status" class="form-select">
                <option value="">اختر الحالة</option>
                <option value="0" {{ old('status') === '0' ? 'selected' : '' }}>Pending</option>
                <option value="1" {{ old('status') === '1' ? 'selected' : '' }}>Active</option>
                <option value="2" {{ old('status') === '2' ? 'selected' : '' }}>Banned</option>
              </select>
            </div>

            <div class="d-flex justify-content-end">
              <button type="submit" class="btn btn-success">إضافة المستخدم</button>
            </div>

          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
