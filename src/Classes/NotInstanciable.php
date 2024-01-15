<?php
namespace Time2Split\Help\Classes;

trait NotInstanciable
{

    private final function __construct()
    {
        throw new \Error(__CLASS__ . " is not an instanciable class");
    }
}