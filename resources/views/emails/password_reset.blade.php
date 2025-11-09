@component('mail::message')
# Password Reset Request

We received a request to reset your password.  
Click the button below to proceed.

@component('mail::button', ['url' => $resetUrl])
Reset Password
@endcomponent

If you didn’t request this, no action is required.

This link will expire in 24 hours.  
Thanks,  
{{ config('app.name') }}
@endcomponent
