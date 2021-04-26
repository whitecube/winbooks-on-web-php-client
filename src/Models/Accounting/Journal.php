<?php

namespace Whitecube\Winbooks\Models\Accounting;

use Whitecube\Winbooks\ObjectModel;

class Journal extends ObjectModel
{

    public function getType(): string
    {
        return 'Winbooks.TORM.OM.Accounting.Journal, Winbooks.TORM.OM';
    }

    /**
     * Get the belongsTo Third relationship
     *
     * @return \Whitecube\Winbooks\Query\Relation
     */
    public function getBookYearRelation()
    {
        return $this->relatesTo(BookYear::class)->using('Id','BookYear_Id');
    }
}
