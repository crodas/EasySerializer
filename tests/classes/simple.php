<?php

/**
 *  @Serialize
 */
class SomethingElse
{
    /** @String */
    public $name;
}

/**
 *  @Serialize
 */
class Something
{
    /** @Required @String */
    public $name;

    /** @Int @Between([18,99], "You must be between 18 and 99 years") */
    public $age;

    /** @Object("SomethingElse") */
    public $obj;

    /** @Base64 */
    public $binary;
}
