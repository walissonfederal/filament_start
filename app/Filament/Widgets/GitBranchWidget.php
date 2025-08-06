<?php
namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Config;

class GitBranchWidget extends Widget
{
    protected static string $view = 'filament.widgets.git-branch-widget';

    protected static ?string $maxHeight = "70px";

    protected static ?int $sort = 2;

    // protected int|string|array $columnSpan = '2';

    public function getBranch(): string
    {
        return Config::get('app.version');
    }
}
