<?php

namespace Whitecube\Winbooks;

use Whitecube\Winbooks\Exceptions\UndefinedOperatorException;

class Query
{
    /**
     * The available ExecuteCriteria operators
     *
     * @var int
     */
    const OPERATOR_EQ = 0;
    const OPERATOR_EQPROPERTY = 1;
    const OPERATOR_BETWEEN = 2;
    const OPERATOR_GE = 3;
    const OPERATOR_GEPROPERTY = 4;
    const OPERATOR_GT = 5;
    const OPERATOR_GTPROPERTY = 6;
    const OPERATOR_IN = 7;
    const OPERATOR_ISNOTNULL = 8;
    const OPERATOR_ISNOTEMPTY = 9;
    const OPERATOR_ISNULL = 10;
    const OPERATOR_ISEMPTY = 11;
    const OPERATOR_ISNOTNUMERIC = 12;
    const OPERATOR_ISNUMERIC = 13;
    const OPERATOR_LE = 14;
    const OPERATOR_LEPROPERTY = 15;
    const OPERATOR_LIKE = 16;
    const OPERATOR_LT = 17;
    const OPERATOR_LTPROPERTY = 18;
    const OPERATOR_OR = 19;
    const OPERATOR_AND = 20;
    const OPERATOR_NOT = 21;
    const OPERATOR_SELECT = 22;
    const OPERATOR_DISTINCT = 23;
    const OPERATOR_SELECTTOP = 24;
    const OPERATOR_AVG = 25;
    const OPERATOR_COUNT = 26;
    const OPERATOR_FIRST = 27;
    const OPERATOR_LAST = 28;
    const OPERATOR_MAX = 29;
    const OPERATOR_MIN = 30;
    const OPERATOR_SUM = 31;
    const OPERATOR_GROUPBY = 32;
    const OPERATOR_HAVING = 33;
    const OPERATOR_UCASE = 34;
    const OPERATOR_LCASE = 35;
    const OPERATOR_MID = 36;
    const OPERATOR_LEN = 37;
    const OPERATOR_ROUND = 38;
    const OPERATOR_NOW = 39;
    const OPERATOR_FORMAT = 40;
    const OPERATOR_CASEWHEN = 41;
    const OPERATOR_CAST = 42;
    const OPERATOR_CONSTANT = 43;
    const OPERATOR_FROMALIAS = 44;
    const OPERATOR_ROWNUMBER = 45;
    const OPERATOR_DATEADD = 46;
    const OPERATOR_DATEDIFF = 47;
    const OPERATOR_ALL = 48;
    const OPERATOR_ROWCOUNT = 49;
    const OPERATOR_EXISTS = 50;
    const OPERATOR_CONCAT = 51;
    const OPERATOR_LEFT = 52;
    const OPERATOR_RIGHT = 53;
    const OPERATOR_FUNCTION = 54;
    const OPERATOR_ABS = 55;
    const OPERATOR_SUBQUERY = 56;
    const OPERATOR_LTRIM = 57;
    const OPERATOR_RTRIM = 58;
    const OPERATOR_DATEPART = 59;
    const OPERATOR_UNION = 60;
    const OPERATOR_UNIONALL = 61;
    const OPERATOR_GROUPING = 62;
    const OPERATOR_RANK = 63;
    const OPERATOR_DENSERANK = 64;

    /**
     * Transform the given value into the operator code
     *
     * @param string $value
     * @return int
     * @throws \Whitecube\Winbooks\Exceptions\UndefinedOperatorException
     */
    public static function operator(string $value)
    {
        $symbol = strtoupper(trim($value));

        foreach (static::getOperatorSymbols() as $operator => $symbols) {
            if(in_array($symbol, $symbols)) return $operator;
        }

        $constant = 'static::OPERATOR_' . str_replace(' ', '', $symbol);

        if(! defined($constant)) {
            throw new UndefinedOperatorException('Undefined query operator "' . $value . '".');
        }

        return constant($constant);
    }

    /**
     * Get the symbolic operators mappings
     *
     * @return array
     */
    public static function getOperatorSymbols()
    {
        return [
            static::OPERATOR_EQ => ['=', '=='],
            static::OPERATOR_GE => ['>='],
            static::OPERATOR_GT => ['>'],
            static::OPERATOR_LE => ['<='],
            static::OPERATOR_LT => ['<'],
        ];
    }
}