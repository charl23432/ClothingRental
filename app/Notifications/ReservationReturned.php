<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReservationReturned extends Notification
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
            'title' => 'Reservation Returned',
            'message' => 'Your reservation for ' . ($this->reservation->product->item_name ?? 'item') . ' was returned.',
            'image' => $this->reservation->product->image ?? null,
            'reservation_id' => $this->reservation->id,
            'status' => $this->reservation->status,
        ];
    }
}