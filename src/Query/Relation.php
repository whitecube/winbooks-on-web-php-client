<?php

namespace Whitecube\Winbooks\Query;

use Whitecube\Winbooks\ObjectModel;

class Relation extends Join
{
    /**
     * Create a new relation (join) instance
     *
     * @param \Whitecube\Winbooks\ObjectModel $from
     * @param \Whitecube\Winbooks\ObjectModel $to
     * @param null|string $alias
     * @return void
     */
    public function __construct(ObjectModel $from, ObjectModel $to, string $alias = null)
    {
        parent::__construct($from, $to);

        if($alias) {
            $this->alias($alias);
        }
    }

    /**
     * Check if given value is a valid relation method name
     *
     * @param null|string $method
     * @return bool
     */
    public static function isRelationMethod(string $method = null): bool
    {
        if(! $method || $method === 'getRelation') {
            return false;
        }
        
        if(strpos($method, 'get') !== 0) {
            return false;
        }
        
        if(strpos($method, 'Relation') !== strlen($method) - 8) {
            return false;
        }

        return true;
    }

    /**
     * Extract the relation name (alias) from method name
     *
     * @param null|string $method
     * @return null|string
     */
    public static function extractRelationName(string $method = null): ?string
    {
        if(! static::isRelationMethod($method)) {
            return null;
        }

        return lcfirst(substr($method, 3, -8));
    }
}