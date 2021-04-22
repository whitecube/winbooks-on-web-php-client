<?php

namespace Whitecube\Winbooks\Models;

use Whitecube\Winbooks\ObjectModel;

class ThirdAddress extends ObjectModel
{
    /**
     * Get the object model's singular name
     *
     * @return string
     * @throws \ReflectionException
     */
    public function getOM(): string
    {
        return 'Third_Address';
    }

    /**
     * Get the object model's $type string
     *
     * @return string
     */
    public function getType(): string
    {
        return 'Winbooks.TORM.OM.Third_Address, Winbooks.TORM.OM';
    }
}
