<?php
namespace Time2Split\Help\Classes;

/**
 * Specify that a class is not instanciable.
 * 
 * 
 * @author Olivier Rodriguez (zuri)
 */
trait NotInstanciable
{
    /**
     * Throws an error if the constructor is called.
     * 
     * @api
     * @throws \Error if the constructor is called.
     * @link https://www.php.net/manual/fr/class.error.php \Error
     */
    private final function __construct()
    {
        throw new \Error(__CLASS__ . " is not an instanciable class");
    }
}