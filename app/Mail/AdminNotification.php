<?php

namespace App\Mail;

use App\Models\LotteryRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $lotteryRequest;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(LotteryRequest $lotteryRequest)
    {
        $this->lotteryRequest = $lotteryRequest;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('New Lottery Request - ' . $this->lotteryRequest->name)
                    ->view('emails.admin_notification_industrial');
    }
}
