<?php

namespace Whitecube\Winbooks\Query;

use JsonSerializable;
use Whitecube\Winbooks\Exceptions\UndefinedOperatorException;

class Operator implements JsonSerializable
{
    /**
     * The available ExecuteCriteria operators
     *
     * @var int
     */
    const TYPE_EQ = 0;
    const TYPE_EQPROPERTY = 1;
    const TYPE_BETWEEN = 2;
    const TYPE_GE = 3;
    const TYPE_GEPROPERTY = 4;
    const TYPE_GT = 5;
    const TYPE_GTPROPERTY = 6;
    const TYPE_IN = 7;
    const TYPE_ISNOTNULL = 8;
    const TYPE_ISNOTEMPTY = 9;
    const TYPE_ISNULL = 10;
    const TYPE_ISEMPTY = 11;
    const TYPE_ISNOTNUMERIC = 12;
    const TYPE_ISNUMERIC = 13;
    const TYPE_LE = 14;
    const TYPE_LEPROPERTY = 15;
    const TYPE_LIKE = 16;
    const TYPE_LT = 17;
    const TYPE_LTPROPERTY = 18;
    const TYPE_OR = 19;
    const TYPE_AND = 20;
    const TYPE_NOT = 21;
    const TYPE_SELECT = 22;
    const TYPE_DISTINCT = 23;
    const TYPE_SELECTTOP = 24;
    const TYPE_AVG = 25;
    const TYPE_COUNT = 26;
    const TYPE_FIRST = 27;
    const TYPE_LAST = 28;
    const TYPE_MAX = 29;
    const TYPE_MIN = 30;
    const TYPE_SUM = 31;
    const TYPE_GROUPBY = 32;
    const TYPE_HAVING = 33;
    const TYPE_UCASE = 34;
    const TYPE_LCASE = 35;
    const TYPE_MID = 36;
    const TYPE_LEN = 37;
    const TYPE_ROUND = 38;
    const TYPE_NOW = 39;
    const TYPE_FORMAT = 40;
    const TYPE_CASEWHEN = 41;
    const TYPE_CAST = 42;
    const TYPE_CONSTANT = 43;
    const TYPE_FROMALIAS = 44;
    const TYPE_ROWNUMBER = 45;
    const TYPE_DATEADD = 46;
    const TYPE_DATEDIFF = 47;
    const TYPE_ALL = 48;
    const TYPE_ROWCOUNT = 49;
    const TYPE_EXISTS = 50;
    const TYPE_CONCAT = 51;
    const TYPE_LEFT = 52;
    const TYPE_RIGHT = 53;
    const TYPE_FUNCTION = 54;
    const TYPE_ABS = 55;
    const TYPE_SUBQUERY = 56;
    const TYPE_LTRIM = 57;
    const TYPE_RTRIM = 58;
    const TYPE_DATEPART = 59;
    const TYPE_UNION = 60;
    const TYPE_UNIONALL = 61;
    const TYPE_GROUPING = 62;
    const TYPE_RANK = 63;
    const TYPE_DENSERANK = 64;

    /**
     * The operator's provided value
     *
     * @var int|string
     */
    protected $value;

    /**
     * The operator's matching API code
     *
     * @var int
     */
    protected $code;

    /**
     * Get the symbolic operators mappings
     *
     * @return array
     */
    public static function getOperatorSymbols()
    {
        return [
            static::TYPE_EQ => ['=', '=='],
            static::TYPE_GE => ['>='],
            static::TYPE_GT => ['>'],
            static::TYPE_LE => ['<='],
            static::TYPE_LT => ['<'],
        ];
    }

    /**
     * Make a new operator instance from a static operator method call
     *
     * @param string $method
     * @param array $arguments
     * @return static
     */
    public static function __callStatic($method, $arguments)
    {
        return new static($method);
    }

    /**
     * Create a new operator instance
     *
     * @param int|string $value
     * @return void
     * @throws \Whitecube\Winbooks\Exceptions\UndefinedOperatorException
     */
    public function __construct($value)
    {
        $this->value = $value;
        $this->code = $this->parse($value);
    }

    /**
     * Transform the given value into the operator code
     *
     * @param int|string $value
     * @return void
     * @throws \Whitecube\Winbooks\Exceptions\UndefinedOperatorException
     */
    public function parse($value)
    {
        if(is_numeric($value)) {
            return intval($value);
        }

        if(! is_string($value)) {
            throw new UndefinedOperatorException('Operator cannot be of type "' . gettype($value) . '".');
        }

        $symbol = strtoupper(trim($value));

        foreach (static::getOperatorSymbols() as $operator => $symbols) {
            if(in_array($symbol, $symbols)) return $operator;
        }

        $constant = 'static::TYPE_' . str_replace(' ', '', $symbol);

        if(! defined($constant)) {
            throw new UndefinedOperatorException('Undefined query operator "' . $value . '".');
        }

        return constant($constant);
    }

    /**
     * Serialize the operator for the request body
     *
     * @return int
     */
    public function jsonSerialize(): int
    {
        return $this->code;
    }
}