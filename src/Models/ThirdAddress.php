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

    /**
     * Get the belongsTo Address relationship
     *
     * @return \Whitecube\Winbooks\Query\Relation
     */
    public function getAddressRelation()
    {
        return $this->relatesTo(Address::class)->using('Id','Address_Id');
    }
}
