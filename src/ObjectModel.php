<?php

namespace Whitecube\Winbooks;

use ReflectionClass;
use JsonSerializable;

abstract class ObjectModel implements JsonSerializable
{
    /**
     * Container for the model's data
     *
     * @var array
     */
    protected $data = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
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
        if(array_key_exists('Code', $this->data)) {
            return $this->Code;
        }

        if(array_key_exists('Id', $this->data)) {
            return $this->Id;
        }

        return null;
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
        return (new ReflectionClass($this))->getShortName() . 's';
    }

    /**
     * Set a value on the model
     *
     * @param string $name
     * @param $value
     */
    public function __set(string $name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * Get a value on the model
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->data[$name];
    }

    /**
     * Serialize the model
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return array_merge(['$type' => $this->getType()], $this->data);
    }
}
