<?php

class CompiledStoreTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Codify\Stores\Memory */
    protected $store;
    /** @var \Codify\CompiledStore */
    protected $compiled_store;
    protected $namespace;

    protected function setUp()
    {
        $this->namespace = 'CodifyTest\\BaseNamespaceCompiled';
        $this->store = new \Codify\Stores\Memory($this->namespace);
        $this->compiled_store = new \Codify\CompiledStore($this->store, function ($class) {
            if ($class === $this->namespace . '\\A') {
                return
                    "namespace " . $this->namespace . ";\n" .
                    "class A { public function action() { return 'A'; } }";
            } elseif ($class === $this->namespace . '\\B') {
                return
                    "namespace " . $this->namespace . ";\n" .
                    "class B { public function action() { return 'B'; } }";
            } else {
                return false;
            }
        });
    }

    public function testDynamicCompile()
    {
        $class = $this->namespace . '\\A';
        $this->compiled_store->autoload($class);
        $this->assertTrue(class_exists($class));
        $instance = new $class;
        $this->assertInstanceOf($class, $instance);
        $this->assertEquals('A', $instance->action());
    }

    function testSave()
    {
        $class = $this->namespace . '\\Foo';
        $this->assertTrue($this->compiled_store->save(
            $class,
            "namespace " . $this->namespace . ";\n" .
            "class Foo { public function action() { return 'foo'; } }"
        ));
        $this->assertEquals(\Codify\StoreInterface::AUTOLOAD_SUCCESS, $this->compiled_store->autoload($class));
        $instance = new $class;
        $this->assertInstanceOf($class, $instance);
        $this->assertEquals('foo', $instance->action());
    }

    function testAutoloadNotFound()
    {
        $this->assertEquals(
            \Codify\StoreInterface::AUTOLOAD_CLASS_NOT_FOUND,
            $this->compiled_store->autoload($this->namespace . '\\Bar')
        );
    }

    function testRemove()
    {
        $class = $this->namespace . '\\Baz';
        $this->assertTrue($this->compiled_store->save(
            $class,
            "namespace " . $this->namespace . ";\n" .
            "class Baz { public function action() { return 'baz'; } }"
        ));
        $this->assertEquals(\Codify\StoreInterface::AUTOLOAD_SUCCESS, $this->compiled_store->autoload($class));
        $instance = new $class;
        $this->assertInstanceOf($class, $instance);
        $this->assertEquals('baz', $instance->action());
        $this->assertTrue($this->compiled_store->remove($class));
        $this->assertEquals(\Codify\StoreInterface::AUTOLOAD_CLASS_NOT_FOUND, $this->compiled_store->autoload($class));
    }

    function testGetBaseNamespace()
    {
        $this->assertEquals($this->namespace . '\\', $this->compiled_store->getBaseNamespace());
    }

    function testInBaseNamespace()
    {
        $this->assertTrue($this->compiled_store->inBaseNamespace($this->namespace . '\\SomeClass'));
        $this->assertTrue($this->compiled_store->inBaseNamespace($this->namespace . '\\AnotherClass'));
        $this->assertFalse($this->compiled_store->inBaseNamespace('\\' . $this->namespace . '\\AnotherClass'));
        $this->assertFalse($this->compiled_store->inBaseNamespace('CodifyTest\\InvalidNamespace\\AnotherClass'));
    }
}