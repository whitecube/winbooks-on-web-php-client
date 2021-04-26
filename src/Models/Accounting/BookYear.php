<?php

namespace Whitecube\Winbooks\Models\Accounting;

use Whitecube\Winbooks\ObjectModel;

class BookYear extends ObjectModel
{

    public function getType(): string
    {
        return 'Winbooks.TORM.OM.Accounting.BookYear, Winbooks.TORM.OM';
    }

}
