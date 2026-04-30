<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminPendingNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('Admin Registration Received')
            ->html('
                        <h2>Hello ' . $this->user->name . ',</h2>
                        <p>Thank you for registering as an admin. Your application has been received and is currently being reviewed by our Team.</p>
                        <p>You will receive another email with your verification link once your account has been approved.</p>
                        <br>
                        <p>Best regards,<br>Lottery System Team</p>
                    ');
    }
}
