<?php

namespace Whitecube\Winbooks\Models\Logistics;

use Whitecube\Winbooks\ObjectModel;

class Item extends ObjectModel
{

    public function getType(): string
    {
        return 'Winbooks.TORM.OM.Logistics.Item, Winbooks.TORM.OM';
    }

}
