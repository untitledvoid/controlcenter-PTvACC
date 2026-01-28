<?php

namespace App\Notifications;

use App\Mail\TrainingMail;
use App\Models\Area;
use App\Models\Training;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TrainingPreStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $training;

    /**
     * Create a new notification instance.
     *
     * @param  string  $key
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
        $textLines[] = 'We would like to inform you that your training request for ' . $this->training->getInlineRatings() . ' in ' . Area::find($this->training->area_id)->name . ' has now been assigned to pre-training.';
        'Please proceed with completing the required Moodle materials and inform your mentor once you have finished and are ready to begin your training sessions.';
        'We would also like to remind you that throughout your training, you are expected to remain active by completing a minimum of 5 hours every 30 days in order to maintain your eligibility for training.';
        'Your mentor will be supporting you on a voluntary basis during your training. We therefore kindly ask that you are punctual and arrive well prepared for each session to make the most of the time available.';
        "Best of luck with your training,  \nPortugal vACC Training Department";
        $area = Area::find($this->training->area_id);
        if (isset($area->template_pretraining)) {
            $textLines[] = $area->template_pretraining;
        }

        $contactMail = Area::find($this->training->area_id)->contact;

        return (new TrainingMail('Training Assigned', $this->training, $textLines, $contactMail))
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
