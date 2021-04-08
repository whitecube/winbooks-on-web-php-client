<?php

namespace Whitecube\Winbooks\Query;

use JsonSerializable;
use Whitecube\Winbooks\Query;
use Whitecube\Winbooks\ObjectModel;
use Whitecube\Winbooks\Query\Operator;
use Whitecube\Winbooks\Query\Property;
use Whitecube\Winbooks\Exceptions\InvalidJoinException;

class Join implements JsonSerializable
{
    /**
     * The object model that owns the relation
     *
     * @var \Whitecube\Winbooks\ObjectModel
     */
    protected $from;

    /**
     * The object model targeted by the relation
     *
     * @var \Whitecube\Winbooks\ObjectModel
     */
    protected $to;

    /**
     * The owning object's alias for this relation
     *
     * @var null|string
     */
    protected $owner = null;

    /**
     * The joined table alias
     *
     * @var string
     */
    protected $alias;

    /**
     * The attribute (property) name on the "from" model
     *
     * @var null|\Whitecube\Winbooks\Query\Property
     */
    protected $left = null;

    /**
     * The joined table alias
     *
     * @var null|\Whitecube\Winbooks\Query\Operator
     */
    protected $operator = null;

    /**
     * The attribute (property) name on the "to" model
     *
     * @var null|\Whitecube\Winbooks\Query\Property
     */
    protected $right = null;

    /**
     * Create a new join instance
     *
     * @param \Whitecube\Winbooks\ObjectModel $from
     * @param \Whitecube\Winbooks\ObjectModel $to
     * @return void
     */
    public function __construct(ObjectModel $from, ObjectModel $to)
    {
        $this->from = $from;
        $this->to = $to;

        $this->alias(strtolower($to->getOM()));
    }

    /**
     * Check if this join targets the same model class
     *
     * @param \Whitecube\Winbooks\ObjectModel $model
     * @return bool
     */
    public function isTargeting(ObjectModel $model): bool
    {
        return get_class($this->to) === get_class($model);
    }

    /**
     * Define the owner association's table alias
     *
     * @param null|string $alias
     * @return $this
     */
    public function owner(string $alias = null)
    {
        $this->owner = $alias;

        return $this;
    }

    /**
     * Define the association's table alias
     *
     * @param string $alias
     * @return $this
     */
    public function alias(string $alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * Get the join association alias
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Alias of on()
     *
     * @param array $definition
     * @return $this
     */
    public function using(...$definition)
    {
        return $this->on(...$definition);
    }

    /**
     * Define the joining method
     *
     * @param array $definition
     * @return $this
     */
    public function on(...$definition)
    {
        if(count($definition) < 2) {
            throw new \InvalidArgumentException('Too few arguments provided to "join". At least 2 expected, ' . count($definition) . ' given.');
        }

        if(count($definition) === 2) {
            $this->left = Query::property($definition[0]);
            $this->right = Query::property($definition[1]);
            $this->operator = Operator::eqproperty();

            return $this;
        }

        $this->left = Query::property($definition[0]);
        $this->right = Query::property($definition[2]);
        $this->operator = Query::operator($definition[1])->forProperty();

        return $this;
    }

    /**
     * Check if the join can be used an throw exceptions if not
     *
     * @return void
     * @throws \Whitecube\Winbooks\Exceptions\InvalidJoinException
     */
    public function failIfNotUsable()
    {
        if(! $this->alias) {
            throw new InvalidJoinException('Missing alias for association');
        }

        if(! $this->left || ! $this->operator || ! $this->right) {
            throw new InvalidJoinException('Missing condition for association');
        }
    }

    /**
     * Serialize the join clause for the request body
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return array_filter([
            'OwnerAlias' => $this->owner,
            'AliasName' => $this->alias,
            'Type' => $this->to->getType(),
            'JoinType' => $this->operator,
            'LeftProperty' => $this->left,
            'RightProperty' => $this->right,
        ]);
    }
}