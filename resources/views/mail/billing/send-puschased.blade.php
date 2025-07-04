<x-mail::message>
    # Compra Realizada com sucesso

    O usuario {{ $userName }} realizou uma compra no valor de {{ $costValue }}.

    Descricao da compra: {{ $title }}
    Desconto: {{ $discount * 100 }}%
    Valor da compra: R$ {{ $costValue }}

    <x-mail::button :url="''">
        Verificar
    </x-mail::button>

    Obrigado,
    {{ config('app.name') }}
</x-mail::message>
