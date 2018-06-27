<?php

namespace Nahid\JsonQ;

class Condition
{
    /**
     * Simple equals
     *
     * @param mixed $value
     * @param mixed $comparable
     *
     * @return bool
     */
    public static function equal($value, $comparable)
    {
        return $value == $comparable;
    }

    /**
     * Strict equals
     *
     * @param mixed $value
     * @param mixed $comparable
     *
     * @return bool
     */
    public static function strictEqual($value, $comparable)
    {
        return $value === $comparable;
    }

    /**
     * Simple not equal
     *
     * @param mixed $value
     * @param mixed $comparable
     *
     * @return bool
     */
    public static function notEqual($value, $comparable)
    {
        return $value != $comparable;
    }

    /**
     * Strict not equal
     *
     * @param mixed $value
     * @param mixed $comparable
     *
     * @return bool
     */
    public static function strictNotEqual($value, $comparable)
    {
        return $value !== $comparable;
    }

    /**
     * Strict greater than
     *
     * @param mixed $value
     * @param mixed $comparable
     *
     * @return bool
     */
    public static function greaterThan($value, $comparable)
    {
        return $value > $comparable;
    }

    /**
     * Strict less than
     *
     * @param mixed $value
     * @param mixed $comparable
     * 
     * @return bool
     */
    public static function lessThan($value, $comparable)
    {
        return $value < $comparable;
    }

    /**
     * Greater or equal
     *
     * @param mixed $value
     * @param mixed $comparable
     *
     * @return bool
     */
    public static function greaterThanOrEqual($value, $comparable)
    {
        return $value >= $comparable;
    }

    /**
     * Less or equal
     *
     * @param mixed $value
     * @param mixed $comparable
     *
     * @return bool
     */
    public static function lessThanOrEqual($value, $comparable)
    {
        return $value <= $comparable;
    }

    /**
     * In array
     *
     * @param mixed $value
     * @param array $comparable
     *
     * @return bool
     */
    public static function in($value, $comparable)
    {
        return (is_array($comparable) && in_array($value, $comparable));
    }

    /**
     * Not in array
     *
     * @param mixed $value
     * @param array $comparable
     *
     * @return bool
     */
    public static function notIn($value, $comparable)
    {
        return (is_array($comparable) && !in_array($value, $comparable));
    }

    /**
     * Is null equal
     *
     * @param mixed $value
     *
     * @return bool
     */
    public static function isNull($value, $comparable)
    {
        return is_null($value);
    }

    /**
     * Is not null equal
     *
     * @param mixed $value
     *
     * @return bool
     */
    public static function isNotNull($value, $comparable)
    {
        return !is_null($value);
    }

    /**
     * Start With
     *
     * @param mixed $value
     * @param string $comparable
     *
     * @return bool
     */
    public static function startWith($value, $comparable)
    {
        if (is_array($comparable) || is_array($value) || is_object($comparable) || is_object($value)) {
            return false;
        }
        
        if (preg_match("/^$comparable/", $value)) {
            return true;
        }

        return false;
    }

    /**
     * End with
     *
     * @param mixed $value
     * @param string $comparable
     *
     * @return bool
     */
    public static function endWith($value, $comparable)
    {
        if (is_array($comparable) || is_array($value) || is_object($comparable) || is_object($value)) {
            return false;
        }
        
        if (preg_match("/$comparable$/", $value)) {
            return true;
        }

        return false;
    }

    /**
     * Match with pattern
     *
     * @param mixed $value
     * @param string $comparable
     *
     * @return bool
     */
    public static function match($value, $comparable)
    {
        if (is_array($comparable) || is_array($value) || is_object($comparable) || is_object($value)) {
            return false;
        }
        
        $comparable = trim($comparable);

        if (preg_match("/^$comparable$/", $value)) {
            return true;
        }

        return false;
    }

    /**
     * Contains substring in string
     *
     * @param string $value
     * @param string $comparable
     *
     * @return bool
     */
    public static function contains($value, $comparable)
    {
        return (strpos($value, $comparable) !== false);
    }

    /**
     * Dates equal
     *
     * @param string $value
     * @param string $comparable
     *
     * @return bool
     */
    public static function dateEqual($value, $comparable, $format = 'Y-m-d')
    {
        $date = date($format, strtotime($value));
        return $date == $comparable;
    }

    /**
     * Months equal
     *
     * @param string $value
     * @param string $comparable
     *
     * @return bool
     */
    public static function monthEqual($value, $comparable)
    {
        $month = date('m', strtotime($value));
        return $month == $comparable;
    }

    /**
     * Years equal
     *
     * @param string $value
     * @param string $comparable
     *
     * @return bool
     */
    public static function yearEqual($value, $comparable)
    {
        $year = date('Y', strtotime($value));
        return $year == $comparable;
    }
}
