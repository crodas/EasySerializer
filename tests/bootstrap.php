<?php

require __DIR__ . "/../vendor/autoload.php";

function getSerializer()
{
    $conf = new EasySerializer\Configuration(__DIR__);
    return $conf->getSerializer();
}
