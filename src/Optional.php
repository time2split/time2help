<?php
declare(strict_types=1);
namespace Time2Split\Help;

/**
 * An container which may contain a value.
 * 
 * The class is inspired by that of Java, but contrary to it it allows null values.
 *
 * @author Olivier Rodriguez (zuri)
 */
final class Optional
{

    private readonly mixed $value;

    private readonly bool $isPresent;

    /**
     * Returns an Optional with the specified present value.
     * 
     * @param mixed $value The value to be present.
     * @return Optional An Optional with the value present.
     */
    public static function of($value): self
    {
        $ret = new Optional();
        $ret->isPresent = true;
        $ret->value = $value;
        return $ret;
    }

    /**
     * Returns an Optional describing the specified value, if non-null, otherwise returns an empty Optional.

     * @param mixed $value The possibly-null value to describe.
     * @return Optional An Optional with a present value if the specified value is non-null, otherwise an empty Optional.

     */
    public static function ofNullable($value): self
    {
        if ($value === null)
            return self::empty();

        return self::of($value);
    }

    private static Optional $empty;

    /**
     * Returns an empty Optional singleton instance. No value is present for this Optional.
     * 
     * The value is a singleton and may be compared with the === operator.
     * 
     * @return Optional An empty Optional.
     */
    public static function empty(): self
    {
        if (!isset (self::$empty)) {
            $e = new self();
            $e->isPresent = false;
            self::$empty = $e;
        }
        return self::$empty;
    }

    // ========================================================================

    /**
     * Return true if there is a value present, otherwise false.
     */
    public final function isPresent()
    {
        return $this->isPresent;
    }

    /**
     * If a value is present in this Optional, returns the value, otherwise throws \Error.
     */
    public final function get()
    {
        return $this->value;
    }

    /**
     * Return the value if present, otherwise return $other.
     * 
     * @param mixed $other The value to be returned if there is no value present, may be null.
     * @return mixed The value, if present, otherwise $other.
     */
    public final function orElse($other)
    {
        if ($this->isPresent)
            return $this->value;

        return $other;
    }

    /**
     * Return the value if present, otherwise invoke other and return the result of that invocation.

     * @param \Closure $supplier A Supplier whose result is returned if no value is present.
     * @return mixed The value if present otherwise the result of $other().
     */
    public final function orElseGet(\Closure $supplier)
    {
        if ($this->isPresent)
            return $this->value;

        return $supplier();
    }
}