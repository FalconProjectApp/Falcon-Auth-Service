<x-mail::message>
# {{ __('mail_auth.forgot.greeting', ['name' => $name]) }}

{{ __('mail_auth.forgot.line.1') }}

<x-mail::button :url="$url">{{ __('mail_auth.forgot.action') }}</x-mail::button>

{{ __('mail_auth.forgot.line.2', ['count' => $expireToken]) }}
{{ __('mail_auth.forgot.line.3') }}

<x-slot:subcopy>
{{ __('mail_auth.forgot.subcopy', ['actionText' => __('mail_auth.forgot.action')]) }} <span class="break-all">[{{ $url }}]({{ $url }})</span>
</x-slot:subcopy>

{{ __('mail_auth.forgot.salutation') }}
{{ config('app.name') }}
</x-mail::message>
