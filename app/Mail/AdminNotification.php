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
                    ->html('
                        <h2>New Lottery Request Received</h2>
                        <p><strong>Name:</strong> ' . $this->lotteryRequest->name . '</p>
                        <p><strong>Email:</strong> ' . $this->lotteryRequest->email . '</p>
                        <p><strong>Phone:</strong> ' . $this->lotteryRequest->phone . '</p>
                        <p><strong>Type:</strong> ' . $this->lotteryRequest->lottery_type . '</p>
                        <p><strong>Numbers:</strong> ' . implode(", ", $this->lotteryRequest->lottery_numbers) . '</p>
                        <p><strong>Amount per Number:</strong> ' . $this->lotteryRequest->amount . '</p>
                        <p><strong>Notes:</strong> ' . ($this->lotteryRequest->notes ?? 'N/A') . '</p>
                    ');
    }
}
