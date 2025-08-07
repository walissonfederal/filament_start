<?php

namespace App\Filament\Widgets;

use App\Enums\TypeTransactionEnum;
use App\Models\Transaction;
use Filament\Widgets\Widget;

class FinancialTotalsWidget extends Widget
{
    protected static string $view      = 'filament.widgets.financial-totals-widget';
    protected               $listeners = ['transactionUpdated' => 'updateTotals'];
    protected static ?int      $sort       = 6;

    public $inputs;
    public $outputs;
    public $balance;

    public function poll()
    {
        $this->updateTotals();
    }

    public function mount()
    {
        $this->updateTotals();
    }

    public function updateTotals()
    {
        $totalInput  = Transaction::whereIn('type', [
            TypeTransactionEnum::input,
        ])->where("paid", true)->sum('value');
        $totalOutput = Transaction::whereIn('type', [
            TypeTransactionEnum::output,
        ])->where("paid", true)->sum('value');

        $this->inputs  = Transaction::where('type', TypeTransactionEnum::input)->sum('value');
        $this->outputs = Transaction::where('type', TypeTransactionEnum::output)->sum('value');
        $this->balance = $totalInput - $totalOutput;
    }

    protected function getViewData(): array
    {
        return [
            'inputs'  => $this->inputs,
            'outputs' => $this->outputs,
            'balance' => $this->balance,
        ];
    }

    public function getColumnSpan(): int|array|string
    {
        return 'full';
    }
}
