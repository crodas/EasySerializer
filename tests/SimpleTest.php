<?php

class SimpleTest extends \phpunit_framework_testcase
{
    public function testInit()
    {
        $this->assertTrue(is_object(getSerializer()));
    }

    /**
     *  @expectedException EasySerializer\Exception\MissingProperty
     */
    public function testInvalidJsonDeserializeMissing()
    {
        $array = ['zname' => uniqid(true)];
        $json  = json_encode($array);
        $serializer = getSerializer();
        $obj = $serializer->deserialize($json, 'Something');
        $this->assertEquals($obj->name, $array['name']);
    }


    /**
     *  @expectedException UnexpectedValueException
     */
    public function testInvalidJsonDeserialize()
    {
        $array = ['name' => uniqid(true), 'age' => rand(100, 109)];
        $json  = json_encode($array);
        $serializer = getSerializer();
        $obj = $serializer->deserialize($json, 'Something');
        $this->assertEquals($obj->name, $array['name']);
        $this->assertEquals($obj->age, $array['age']);
    }

    public function testJsonDeserialize()
    {
        $array = ['name' => uniqid(true), 'age' => rand(19, 98), 'obj' => ['name' => uniqid(True)]];
        $json  = json_encode($array);
        $serializer = getSerializer();
        $obj = $serializer->deserialize($json, 'Something');
        $this->assertEquals($obj->name, $array['name']);
        $this->assertEquals($obj->age, $array['age']);
        $this->assertTrue($obj->obj instanceof SomethingElse);
        $this->assertEquals($obj->obj->name, $array['obj']['name']);
    }
}
