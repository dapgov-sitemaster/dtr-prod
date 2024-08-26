<x-mail::message>
# Good day!
We have received a password change request for your eDTR account.

This link will expire in 24 hours. If you did not request a password change, please ignore this email, no changes will be made to your account.

<x-mail::button :url="route('forgot-password.reset', ['token'=>$token])">
Reset password
</x-mail::button>

Regards,<br>
ICTD Team~
</x-mail::message>
