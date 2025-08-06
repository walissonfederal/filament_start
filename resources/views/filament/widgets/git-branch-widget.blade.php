<x-filament::widget>
    <x-filament::card>
        <h2>{{ env("APP_NAME") }} Versão Atual: {{ $this->getBranch() }}</h2>
        <div style="height: 16px !important;"></div>
    </x-filament::card>
</x-filament::widget>
