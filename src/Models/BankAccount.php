<?php

namespace Whitecube\Winbooks\Models;

use Whitecube\Winbooks\ObjectModel;

class BankAccount extends ObjectModel
{

    public function getType(): string
    {
        return 'Winbooks.TORM.OM.BankAccount, Winbooks.TORM.OM';
    }

}
