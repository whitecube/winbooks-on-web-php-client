<?php

namespace Whitecube\Winbooks;

use JsonSerializable;
use Whitecube\Winbooks\Winbooks;
use Whitecube\Winbooks\ObjectModel;
use Whitecube\Winbooks\Query\Join;
use Whitecube\Winbooks\Query\Operator;
use Whitecube\Winbooks\Query\Property;

class Query implements JsonSerializable
{
    /**
     * The entity model instance for this query
     *
     * @var \Whitecube\Winbooks\ObjectModel
     */
    protected $model;

    /**
     * The entity's query Alias
     *
     * @var string
     */
    protected $alias;

    /**
     * The query projections (selects)
     *
     * @var array
     */
    protected $projections = [];

    /**
     * The query associations (joins)
     *
     * @var array
     */
    protected $associations = [];

    /**
     * The query conditions (wheres)
     *
     * @var array
     */
    protected $conditions = [];

    /**
     * The query ordering clauses (order by)
     *
     * @var array
     */
    protected $orders = [];

    /**
     * The amount of results per page
     *
     * @var null|int
     */
    protected $amount = null;

    /**
     * The amount of first skipped results
     *
     * @var null|int
     */
    protected $cursor = null;

    /**
     * Create a new query instance
     *
     * @param \Whitecube\Winbooks\ObjectModel $model
     * @param string $alias
     * @return void
     */
    public function __construct(ObjectModel $model, $alias = 'this')
    {
        $this->model = $model;
        $this->alias = $alias;
    }

    /**
     * Add one or more projections with the default SELECT operator
     *
     * @param array $properties
     * @return $this
     */
    public function select(...$properties)
    {
        return $this->selectOperator(Operator::select(), $properties);
    }

    /**
     * Add one or more projections with a custom operator
     *
     * @param int|string|\Whitecube\Winbooks\Query\Operator $operator
     * @param array $properties
     * @return $this
     */
    public function selectOperator($operator, ...$properties)
    {
        $properties = $this->flatten($properties);

        if(count($properties) === 1 && is_null($properties[0])) {
            $this->projections = [];

            return $this;
        }

        $operator = static::operator($operator);

        $properties = array_map(function($property) use ($operator) {
            return [
                'PropertyName' => static::property($property),
                'Operator' => $operator
            ];
        }, $properties);

        $this->projections = array_merge($this->projections, $properties);

        return $this;
    }

    /**
     * Add a single condition
     *
     * @param array $definition
     * @return $this
     * @throws InvalidArgumentException
     */
    public function where(...$definition)
    {
        if(count($definition) < 2) {
            throw new \InvalidArgumentException('Too few arguments provided to "where" condition. At least 2 expected, ' . count($definition) . ' given.');
        }

        if(count($definition) === 2) {
            $property = static::property($definition[0]);
            $value = $definition[1];
            $operator = Operator::eq();
        } else {
            $property = static::property($definition[0]);
            $value = $definition[2];
            $operator = static::operator($definition[1]);
        }

        if(is_a($value, Property::class)) {
            $otherProperty = $value;
            $value = [];
            $operator->forProperty();
        } else {
            $otherProperty = '';
            $value = is_array($value) ? $value : [$value];
            $operator->forValues();
        }

        $this->conditions[] = [
            'Operator' => $operator,
            'PropertyName' => $property,
            'OtherPropertyName' => $otherProperty,
            'Values' => $value,
        ];

        return $this;
    }

    /**
     * Add a single ordering clause
     *
     * @param null|string|\Whitecube\Winbooks\Query\Property $property
     * @param string $direction
     * @return $this
     */
    public function orderBy($property = null, string $direction = 'asc')
    {
        if(is_null($property)) {
            $this->orders = [];

            return $this;
        }

        $this->orders[] = [
            'PropertyName' => static::property($property),
            'Alias' => null,
            'Projections' => null,
            'Ascending' => ! (strtolower($direction) === 'desc'),
        ];

        return $this;
    }

    /**
     * Add a single association join
     *
     * @param string|\Whitecube\Winbooks\ObjectModel $model
     * @param null|callable $closure
     * @return $this
     */
    public function join($target, callable $closure = null)
    {
        $target = static::model($target);

        if(! ($join = $this->model->getRelationFor($target))) {
            $join = new Join($this->model, $target);
        }

        return $this->associate($join, $closure);
    }

