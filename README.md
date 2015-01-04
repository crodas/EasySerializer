EasySerializer
==============

Serialize/deserialize and validate data structures. It uses annotations to declare data structures formats and their validations.

How does it work?
------------------

```php
$conf = new EasySerializer\Configuration(__DIR__/* directory where the classes are defined */);
$serializer = $conf->getSerializer();
```

We have our serializer object, it have *two* main methods, `serialize` and `deserialize`. The serializer  configuraiton object will walk through our directories the first time looking for classes with the `@Serialize` annotation. It will read their properties to understand their format and validations.

```php

/** @Serialize */
class Foobar
{
   /** @Required @String */
   public $name;
   
   /** @Int @Between([18,99], "Age is invalid") */
   public $age;
}
```

By default, it will read and write json objects but it's easier to change the serialize/deserialize functions at runtime (with the `setFunction($serialize, $deserialize)` method).

TODO
----

1. More unit testing
2. More documentation
3. More documentation for `crodas/validator` 
