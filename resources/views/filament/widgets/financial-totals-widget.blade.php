<x-filament-widgets::widget>
    <x-filament::section>

        <h3 class="text-lg font-bold mb-12">Métricas Financeiras (Se o valor estiver inconsistente aperte F5)</h3>
        <table class="w-full table-auto border-collapse border border-gray-300 rounded-lg">
            <thead>
            <tr class="bg-gray-100">
                <th class="p-2 border border-gray-300 text-left text-sm font-semibold text-gray-700">Receitas</th>
                <th class="p-2 border border-gray-300 text-left text-sm font-semibold text-gray-700">Despesas</th>
                <th class="p-2 border border-gray-300 text-left text-sm font-semibold text-gray-700">Patrimônio</th>
                <th class="p-2 border border-gray-300 text-left text-sm font-semibold text-gray-700">Saldo</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td class="p-2 border border-gray-300 text-sm text-gray-900">
                    R$ {{ number_format($inputs, 2, ',', '.') }}</td>
				<td class="p-2 border border-gray-300 text-sm text-gray-900">
					R$ {{ number_format($outputs, 2, ',', '.') }}</td>
				<td class="p-2 border border-gray-300 text-sm text-gray-900"></td>
				<td class="p-2 border border-gray-300 text-sm text-gray-900">
					R$ {{ number_format($balance, 2, ',', '.') }}</td>
            </tr>
            </tbody>
        </table>

    </x-filament::section>
</x-filament-widgets::widget>
