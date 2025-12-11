<script src="{{asset('dashboard/assets/js/core/popper.min.js')}}"></script>
  <script src="{{asset('dashboard/assets/js/core/bootstrap.min.js')}}"></script>
  <script src="{{asset('dashboard/assets/js/plugins/perfect-scrollbar.min.js')}}"></script>
  <script src="{{asset('dashboard/assets/js/plugins/smooth-scrollbar.min.js')}}"></script>
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>
  <!-- Github buttons -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="{{asset('dashboard/assets/js/material-dashboard.min.js?v=3.2.0')}}"></script>
  <script>
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.auto-dismiss');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 3000);
    });
});
</script>
<!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {

  // 🔹 فحص أولي عند تحميل الصفحة إذا في شكاوى جديدة
  
  updateNotificationBadge();
  checkUnreadComplaints();


  // 🔹 إعداد Pusher
  var pusher = new Pusher('52640577e6e74198cef6', {
    cluster: 'mt1',
    authEndpoint: '/broadcasting/auth',
  });

      function subscribeChannel(channelName, eventName) {
        var channel = pusher.subscribe(channelName);
        channel.bind(eventName, function(data) {
            showNotification(data);
            if(data.type === 'new_support_ticket'){ 
    $('#support-icon').addClass('has-notifications');
    updateNotificationBadge();
    if ($('#support-table').length){
      updateSupportTable();
    }
    
} else{ 
    updateNotificationBadge();
}

            
        });
    }
    subscribeChannel('private-support-admin-notifications', 'admin_notification');
    subscribeChannel('private-store-admin-notifications', 'store_notification');
    subscribeChannel('private-product-admin-notifications', 'product_notification');
    subscribeChannel('private-order-admin-notifications', 'order_notification');

});

// 🟥 فحص عدد الشكاوى الجديدة من السيرفر
function checkUnreadComplaints() {
  $.get('{{ route("notifications.unreadCount") }}', function(response) {
    console.log('Unread complaints count:', response.unreadSupportc_count);
    if (response.unreadSupportc_count > 0) {
      $('#support-icon').addClass('has-notifications');
    }
  });
}

function updateNotificationBadge() {
  $.get('{{ route("notifications.unreadCount") }}', function(response) {
      const badge = $('#notification-badge');
      if(response.unreadNotifications_count > 0){
          badge.text(response.unreadNotifications_count);
          badge.show();
      } else {
          badge.hide();
      }
  });
}


// 🟢 تحديث الجدول بعد وصول إشعار
let lastSupportTable = $('#support-table tbody').html();
function updateSupportTable() {
  $.ajax({
    url: "{{ route('admin.supports.index') }}",
    type: 'GET',
    dataType: 'html',
    success: function(response) {
      const newTbody = $(response).find('#support-table tbody').html();
      if (lastSupportTable !== newTbody) {
        $('#support-table tbody').html(newTbody);
        lastSupportTable = newTbody;
      }
    },
    error: function(err) { console.error(err); }
  });
}




let lastNotificationType = null;
// 💬 عرض إشعار جميل عند وصول تنبيه جديد
function showNotification(data) {
  lastNotificationType = data.type;
  const container = document.getElementById('notifications-container');
    let icon = '⚠️';
    if(data.type === 'product_edited') {icon = '🛠️';}
    if(data.type === 'store_edited') {icon = '🏪'; }
    if(data.type === 'order_accept') { icon = '✅';}
    if(data.type === 'new_support_ticket') { icon = '📩';}
  const notif = document.createElement('div');
  notif.classList.add('notification', 'warning');
     notif.innerHTML = `
        <span class="icon">${icon}</span>
        <div class="content">
            <strong>${data.message || ''}</strong>
            <p>${data.subject || ''}</p>
        </div>
    `;
  notif.onclick = function() {
     if(data.type === 'new_support_ticket'){
            window.location.href = '/admin/supports/' + (data.id || '');
        } else if(data.type === 'product_edited'){
            window.location.href = '/admin/products/'+ (data.id || '');
        } else if(data.type === 'store_edited'){
            window.location.href = '/admin/stores/'+ (data.id || '');
        }
        else if(data.type === 'order_accept'){
            window.location.href = '/admin/orders/'+ (data.id || '');
        }
  };
  container.prepend(notif);
  setTimeout(() => notif.remove(), 10000);
}
</script>
  @yield('scripts')