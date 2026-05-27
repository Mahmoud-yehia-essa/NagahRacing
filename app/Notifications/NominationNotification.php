<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NominationNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct($userCreatNomination,$camelRoundParticipation,$festival,$round)
    {
         $this->userCreatNomination = $userCreatNomination;
         $this->camelRoundParticipation = $camelRoundParticipation;


        //   $festival,$round
         $this->festival = $festival;
         $this->round = $round;

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
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [


                'type' => 'ترشيح جديد',
        'message' => 'تمت اضافة ترشيح جديد',
        'userCreatNomination' => $this->userCreatNomination,
        'camelRoundParticipation' => $this->camelRoundParticipation,

      'festival' => $this->festival,
        'round' => $this->round,

        ];
    }
}
