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
        $this->dir =
            dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'codify-php-test-filesystem' . DIRECTORY_SEPARATOR;
        $this->chmodRecursive($this->dir, 0777);
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

    protected function chmodRecursive($path, $mode)
    {
        $path = rtrim($path, DIRECTORY_SEPARATOR);
        @chmod($path, $mode);
        if (is_dir($path)) {
            if ($handle = opendir($path)) {
                while (false !== ($entry = readdir($handle))) {
                    if ($entry != '.' && $entry != '..') {
                        $this->chmodRecursive($path . DIRECTORY_SEPARATOR . $entry, $mode);
                    }
                }
                closedir($handle);
            }
        }
    }

    protected function removeDirRecursive($dir)
    {
        $this->chmodRecursive($dir, 0777);
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
            "class Foo { public function action() { return 'foo'; } }"
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
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /Unable to create the directory/
     */
    function testSaveNoPermission()
    {
        $class = $this->namespace . '\\Foo';
        @mkdir($this->dir, 0000, true);
        @chmod($this->dir, 0000);
        $this->assertFalse($this->store->save(
          $class,
          "namespace " . $this->namespace . ";\n" .
          "class Foo { public function action() { return 'foo'; } }"
        ));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /Unable to write to directory/
     */
    function testSaveNotWritable()
    {
        $class = $this->namespace . '\\Foo';
        $dir = $this->dir . str_replace('\\', DIRECTORY_SEPARATOR, $this->namespace);
        @mkdir($dir, 0777, true);
        @chmod($dir, 0000);
        $this->assertFalse($this->store->save(
          $class,
          "namespace " . $this->namespace . ";\n" .
          "class Foo { public function action() { return 'foo'; } }"
        ));
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
            "class Baz { public function action() { return 'baz'; } }"
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