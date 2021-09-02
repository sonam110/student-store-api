@component('mail::message')
# Introduction

The body of your message.

@component('mail::button', ['url' => ''])
Button Text
{{$otp}}
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
