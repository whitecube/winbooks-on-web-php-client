<?php

namespace Whitecube\Winbooks\Models\Accounting;

use Whitecube\Winbooks\ObjectModel;

class BookPeriod extends ObjectModel
{

    public function getType(): string
    {
        return 'Winbooks.TORM.OM.Accounting.BookPeriod, Winbooks.TORM.OM';
    }

}
