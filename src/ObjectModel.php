<?php

namespace Whitecube\Winbooks;

use ArrayAccess;
use ReflectionClass;
use ReflectionMethod;
use JsonSerializable;
use InvalidArgumentException;
use Whitecube\Winbooks\Query\Relation;

abstract class ObjectModel implements ArrayAccess, JsonSerializable
{
    /**
     * Container for the model's data
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Create a new ObjectModel instance
     *
     * @param array $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    /**
     * Get the object model's $type string
     *
     * @return string
     */
    abstract public function getType(): string;

    /**
     * Get the Code, or Id, if they exist.
     *
     * @return string|void
     */
    public function getCode(): ?string
    {
        if($this->has('Code')) {
            return $this->get('Code');
        }

        if($this->has('Id')) {
            return $this->get('Id');
        }

        return null;
    }

    /**
     * Define wether the single add route should include
     * the model's Code/Id or not...
     *
     * @return bool
     */
    public function hasCodeInCreateUrl(): bool
    {
        return true;
    }

    /**
     * Define wether the single update route should include
     * the model's Code/Id or not...
     *
     * @return bool
     */
    public function hasCodeInUpdateUrl(): bool
    {
        return true;
    }

    /**
     * Get the object model's singular name
     *
     * @return string
     * @throws \ReflectionException
     */
    public function getOM(): string
    {
        return (new ReflectionClass($this))->getShortName();
    }

    /**
     * Get the object model's plural name
     *
     * @return string
     * @throws \ReflectionException
     */
    public function getOMS(): string
    {
        return $this->getOM() . 's';
    }

    /**
     * Create a relation definition
     *
     * @param string $classname
     * @return \Whitecube\Winbooks\Query\Relation
     */
    protected function relatesTo($classname): Relation
    {
        $method = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2)[1]['function'] ?? null;

        $target = new $classname();

        return new Relation($this, $target, Relation::extractRelationName($method));
    }

    /**
     * Get the relation name for given child model
     *
     * @return null|\Whitecube\Winbooks\Query\Relation
     */
    public function getRelationFor(ObjectModel $model): ?Relation
    {
        $methods = (new ReflectionClass($this))->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $method = $method->name;

            if(! Relation::isRelationMethod($method)) continue;

            $relation = $this->$method();

            if(! $relation->isTargeting($model)) continue;

            return $relation;
        }

        return null;
    }

    /**
     * Recursively merge new incoming data into this model's attributes
     *
     * @param mixed $value
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function merge($value)
    {
        if(is_a($value, static::class)) {
            $value = $value->getAttributes();
        }

        if(! is_array($value)) {
            $type = is_object($value) ? get_class($value) : gettype($value);
            throw new InvalidArgumentException('Cannot merge "' . $type . '" into ObjectModel "' . get_class() . '".');
        }

        $this->attributes = $this->mergeRecursiveDistinct($this->attributes, $value);

        return $this;
    }

    /**
     * Recursively merge new incoming data into this model's attributes
     *
     * @param array $base
     * @param array $array
     * @return array
     */
    protected function mergeRecursiveDistinct($base, $array)
    {
        if(! $this->isAssociativeArray($base) && ! $this->isAssociativeArray($array)) {
            return array_merge($base, $array);
        }

        foreach ($array as $key => $value) {
            if(! is_array($value) || ! is_array($base[$key] ?? null)) {
                $base[$key] = $value;
                continue;
            }

            $base[$key] = $this->mergeRecursiveDistinct($base[$key], $value);
        }

        return $base;
    }

    /**
     * Check if given array is associative (in opposition to sequential)
     *
     * @param array $base
     * @return bool
     */
    protected function isAssociativeArray(array $array): bool
    {
        $keys = array_keys($array);

        return array_keys($keys) !== $keys;
    }

    /**
     * Get the model's raw attributes array
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Check if a given attribute exists in the model's attributes
     *
     * @param string $attribute
     * @return bool
     */
    public function has($attribute): bool
    {
        return array_key_exists(ucfirst($attribute), $this->attributes);
    }

    /**
     * ArrayAccess' has alias
     *
     * @param string $attribute
     * @return bool
     */
    public function offsetExists($attribute)
    {
        return $this->has($attribute);
    }

    /**
     * Set a value on the model
     *
     * @param string $attribute
     * @param mixed $value
     * @return void
     */
    public function set(string $attribute, $value)
    {
        $this->attributes[ucfirst($attribute)] = $value;
    }

    /**
     * Magically set a value on the model
     *
     * @param string $attribute
     * @param mixed $value
     * @return void
     */
    public function __set(string $attribute, $value)
    {
        $this->set($attribute, $value);
    }

    /**
     * ArrayAccess' set alias
     *
     * @param string $attribute
     * @param mixed $value
     * @return void
     */
    public function offsetSet($attribute, $value)
    {
        $this->set($attribute, $value);
    }

    /**
     * Get a value on the model
     *
     * @param string $attribute
     * @return mixed
     */
    public function get(string $attribute)
    {
        $value = $this->attributes[ucfirst($attribute)] ?? null;

        if(is_a($value, self::class)) {
            return $value;
        }

        if(is_a($value = Winbooks::toModel($value), self::class)) {
            $this->set($attribute, $value);
        }

        return $value;
    }

    /**
     * Magically get a value on the model
     *
     * @param string $attribute
     * @return mixed
     */
    public function __get(string $attribute)
    {
        return $this->get($attribute);
    }

    /**
     * ArrayAccess' get alias
     *
     * @param string $attribute
     * @return mixed
     */
    public function offsetGet($attribute)
    {
        return $this->get($attribute);
    }

    /**
     * Unset a value on the model
     *
     * @param string $attribute
     * @return void
     */
    public function remove(string $attribute)
    {
        if(! $this->has($attribute)) {
            return;
        }

        unset($this->attributes[ucfirst($attribute)]);
    }

    /**
     * ArrayAccess' remove alias
     *
     * @param string $attribute
     * @return void
     */
    public function offsetUnset($attribute)
    {
        $this->remove($attribute);
    }

    /**
     * Serialize the model
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return array_merge(
            ['$type' => $this->getType()],
            $this->attributes
        );
    }
}
