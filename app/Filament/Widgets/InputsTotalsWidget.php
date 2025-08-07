<?php

namespace App\Filament\Widgets;

use App\Enums\CurrentMonthReferenceEnum;
use App\Enums\TypeTransactionEnum;
use App\Models\Transaction;
use Filament\Widgets\ChartWidget;

class InputsTotalsWidget extends ChartWidget
{
    protected static ?string   $heading         = 'Entradas x Saídas';
    protected static string    $color           = 'info';
    protected int|string|array $columnSpan      = '4';
    protected static ?int      $sort            = 7;
    protected static ?string   $pollingInterval = null;
    protected static ?string   $maxHeight       = "200px";

    protected function getData(): array
    {
        $currentMonths = CurrentMonthReferenceEnum::cases();
        $labels        = array_map(function ($month) {
            return ucfirst($month->label());
        }, $currentMonths);

        $input  = $this->transactionsGroupsType(TypeTransactionEnum::input);
        $output = $this->transactionsGroupsType(TypeTransactionEnum::output);

        return [
            'datasets' => [
                [
                    'label'           => 'Entradas',
                    'data'            => array_values($input),
                    'backgroundColor' => 'green',
                    'borderColor'     => 'green',
                    //"tension"         => 2,
                ],
                [
                    'label'           => 'Saídas',
                    'data'            => array_values($output),
                    'backgroundColor' => 'red',
                    'borderColor'     => 'red',
                    //"tension"         => 1,
                ],
            ],
            'labels'   => array_values($labels),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function transactionsGroupsType(TypeTransactionEnum $type): ?array
    {
        $monthly = Transaction::where("monthly", 1)
            ->where("type", $type->value)
            ->sum("value");

        $transactionsGroups =
            Transaction::where("type", $type->value)
                ->get()
                ->groupBy("current_month");

        $year = now()->year;

        $data = [
            "01/$year" => 0,
            "02/$year" => 0,
            "03/$year" => 0,
            "04/$year" => 0,
            "05/$year" => 0,
            "06/$year" => 0,
            "07/$year" => 0,
            "08/$year" => 0,
            "09/$year" => 0,
            "10/$year" => 0,
            "11/$year" => 0,
            "12/$year" => 0,
        ];

        foreach ($data as $index => $month) {
            $data[$index] = $monthly;
            if (isset($transactionsGroups[$index])) {
                $data[$index] = $transactionsGroups[$index]->sum("value");
            }
        }

        return $data;
    }

    public function getColumnSpan(): int|array|string
    {
        return 'full';
    }
}
