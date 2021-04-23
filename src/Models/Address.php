<?php

namespace Whitecube\Winbooks\Models;

use Whitecube\Winbooks\ObjectModel;

class Address extends ObjectModel
{
    /**
     * Get the object model's $type string
     *
     * @return string
     */
    public function getType(): string
    {
        return 'Winbooks.TORM.OM.Address, Winbooks.TORM.OM';
    }

    /**
     * Define wether the single add route should include
     * the model's Code/Id or not...
     *
     * @return bool
     */
    public function hasCodeInCreateUrl(): bool
    {
        return false;
    }

    /**
     * Define wether the single update route should include
     * the model's Code/Id or not...
     *
     * @return bool
     */
    public function hasCodeInUpdateUrl(): bool
    {
        return false;
    }
}
