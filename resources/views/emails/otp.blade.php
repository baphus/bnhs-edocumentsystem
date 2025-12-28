<x-mail::message>
# Your Verification Code

@if($purpose === 'request')
You are requesting to submit a document request on the BNHS eDocument System.
@else
You are attempting to track your document request on the BNHS eDocument System.
@endif

Your one-time verification code is:

<x-mail::panel>
<div style="text-align: center; font-size: 32px; font-weight: bold; letter-spacing: 8px; font-family: monospace;">
{{ $otp }}
</div>
</x-mail::panel>

This code will expire in **{{ $expiresIn }}**.

**Important:** If you did not request this code, please ignore this email.

Thanks,<br>
{{ config('app.name') }}

<x-mail::subcopy>
Bato National High School<br>
Toledo City, Cebu
</x-mail::subcopy>
</x-mail::message>

