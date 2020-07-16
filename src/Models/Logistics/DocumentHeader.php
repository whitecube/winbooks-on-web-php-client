<?php

namespace Winbooks\Models\Logistics;

use Winbooks\ObjectModel;

class DocumentHeader extends ObjectModel
{

    public function getType(): string
    {
        return 'Winbooks.TORM.OM.Logistics.DocumentHeader, Winbooks.TORM.OM';
    }

}
