<?php

class FilesystemTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Codify\Stores\Filesystem */
    protected $store;
    protected $dir;
    protected $namespace_dir;
    protected $namespace;

    protected function setUp()
    {
        $this->dir = __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, '/../../codify-php-test-filesystem/');
        $this->namespace = 'CodifyTest\\BaseNamespaceFileSystem';
        $this->namespace_dir =
            $this->dir . str_replace('\\', DIRECTORY_SEPARATOR, $this->namespace);
        $this->removeDirRecursive($this->dir);
        $this->store = new \Codify\Stores\Filesystem($this->namespace, $this->dir);
    }

    protected function tearDown()
    {
        $this->removeDirRecursive($this->dir);
    }

    protected function removeDirRecursive($dir)
    {
        if (is_dir($dir)) {
            foreach (
                new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::CHILD_FIRST
                ) as $path
            ) {
                $path->isFile() ? unlink($path->getPathname()) : rmdir($path->getPathname());
            }
            rmdir($dir);
        }
    }

    function testGetBaseDirectory()
    {
        $this->assertEquals($this->dir, $this->store->getBaseDirectory());
    }

    function testGetFilePath()
    {
        $this->assertEquals(
            $this->namespace_dir . DIRECTORY_SEPARATOR . 'X.php',
            $this->store->getFilePath($this->namespace . '\\X')
        );
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
        $this->assertTrue(is_file(
            $this->dir . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $this->namespace . '\\Foo.php')
        ));
        $this->assertEquals(\Codify\StoreInterface::AUTOLOAD_SUCCESS, $this->store->autoload($class));
        $instance = new $class;
        $this->assertInstanceOf($class, $instance);
        $this->assertEquals('foo', $instance->action());
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    function testSaveFailed()
    {
        $path = $this->namespace_dir;
        mkdir($path, 0777, true);
        $path .= DIRECTORY_SEPARATOR . 'No';
        file_put_contents($path, 'No');
        $class = $this->namespace . '\\No\\A';
        $this->store->save($class, '<?php ');
    }

    function testAutoloadNotFound()
    {
        $this->assertEquals(
            \Codify\StoreInterface::AUTOLOAD_CLASS_NOT_FOUND,
            $this->store->autoload('CodifyTest\\BaseNamespaceFileSystem\\Bar')
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

    function testRemoveFailed()
    {
        $this->assertFalse($this->store->remove($this->namespace . '\\Nope'));
    }
}