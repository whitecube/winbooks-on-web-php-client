<?php

namespace Winbooks\Models\Logistics;

use Winbooks\ObjectModel;

class StockTransaction extends ObjectModel
{

    public function getType(): string
    {
        return 'Winbooks.TORM.OM.Logistics.StockTransaction, Winbooks.TORM.OM';
    }

}
