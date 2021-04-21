<?php

namespace Whitecube\Winbooks\Models\Accounting;

use Whitecube\Winbooks\ObjectModel;

class PayCode extends ObjectModel
{

    public function getType(): string
    {
        return 'Winbooks.TORM.OM.Accounting.PayCode, Winbooks.TORM.OM';
    }

}
