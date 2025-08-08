<x-filament-panels::page>
    <div class="border-b border-gray-200 flex space-x-4">
        <button
            wire:click="switchTab('orders')"
            class="px-3 py-2 font-medium text-sm rounded-t-lg
            {{ $this->activeTab === 'orders' ? 'text-green-600 border-b-2 border-green-600' : 'text-gray-500 hover:text-gray-700' }}"
        >
        <span class="flex items-center">
            <x-heroicon-o-truck class="w-4 h-4 mr-2"/>
            Pedidos
        </span>
        </button>

        <button
                wire:click="switchTab('transactions')"
                class="px-3 py-2 font-medium text-sm rounded-t-lg
            {{ $this->activeTab === 'transactions' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}"
        >
        <span class="flex items-center">
            <x-heroicon-o-currency-dollar class="w-4 h-4 mr-2"/>
            Faturas
        </span>
        </button>
    </div>

    <div class="mt-4">
        <h2 class="text-xl font-semibold mb-4">
            {{ $this->tabName }}
        </h2>
        {{ $this->table }}
    </div>
</x-filament-panels::page>
