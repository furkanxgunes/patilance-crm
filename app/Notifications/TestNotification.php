<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $current;
    protected $total;

    /**
     * Create a new notification instance.
     */
    public function __construct($current, $total)
    {
        $this->current = $current;
        $this->total = $total;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Test Bildirimi #' . $this->current . '/' . $this->total,
            'message' => 'Bu bir test bildirimidir. ' . now()->format('d.m.Y H:i:s'),
            'url' => route('dashboard'),
            'icon' => 'fa-bell'
        ];
    }
}
