@component('mail::message')
# Password Reset Request

Hello,

You are receiving this email because we received a password reset request for your administrator account.

Your password reset token is:

@component('mail::panel')
**{{ $token }}**
@endcomponent

Please copy this token and paste it into the reset form to create a new password.

If you did not request a password reset, no further action is required.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
