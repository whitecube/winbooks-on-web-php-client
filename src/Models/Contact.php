<?php

namespace Winbooks\Models;

use Winbooks\ObjectModel;

class Contact extends ObjectModel
{

    public function getType(): string
    {
        return 'Winbooks.TORM.OM.Contact, Winbooks.TORM.OM';
    }

}
