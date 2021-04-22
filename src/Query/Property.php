<?php

namespace Whitecube\Winbooks\Query;

use JsonSerializable;

class Property implements JsonSerializable
{
    /**
     * The property's initial value
     *
     * @var string
     */
    protected $value;

    /**
     * The property's origin table's prefix
     *
     * @var null|string
     */
    protected $from;

    /**
     * The property's key name
     *
     * @var string
     */
    protected $name;

    /**
     * Create a new property instance
     *
     * @param string $value
     * @return void
     */
    public function __construct($value)
    {
        $this->value = $value;

        [$from, $name] = $this->parse($value);

        $this->from = $from;
        $this->name = $name;
    }

    /**
     * Transform the given value into the operator code
     *
     * @param int|string $value
     * @return array
     */
    public function parse($value)
    {
        $parts = array_values(array_filter(array_map(function($part) {
            return trim($part);
        }, explode('.', $value))));

        if(! $parts) {
            throw new \InvalidArgumentException('Property cannot be empty or null.');
        }

        if(count($parts) === 1) {
            array_unshift($parts, null);
        }

        $parts[1] = ucfirst($parts[1]);

        return array_slice($parts, 0, 2);
    }

    /**
     * Return the defined property name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Serialize the property for the request body
     *
     * @return string
     */
    public function jsonSerialize(): string
    {
        return trim($this->from . '.' . $this->name, '.');
    }
}