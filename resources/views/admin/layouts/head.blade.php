<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="{{asset('dashboard/assets/img/apple-icon.png')}}">
  <link rel="icon" type="image/png" href="{{asset('dashboard/assets/img/favicon.png')}}">
  <title>
    Material Dashboard 3 by Creative Tim
  </title>
  <!--     Fonts and icons     -->
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
  <!-- Nucleo Icons -->
  <link href="{{asset('dashboard/assets/css/nucleo-icons.css')}}" rel="stylesheet" />
  <link href="{{asset('dashboard/assets/css/nucleo-svg.css')}}" rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <!-- Material Icons -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
  <!-- CSS Files -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <link id="pagestyle" href="{{asset('dashboard/assets/css/material-dashboard.css?v=3.2.0')}}" rel="stylesheet" />
  <style>
.alert {
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    font-weight: 500;
    opacity: 0.95;
}

#notifications-container {
    position: fixed;
    top: 20px;
    right: 20px;
    width: 350px;
    max-width: 90%;
    z-index: 9999;
}

.notification {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-direction: row-reverse; /* 🔥 هي السطر المهم */
    gap: 16px;
    background-color: #ff9800;
    padding: 14px 18px;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    color: #fff;
    width: 100%;
    max-width: 380px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 14px;
    direction: rtl; /* ليتعامل النص بالعربي */
}

.notification .content {
    text-align: right;
}

.notification .icon {
    font-size: 30px;
    flex-shrink: 0;
    position: relative;
    top: -3px;
}
.notification .content p {
    margin: 2px 0 0 0;
}

.notification .time {
    font-size: 0.8em;
    opacity: 0.8;
    white-space: nowrap;
}

.notification.success { background-color: #4CAF50; } /* أخضر */
.notification.info    { background-color: #2196F3; } /* أزرق */
.notification.warning { background-color: #ff9800; } /* برتقالي */
.notification.complaint { background-color: #f44336; } /* أحمر */

@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to   { transform: translateX(0); opacity: 1; }
}

@keyframes fadeOut {
    to { opacity: 0; height: 0; margin: 0; padding: 0; }
}








#support-icon.has-notifications::after {
  content: "";
  position: absolute;
  top: 4px;
  right: 6px;
  width: 10px;
  height: 10px;
  background-color: red;
  border-radius: 50%;
  animation: pulse 1s infinite alternate;
}

@keyframes pulse {
  from {
    transform: scale(1);
    opacity: 1;
  }
  to {
    transform: scale(1.4);
    opacity: 0.6;
  }
}


</style>
  @yield('css')
</head>