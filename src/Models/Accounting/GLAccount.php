<?php

namespace Whitecube\Winbooks\Models\Accounting;

use Whitecube\Winbooks\ObjectModel;

class GLAccount extends ObjectModel
{

    public function getType(): string
    {
        return 'Winbooks.TORM.OM.Accounting.GLAccount, Winbooks.TORM.OM';
    }

}
