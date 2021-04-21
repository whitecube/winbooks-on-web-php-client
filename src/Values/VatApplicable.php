<?php

namespace Whitecube\Winbooks\Values;

class VatApplicable
{
    /**
     * The available values
     *
     * @var int
     */
    const UNDEFINED = 0;
    const SUBJECT_TO = 1;
    const EXEMPT_FROM = 2;
    const NOT_SUBJECT_TO = 3;
}
