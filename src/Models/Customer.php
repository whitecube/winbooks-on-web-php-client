<?php

namespace Whitecube\Winbooks\Models;

use Whitecube\Winbooks\ObjectModel;

class Customer extends ObjectModel
{
    /**
     * Get the object model's $type string
     *
     * @return string
     */
    public function getType(): string
    {
        return 'Winbooks.TORM.OM.Customer, Winbooks.TORM.OM';
    }

    /**
     * Get the belongsTo Third relationship
     *
     * @return \Whitecube\Winbooks\Query\Relation
     */
    public function getThirdRelation()
    {
        return $this->relatesTo(Third::class)->using('Third_Id','Id');
    }
}
