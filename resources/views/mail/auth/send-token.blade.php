<x-mail::message>
# <center>Código de verificação</center>

Por favor, volte para o aplicativo ou site e insira o código de verificação abaixo para acessar seu sistema.

<x-mail::panel>
<p
style="
font-family: Arial, Helvetica, sans-serif;
font-size: 42px;
text-align: center;
font-weight: bold;
display: block;
margin: 0;
">
{{ $token }}
</p>
</x-mail::panel>

<x-slot:subcopy>
Caso esta solicitação não tenha sido feita por você, ignore este e-mail.
</x-slot:subcopy>

{{ __('mail_auth.gran-permission.salutation') }}
{{ config('app.name') }}
</x-mail::message>
