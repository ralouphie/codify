<?php

class AutoloaderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Codify\Stores\Memory */
    protected $store;
    protected $namespace;

    protected function setUp()
    {
        $this->namespace = 'CodifyTest\\BaseNamespaceAutoload';
        $this->store = new \Codify\Stores\Memory($this->namespace);
    }

    function testGetStore()
    {
        $autoloader = new \Codify\Autoloader($this->store);
        $this->assertEquals($this->store, $autoloader->getStore());
    }

    function testAutoloadWorks()
    {
        $autoloader = new \Codify\Autoloader($this->store);
        $class = $this->namespace . '\\Foo';
        $this->assertTrue($this->store->save(
            $class,
            "namespace " . $this->namespace . ";\n" .
            "class Foo { public function action() { return 'foo'; } }"
        ));
        $this->assertFalse(class_exists($class));
        $autoloader->register();
        $this->assertTrue(class_exists($class));
        $instance = new $class;
        $this->assertInstanceOf($class, $instance);
        $this->assertEquals('foo', $instance->action());
        $autoloader->unregister();
    }

    function testUnregisterWorks()
    {
        $autoloader = new \Codify\Autoloader($this->store);

        $autoloader->register();
        $class_1 = $this->namespace . '\\Bar';
        $this->assertTrue($this->store->save(
            $class_1,
            "namespace " . $this->namespace . ";\n" .
            "class Bar { public function action() { return 'bar'; } }"
        ));
        $this->assertTrue(class_exists($class_1));

        $autoloader->unregister();
        $class_2 = $this->namespace . '\\Baz';
        $this->assertTrue($this->store->save(
            $class_2,
            "namespace " . $this->namespace . ";\n" .
            "class Baz { public function action() { return 'baz'; } }"
        ));
        $this->assertFalse(class_exists($class_2));
    }

    function testCannotRegisterTwice()
    {
        $autoloader = new \Codify\Autoloader($this->store);
        $this->assertTrue($autoloader->register());
        $this->assertFalse($autoloader->register());
    }

    function testCannotUnregisterTwice()
    {
        $autoloader = new \Codify\Autoloader($this->store);
        $this->assertFalse($autoloader->unregister());
        $this->assertTrue($autoloader->register());
        $this->assertTrue($autoloader->unregister());
        $this->assertFalse($autoloader->unregister());
    }
}