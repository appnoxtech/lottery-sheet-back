<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomerAcceptanceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $lotteryRequest;

    /**
     * Create a new message instance.
     *
     * @param $lotteryRequest
     * @return void
     */
    public function __construct($lotteryRequest)
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
        return $this->subject('Lottery Request Processed - Curacao Lottery')
                    ->markdown('emails.customer_acceptance');
    }
}
