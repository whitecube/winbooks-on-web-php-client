<?php

namespace Whitecube\Winbooks\Models;

use Whitecube\Winbooks\ObjectModel;

class Currency extends ObjectModel
{

    public function getType(): string
    {
        return 'Winbooks.TORM.OM.Currency, Winbooks.TORM.OM';
    }

}
