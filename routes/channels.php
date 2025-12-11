<?php

use Illuminate\Support\Facades\Broadcast;

// Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });

Broadcast::channel('support-admin-notifications', function ($user) {
    // ترجع true إذا المستخدم admin
     return $user->hasRole('admin') || $user->hasRole('editor');
});

Broadcast::channel('product-admin-notifications', function ($user) {
    // ترجع true إذا المستخدم admin
     return $user->hasRole('admin') || $user->hasRole('editor');
});

Broadcast::channel('store-admin-notifications', function ($user) {
    // ترجع true إذا المستخدم admin
     return $user->hasRole('admin') || $user->hasRole('editor');
});

Broadcast::channel('order-admin-notifications', function ($user) {
    // ترجع true إذا المستخدم admin
     return $user->hasRole('admin') || $user->hasRole('editor');
});

