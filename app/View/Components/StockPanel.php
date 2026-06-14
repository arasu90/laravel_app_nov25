<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class StockPanel extends Component
{
    public $title;
    public $collapseId;
    public $stocks;
    public $type;
    
    /**
     * Create a new component instance.
     */
    public function __construct($title, $collapseId, $stocks, $type = 'danger')
    {
        $this->title = $title;
        $this->collapseId = $collapseId;
        $this->stocks = $stocks;
        $this->type = $type;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.stock-panel');
    }
}
