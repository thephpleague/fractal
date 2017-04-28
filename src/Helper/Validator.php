<?php

namespace League\Fractal\Helper;

use InvalidArgumentException;

/**
 * Validator for function params.
 *
 * @package League\Fractal\Helper
 */
final class Validator
{
    const NOT_VALID_PARAM_TYPE = 'Param received "%s" is not valid. Should be %s.';

    /**
     * Evaluates that a parameter received on a function is type string.
     *
     * @param string $name The name of the parameter that is being evaluated.
     * @param mixed $value Value received on the function to be evaluated as string.
     *
     * @return void
     * @throws Not valid param received on function.
     */
    public static function validateParamString($name, $value)
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException(sprintf(static::NOT_VALID_PARAM_TYPE, $name, 'string'));
        }
    }

    /**
     * Evaluates that a parameter received on a function is type boolean.
     *
     * @param string $name The name of the parameter that is being evaluated.
     * @param mixed $value Value received on the function to be evaluated as boolean.
     *
     * @return void
     * @throws Not valid param received on function.
     */
    public static function validateParamBool($name, $value)
    {
        if (!is_bool($value)) {
            throw new InvalidArgumentException(sprintf(static::NOT_VALID_PARAM_TYPE, $name, 'boolean'));
        }
    }
}
