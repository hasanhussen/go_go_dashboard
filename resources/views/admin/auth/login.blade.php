<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-card {
            max-width: 400px;
            margin: 80px auto;
            padding: 30px;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .btn-login {
            background-color: black;
            color: white;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center mb-4">
        <img src="{{ asset('dashboard/app-assets/img/logo.png') }}" width="150" alt="Logo">
    </div>

    <h4 class="text-center mb-4">تسجيل الدخول</h4>

    {{-- رسالة النجاح --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- عرض الأخطاء --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('login') }}" method="POST">
        @csrf
        <div class="form-group">
            <input type="text" name="email" class="form-control" placeholder="البريد الإلكتروني أو الهاتف" required>
        </div>
        <div class="form-group">
            <input type="password" name="password" class="form-control" placeholder="كلمة السر" required>
        </div>
        <button type="submit" class="btn btn-login btn-block">دخول</button>
    </form>
</div>

</body>
</html>
