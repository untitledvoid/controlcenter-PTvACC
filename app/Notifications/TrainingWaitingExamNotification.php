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
            'Your training for ' . $this->training->getInlineRatings() . ' in ' . Area::find($this->training->area_id)->name . ' has progressed to the examination stage. You will soon be given access to the theoretical exam and a solo endorsement.',
            "Please note that the CPT **must be scheduled** within the following time windows:  \nWeekdays: 17:30 - 22:00 UTC  \nSaturday: 14:30 - 22:00 UTC  \nSunday: 14:30 - 19:00 UTC",
            'Requests submitted outside of these times will be automatically declined.',
            'To schedule your CPT, please [click here to send us your availability.](mailto:caladon.evans@portugal-vacc.org?subject=CPT%20Availability&body=Name%3A%0ACID%3A%0ARating%3A%20%0ADesired%20airport%20(ICAO)%3A%20%0AAvailability%3A' . urlencode($this->training->getInlineRatings()) . '%0A%5BWrite%20your%20availability%20here%5D)',
            "Best of luck with your exam!  \nPortugal vACC Training Department",
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
