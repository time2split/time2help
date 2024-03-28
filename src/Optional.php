<?php

declare(strict_types=1);

namespace Time2Split\Help;

/**
 * An container which may contain a value.
 * 
 * The class is inspired by that of Java, but contrary to it it allows null values.
 *
 * @template T
 * @package time2help\container
 * @author Olivier Rodriguez (zuri)
 */
final class Optional
{

    /**
     * @var T
     */
    private mixed $value;

    private bool $isPresent;


    /**
     * @return Optional<void>
     */
    private function __construct()
    {
        $this->isPresent = false;
    }

    /**
     * @template V
     * @param V $value
     * @return Optional<V>
     */
    private function setValue($value): Optional
    {
        $this->isPresent = true;
        $this->value = $value;
        return $this;
    }

    /**
     * Returns an Optional with the specified present value.
     * 
     * @template V
     * @param V $value The value to be present.
     * @return Optional<V> An Optional with the value present.
     */
    public static function of($value): self
    {
        return (new Optional())->setValue($value);
    }

    /**
     * Returns an Optional describing the specified value, if non-null, otherwise returns an empty Optional.
     * 
     * @template V
     * @param V $value The possibly-null value to describe.
     * @param mixed $null The null value to consider.
     * @return Optional<V> An Optional with a present value if the specified value is non-null, otherwise an empty Optional.
     */
    public static function ofNullable($value, $null = null): self
    {
        if ($value === $null) {
            /**  @var Optional<V> */
            return self::empty();
        }
        /**  @var Optional<V> */
        return self::of($value);
    }

    /**
     * @var Optional<void>
     */
    private static Optional $empty;

    /**
     * Returns an empty Optional singleton instance. No value is present for this Optional.
     * 
     * The value is a singleton and may be compared with the === operator.
     * 
     * @return Optional<void> An empty Optional.
     */
    public static function empty(): self
    {
        return self::$empty ??= new self();
    }

    // ========================================================================

    /**
     * Return true if there is a value present, otherwise false.
     */
    public final function isPresent(): bool
    {
        return $this->isPresent;
    }

    /**
     * If a value is present in this Optional, returns the value, otherwise throws \Error.
     * @return T The value of the optional.
     */
    public final function get()
    {
        if ($this->isPresent())
            return $this->value;

        throw new \Error('An empty Optional cannot get a value');
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
     * 
     * @param \Closure $supplier A Supplier whose result is returned if no value is present.
     * @return T|mixed The value if present otherwise the result of $other().
     */
    public final function orElseGet(\Closure $supplier)
    {
        if ($this->isPresent)
            return $this->value;

        return $supplier();
    }
}
