<?php

class StoreTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Codify\Store */
    protected $store;

    protected $classes_available = [
        'CodifyTest\\BaseNamespace\\Foo',
        'CodifyTest\\BaseNamespace\\Bar'
    ];

    protected function setUp()
    {
        $stub = $this
            ->getMockBuilder('Codify\\Store')
            ->setConstructorArgs(['CodifyTest\\BaseNamespace'])
            ->getMockForAbstractClass();

        $stub
            ->expects($this->any())
            ->method('autoloadImplementation')
            ->will($this->returnCallback(function ($class) {
                return in_array($class, $this->classes_available)
                    ? \Codify\StoreInterface::AUTOLOAD_SUCCESS
                    : \Codify\StoreInterface::AUTOLOAD_CLASS_NOT_FOUND;
            }));

        $stub
            ->expects($this->any())
            ->method('saveImplementation')
            ->will($this->returnValue(true));

        $stub
            ->expects($this->any())
            ->method('removeImplementation')
            ->will($this->returnValue(true));

        $this->store = $stub;
    }

    function testGetBaseNamespace()
    {
        $this->assertEquals('CodifyTest\\BaseNamespace\\', $this->store->getBaseNamespace());
    }

    function testInBaseNamespace()
    {
        $this->assertTrue($this->store->inBaseNamespace('CodifyTest\\BaseNamespace\\SomeClass'));
        $this->assertTrue($this->store->inBaseNamespace('CodifyTest\\BaseNamespace\\AnotherClass'));
        $this->assertFalse($this->store->inBaseNamespace('\\CodifyTest\\BaseNamespace\\AnotherClass'));
        $this->assertFalse($this->store->inBaseNamespace('CodifyTest\\InvalidNamespace\\AnotherClass'));
    }

    function testAutoload()
    {
        $this->assertEquals(
            \Codify\StoreInterface::AUTOLOAD_CLASS_NOT_IN_NAMESPACE,
            $this->store->autoload('CodifyTest\\InvalidNamespace\\Foo')
        );

        $this->assertEquals(
            \Codify\StoreInterface::AUTOLOAD_CLASS_NOT_FOUND,
            $this->store->autoload('CodifyTest\\BaseNamespace\\SomeClass')
        );

        $this->assertEquals(
            \Codify\StoreInterface::AUTOLOAD_SUCCESS,
            $this->store->autoload('CodifyTest\\BaseNamespace\\Foo')
        );
    }

    function testSave()
    {
        $this->assertTrue($this->store->save('CodifyTest\\BaseNamespace\\Foo', 'x'));
    }

    function testRemove()
    {
        $this->assertTrue($this->store->remove('CodifyTest\\BaseNamespace\\Foo', 'x'));
    }

    /**
     * @expectedException \Codify\Exception
     * @expectedExceptionCode \Codify\Exception::CLASS_NOT_IN_NAMESPACE
     */
    function testSaveInvalidClass()
    {
        $this->store->save('CodifyTest\\InvalidNamespace\\Foo', 'x');
    }

    /**
     * @expectedException \Codify\Exception
     * @expectedExceptionCode \Codify\Exception::CLASS_NOT_IN_NAMESPACE
     */
    function testRemoveInvalidClass()
    {
        $this->store->remove('CodifyTest\\InvalidNamespace\\Foo', 'x');
    }
}