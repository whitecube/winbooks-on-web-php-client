<?php

namespace Whitecube\Winbooks\Models\Logistics;

use Whitecube\Winbooks\ObjectModel;

class StockTransaction extends ObjectModel
{

    public function getType(): string
    {
        return 'Winbooks.TORM.OM.Logistics.StockTransaction, Winbooks.TORM.OM';
    }

}
