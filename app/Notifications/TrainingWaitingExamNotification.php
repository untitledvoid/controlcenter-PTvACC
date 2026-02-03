<?php

namespace App\Notifications;

use App\Mail\TrainingMail;
use App\Models\Area;
use App\Models\Training;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TrainingWaitingExamNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $training;

    /**
     * Create a new notification instance.
     */
    public function __construct(Training $training)
    {
        $this->training = $training;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return TrainingMail
     */
    public function toMail($notifiable)
    {
        $textLines = [
            'Congratulations! Your training for ' . $this->training->getInlineRatings() . ' in ' . Area::find($this->training->area_id)->name . ' has progressed to the examination stage.',
            'Your mentor will contact you soon to schedule your examination.',
            'Please ensure you are well prepared and have reviewed all the necessary materials.',
            'Good luck with your upcoming exam!',
        ];

        $area = Area::find($this->training->area_id);
        if (isset($area->template_waitingexam)) {
            $textLines[] = $area->template_waitingexam;
        }

        $contactMail = $area->contact;

        return (new TrainingMail('Training Ready for Examination', $this->training, $textLines, $contactMail))
            ->to($this->training->user->personalNotificationEmail, $this->training->user->name);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'training_id' => $this->training->id,
        ];
    }
}
