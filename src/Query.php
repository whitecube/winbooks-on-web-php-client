<?php

namespace Whitecube\Winbooks;

use JsonSerializable;
use Whitecube\Winbooks\ObjectModel;
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
     * The query conditions (wheres)
     *
     * @var array
     */
    protected $conditions = [];

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

        if($this->projections) {
            $query['ProjectionsList'] = $this->projections;
        }

        if($this->conditions) {
            $query['Conditions'] = $this->conditions;
        }

        return $query;
    }
}