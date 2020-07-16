<?php

namespace Winbooks\Models;

use Winbooks\ObjectModel;

class Address extends ObjectModel
{

    public function getType(): string
    {
        return 'Winbooks.TORM.OM.Address, Winbooks.TORM.OM';
    }

}
