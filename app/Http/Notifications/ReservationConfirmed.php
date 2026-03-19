<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReservationConfirmed extends Notification
{
    use Queueable;

    protected $reservation;

    /**
     * Create a new notification instance.
     *
     * @param Reservation $reservation
     */
    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
   public function via($notifiable)
{
    return ['database'];
}

public function toDatabase($notifiable)
{
    return [
        'title' => 'Reservation Confirmed',
        'message' => "Your reservation for {$this->reservation->product->item_name} on " . 
                     date('Y-m-d H:i', strtotime($this->reservation->rent_time)) . " has been confirmed.",
        'image' => $this->reservation->product->image,
        'status' => $this->reservation->status,
        'reservation_id' => $this->reservation->id,
    ];
}   
}
