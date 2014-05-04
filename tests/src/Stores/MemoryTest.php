<?php

class MemoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Codify\Stores\Memory */
    protected $store;
    protected $namespace;

    protected function setUp()
    {
        $this->namespace = 'CodifyTest\\BaseNamespaceMemory';
        $this->store = new \Codify\Stores\Memory($this->namespace);
    }

    function testSave()
    {
        $class = $this->namespace . '\\Foo';
        $this->assertTrue($this->store->save(
            $class,
            "namespace " . $this->namespace . ";\n" .
            "class Foo {\n" .
            "    public function action() { return 'foo'; }\n" .
            "}"
        ));
        $this->assertEquals(\Codify\StoreInterface::AUTOLOAD_SUCCESS, $this->store->autoload($class));
        $instance = new $class;
        $this->assertInstanceOf($class, $instance);
        $this->assertEquals('foo', $instance->action());
    }

    function testAutoloadNotFound()
    {
        $this->assertEquals(
            \Codify\StoreInterface::AUTOLOAD_CLASS_NOT_FOUND,
            $this->store->autoload($this->namespace . '\\Bar')
        );
    }

    function testRemove()
    {
        $class = $this->namespace . '\\Baz';
        $this->assertTrue($this->store->save(
            $class,
            "namespace " . $this->namespace . ";\n" .
            "class Baz {\n" .
            "    public function action() { return 'baz'; }\n" .
            "}"
        ));
        $this->assertEquals(\Codify\StoreInterface::AUTOLOAD_SUCCESS, $this->store->autoload($class));
        $instance = new $class;
        $this->assertInstanceOf($class, $instance);
        $this->assertEquals('baz', $instance->action());
        $this->assertTrue($this->store->remove($class));
        $this->assertEquals(\Codify\StoreInterface::AUTOLOAD_CLASS_NOT_FOUND, $this->store->autoload($class));
    }
}