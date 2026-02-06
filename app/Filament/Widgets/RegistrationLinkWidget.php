<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class RegistrationLinkWidget extends Widget
{
    protected string $view = 'filament.widgets.registration-link-widget';
    
    protected int | string | array $columnSpan = 'full';
}
