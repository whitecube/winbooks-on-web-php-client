<?php

namespace Whitecube\Winbooks\Models;

use Whitecube\Winbooks\ObjectModel;

class Customer extends ObjectModel
{

    public function getType(): string
    {
        return 'Winbooks.TORM.OM.Customer, Winbooks.TORM.OM';
    }

}
