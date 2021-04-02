<?php

namespace Whitecube\Winbooks\Models\Logistics;

use Whitecube\Winbooks\ObjectModel;

class DocumentHeader extends ObjectModel
{

    public function getType(): string
    {
        return 'Winbooks.TORM.OM.Logistics.DocumentHeader, Winbooks.TORM.OM';
    }

}
