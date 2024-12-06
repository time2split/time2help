<?php

declare(strict_types=1);

namespace Time2Split\Help;

/**
 * A container which may contain a value.
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
     * Returns an Optional containing a specified value.
     * 
     * @template V
     * @param V $value The value to be stored.
     * @return Optional<V> An Optional containing `$value`.
     */
    public static function of($value): self
    {
        return (new Optional())->setValue($value);
    }

    /**
     * Gets an Optional of a specified value if non-null, otherwise returns an empty Optional.
     * 
     * @template V
     * @param V $value The possibly-null value to describe.
     * @param mixed $null The value to be considered as null.
     * @return Optional<V> An Optional containing `$value` if `$value !== $null`, otherwise {@see Optional::empty()}.
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
     * Returns an empty Optional singleton instance (ie. no value is stored).
     * 
     * The value is a singleton and may be compared with the `===` operator.
     * 
     * @return Optional<void> An empty Optional.
     */
    public static function empty(): self
    {
        return self::$empty ??= new self();
    }

    // ========================================================================

    /**
     * Whether a value is stored in this Optional.
     * 
     * @return bool true if there is a stored value, otherwise false.
     */
    public final function isPresent(): bool
    {
        return $this->isPresent;
    }

    /**
     * Whether this Optional stores no value.
     * 
     * @return bool true if there is no stored value, otherwise false.
     */
    public final function isEmpty(): bool
    {
        return !$this->isPresent;
    }

    /**
     * Retrieves the value of this Optional, or throws an error if no value is stored.
     * 
     * @return T The value of the optional.
     * @throws \Error
     */
    public final function get()
    {
        if ($this->isPresent())
            return $this->value;

        throw new \Error('An empty Optional cannot get a value');
    }

    /**
     * Returns the value if present, otherwise another specified one.
     * 
     * @param mixed $other The value to be returned if this Optional is empty.
     * It may be null.
     * 
     * @return mixed The value if present, otherwise `$other`.
     */
    public final function orElse($other)
    {
        if ($this->isPresent)
            return $this->value;

        return $other;
    }

    /**
     * Returns the value if present, otherwise the result of a closure.
     * 
     * @param \Closure $supplier
     * - `$supplier():mixed`
     * 
     * Compute a value to be returned if this Optional is empty.
     * 
     * @return T|mixed The value if present, otherwise the result of `$supplier()`.
     */
    public final function orElseGet(\Closure $supplier)
    {
        if ($this->isPresent)
            return $this->value;

        return $supplier();
    }
}
