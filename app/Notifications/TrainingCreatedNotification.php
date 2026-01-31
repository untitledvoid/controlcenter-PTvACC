<?php

namespace App\Notifications;

use App\Mail\TrainingMail;
use App\Models\Area;
use App\Models\Training;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TrainingCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $training;

    private $contactMail;

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

        $waitingTime = ! empty($this->training->area->waiting_time) ? $this->training->area->waiting_time : 'unknown';

        $textLines = [
            'We hereby confirm that we have received your training request for ' . $this->training->getInlineRatings() . ' within the ' . Area::find($this->training->area_id)->name . '.',
            'While we are unable to provide an exact timeframe, please note that most training requests are typically processed within approximately three months.',
            'During this waiting period, we kindly ask that you remain active by controlling regularly and completing a minimum of 5 hours every 30 days to maintain your eligibility for training.',
            'Please note that if you become inactive, your training request will be removed. You are welcome to reapply once you are available to control again.',
            "Happy controlling and see you online!  \nPortugal vACC Training Department",
        ];

        $area = Area::find($this->training->area_id);
        if (isset($area->template_newreq)) {
            $textLines[] = $area->template_newreq;
        }

        // Find staff who wants notification of new training request
        $bcc = User::allWithGroup(2, '<=')->where('setting_notify_newreq', true);

        foreach ($bcc as $key => $user) {
            if (! $user->isModeratorOrAbove($this->training->area)) {
                $bcc->pull($key);
            }
        }

        $contactMail = $area->contact;

        return (new TrainingMail('New Training Request Confirmation', $this->training, $textLines, $contactMail))
            ->to($this->training->user->personalNotificationEmail, $this->training->user->name)
            ->bcc($bcc->pluck('workNotificationEmail'));
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
