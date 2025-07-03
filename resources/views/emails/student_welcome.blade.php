@component('mail::message')
# Welcome, {{ $student->first_name }}!

Your portal is ready. Log in with:

- **Email:** {{ $student->email }}
- **Password:** {{ $rawPassword }}

@component('mail::button', ['url' => url('/student/login')])
Student Login
@endcomponent

Thanks,<br>
Nicolites Portal
@endcomponent
