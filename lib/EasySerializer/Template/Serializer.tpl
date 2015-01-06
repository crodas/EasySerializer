<?php

@set($ns, 'EasySerialize\\n' . uniqid(true));

namespace {{$ns}};

use {{$validator->getNamespace()}} as v;

function write_property($obj, $property, $value)
{
    $ref = new \ReflectionProperty($obj, $property);
    $ref->setAccesible(true);
    return $ref->setValue($obj, $value);
}

spl_autoload_register(function($class) {
    switch(strtolower($class)) {
    @foreach ($classes as $class => $obj) 
    case {{@$class}}:
        require {{@$obj->getFile()}};
        return true;
    @end
    }

    return false;
});

class Serializer
{
    protected $encoders = array(
        'encode' => 'json_encode',
        'decode' => 'json_decode',
    );

    public function setFunction($encode, $decode)
    {
        foreach (['encode', 'decode'] as $type) {
            if (!is_callable($$type)) {
                throw new \InvalidArgumentException("$type is not callable");
            }
        }
        $this->encoders = compact('encode', 'decode');
    }

    public function serialize($object, $raw = false)
    {
        $array = v\get_object_properties($object);

        switch (strtolower(get_class($object))) {
        @foreach ($classes as $class => $obj)
        case {{@$class}}:
            @foreach ($obj->GetProperties() as $prop)
                @if ($prop->has('Required'))
                if (empty($array[{{@$prop['property']}}])) {
                    throw new \EasySerializer\Exception\MissingProperty({{@$class}}, {{@$prop['property']}});
                }
                @end
                @if ($prop->has('Object'))
                    @if ($prop->has('Required'))
                    else {
                    @else
                    if (!empty($array[{{@$prop['property']}}])) {
                    @end
                        $array[{{@$prop['property']}}] = $this->serialize($array[{{@$prop['property']}}], true);
                    }
                @end
            @end
            break;
        @end
        }
        return  $raw ? $array : $this->encoders['encode']($array);
    }

    public function deserialize($str, $class)
    {
        $array = (array)(is_scalar($str) ? $this->encoders['decode']($str) : $str);
        if (is_object($class)) {
            $object = $class;
            $class  = get_class($object);
        } else {
            $object = new $class;
        }

        switch (strtolower($class)) {
        @foreach ($classes as $class => $obj)
        case {{@$class}}:
            @foreach ($obj->getProperties() as $prop)
                if (!empty($array[{{@$prop['property']}}])) {
                @if ($prop->has('Object'))
                    $array[{{@$prop['property']}}] = $this->deserialize($array[{{@$prop['property']}}], {{@current($prop->getOne('Object'))}});
                @end
                @set($val, strtolower($prop['class'] . "::" . $prop['property']))
                @if ($validator->hasRules($val))
                    if (v\validate_{{sha1($val)}}($array[{{@$prop['property']}}]) === false) {
                        throw new \EasySerializer\Exception\Validation({{@$class}}, {{@$prop['property']}});
                    }
                @end
                /**
                  {{@$prop}}
                */
                @if ($prop->hasAnnotation('Base64')) 
                    $array[{{@$prop['property']}}] = base64_decode($array[{{@$prop['property']}}]);
                @end
                @if (in_array('public', $prop->getMetadata()['visibility']))
                    $object->{{$prop['property']}} = $array[{{@$prop['property']}}];
                @else
                    write_property($object, {{@$prop['property']}}, $array[{{@$prop['property']}}]);
                @end
                @if ($prop->has('Required'))
                } else {
                    throw new \EasySerializer\Exception\MissingProperty({{@$class}}, {{@$prop['property']}});
                @end
                }
            @end
            break;
        @end
        default:
            throw new \InvalidArgumentException("Cannot find serializer for class " . get_class($obj));
        }

        return $object;
    }
}

{{substr($validator->getCode(), 5)}}

return new \{{$ns}}\Serializer;
