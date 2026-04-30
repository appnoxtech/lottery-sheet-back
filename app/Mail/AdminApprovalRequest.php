<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class AdminApprovalRequest extends Mailable
{
    use Queueable, SerializesModels;

    public $newUser;
    public $approvalUrl;

    public function __construct(User $newUser)
    {
        $this->newUser = $newUser;
        $this->approvalUrl = URL::signedRoute(
            'admin.approve',
            ['id' => $newUser->id, 'hash' => sha1($newUser->email)]
        );
    }

    public function build()
    {
        return $this->subject('New Admin Registration Approval')
                    ->html('
                        <h2>New Admin Registration</h2>
                        <p>A new user has registered and is waiting for your approval to access the admin panel.</p>
                        <p><strong>Name:</strong> ' . $this->newUser->name . '</p>
                        <p><strong>Email:</strong> ' . $this->newUser->email . '</p>
                        <br>
                        <a href="' . $this->approvalUrl . '" style="padding: 10px 20px; background: #6366f1; color: white; text-decoration: none; border-radius: 5px;">Approve Admin Access</a>
                        <br><br>
                        <p>If you did not expect this, you can ignore this email.</p>
                    ');
    }
}
