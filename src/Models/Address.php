<?php

namespace Whitecube\Winbooks\Models;

use Whitecube\Winbooks\ObjectModel;

class Address extends ObjectModel
{

    public function getType(): string
    {
        return 'Winbooks.TORM.OM.Address, Winbooks.TORM.OM';
    }

}
