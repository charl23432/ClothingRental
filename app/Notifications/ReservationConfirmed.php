<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReservationConfirmed extends Notification
{
    use Queueable;

    protected $reservation;

    public function __construct($reservation)
    {
        $this->reservation = $reservation;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Reservation Confirmed',
            'message' => 'Your reservation for ' . ($this->reservation->product->item_name ?? 'item') . ' has been confirmed.',
            'image' => $this->reservation->product->image ?? null,
            'reservation_id' => $this->reservation->id,
            'status' => $this->reservation->status,
        ];
    }
}