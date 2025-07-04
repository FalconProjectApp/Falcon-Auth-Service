<x-mail::message>
# {{ __('mail_auth.gran-permission.greeting', ['name' => $name]) }}

{{ __('mail_auth.gran-permission.line.1') }}

{{ __('mail_auth.gran-permission.device', ['device' => $device]) }}
{{ __('mail_auth.gran-permission.ip', ['ip' => $ip]) }}

<x-mail::button :url="$url">{{ __('mail_auth.gran-permission.action') }}</x-mail::button>

<x-slot:subcopy>
{{ __('mail_auth.gran-permission.subcopy', ['actionText' => __('mail_auth.gran-permission.action')]) }} <span class="break-all">[{{ $url }}]({{ $url }})</span>
</x-slot:subcopy>

{{ __('mail_auth.gran-permission.salutation') }}
{{ config('app.name') }}
</x-mail::message>
