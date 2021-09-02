
@component('mail::message')
# {{ $details['title'] }}
  
{{$details['body']}}. 
   
<!-- @component('mail::button', ['url' => ''])
Reset Password
@endcomponent -->
   
Thanks,<br>
{{ config('app.name') }}
@endcomponent

