<?php

namespace Winbooks\Models;

use Winbooks\ObjectModel;

class Customer extends ObjectModel
{

    public function getType(): string
    {
        return 'Winbooks.TORM.OM.Customer, Winbooks.TORM.OM';
    }

}
