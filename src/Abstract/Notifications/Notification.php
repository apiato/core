<?php

namespace Apiato\Abstract\Notifications;

use Illuminate\Notifications\Notification as LaravelNotification;

abstract class Notification extends LaravelNotification
{
    public function via($notifiable): array
    {
        return config('notification.channels');
    }
}