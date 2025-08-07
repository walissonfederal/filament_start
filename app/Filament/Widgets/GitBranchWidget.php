<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class GitBranchWidget extends Widget
{
    protected static string    $view       = 'filament.widgets.git-branch-widget';
    protected static ?string   $maxHeight  = "70px";
    protected static ?int      $sort       = 0;
    protected int|string|array $columnSpan = '1';

    public function getBranch(): string
    {
        return env("APP_VERSION", "beta");
    }
}