    /**
     * Add a single association join based on a model relation
     *
     * @param string $relation
     * @param null|callable $closure
     * @return $this
     * @throws InvalidArgumentException
     */
    public function with(string $relation, callable $closure = null)
    {
        $method = 'get' . ucfirst($relation) . 'Relation';

        if(! method_exists($this->model, $method)) {
            throw new InvalidArgumentException('Relation "' . $relation . '" does not exist on model ' . get_class($this->model));
        }

        $relation = $this->model->$method();

        return $this->associate($relation, $closure);
    }

    /**
     * Add a single association
     *
     * @param \Whitecube\Winbooks\Query\Join $join
     * @param null|callable $closure
     * @return $this
     */
    public function associate(Join $join, callable $closure = null)
    {
        if($closure) {
            $closure($join);
        }

        $join->failIfNotUsable();

        $this->associations[$join->getAlias()] = $join;

        return $this;
    }

    /**
     * Configure the results pagination
     *
     * @param null|int $perPage
     * @param null|int $page
     * @return $this
     */
    public function paginate(int $perPage = null, int $page = null)
    {
        if(is_null($perPage)) {
            $this->amount = null;
            $this->cursor = null;

            return $this;
        }

        return $this->take($perPage)->skip((($page ?? 1) - 1) * $perPage);
    }

    /**
     * Set the max. results count
     *
     * @param null|int $amount
     * @return $this
     */
    public function take(int $amount = null)
    {
        // In Winbook's current API, "MaxResult" only works when
        // "FirstResult" is defined, that's why we'll set it to "0"
        // if it has not been set yet.

        if(is_null($this->cursor) && ! is_null($amount)) {
            $this->cursor = 0;
        } else if (is_null($amount) && $this->cursor === 0) {
            $this->cursor = null;
        }

        $this->amount = $amount;

        return $this;
    }

    /**
     * Set the index of the first result (first = 0)
     *
     * @param null|int $amount
     * @return $this
     */
    public function skip(int $amount = null)
    {
        $this->cursor = $amount;

        return $this;
    }

    /**
     * Create a valid operator instance
     *
     * @param int|string|\Whitecube\Winbooks\Query\Operator $value
     * @return \Whitecube\Winbooks\Query\Operator
     */
    public static function operator($value)
    {
        if(is_a($value, Operator::class)) {
            return $value;
        }

        return new Operator($value);
    }

    /**
     * Create a valid property instance
     *
     * @param string|\Whitecube\Winbooks\Query\Property $value
     * @return \Whitecube\Winbooks\Query\Property
     */
    public static function property($value)
    {
        if(is_a($value, Property::class)) {
            return $value;
        }

        return new Property($value);
    }

    /**
     * Create a valid model instance
     *
     * @param string|\Whitecube\Winbooks\ObjectModel $value
     * @return \Whitecube\Winbooks\ObjectModel
     */
    public static function model($value)
    {
        if(is_a($value, ObjectModel::class)) {
            return $value;
        }

        if(Winbooks::isModelType($value)) {
            return Winbooks::makeModelForType($value);
        }

        $type = null;

        foreach (['classname', 'om', 'oms'] as $attribute) {
            if(! ($definition = Winbooks::findModelType($attribute, $value))) continue;
            $type = $definition['type'];
            break;
        }

        return Winbooks::makeModelForType($type);
    }

    /**
     * Recursively flatten a multidimensional array and remove keys
     *
     * @param array $data
     * @param array $results
     * @return array
     */
    protected function flatten(array $data, array $result = [])
    {
        foreach ($data as $flat) {
            if (is_array($flat)) {
                $result = $this->flatten($flat, $result);
            } else {
                $result[] = $flat;
            }
        }

        return $result;
    }

    /**
     * Serialize the query as request body
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        $query = [
            'EntityType' => $this->model->getType(),
            'Alias' => $this->alias,
        ];

        if($this->associations) {
            $query['Association'] = $this->associations;
        }

        if($this->projections) {
            $query['ProjectionsList'] = $this->projections;
        }

        if($this->conditions) {
            $query['Conditions'] = $this->conditions;
        }

        if($this->orders) {
            $query['Orders'] = $this->orders;
        }

        if(! is_null($this->cursor)) {
            $query['FirstResult'] = $this->cursor;
        }

        if(! is_null($this->amount)) {
            $query['MaxResult'] = $this->amount;
        }

        return $query;
    }
}