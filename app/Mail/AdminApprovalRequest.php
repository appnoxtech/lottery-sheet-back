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
                    ->view('emails.admin_approval_industrial');
    }
}
