<?php

/**
 * This file is a part of Codify PHP.
 *
 * (c) 2014 Ralph Khattar
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Codify\Stores;

use Codify\Store;

/**
 * Store code on the filesystem. This is the de facto code store.
 */
class Filesystem extends Store
{
    /** @var string The base directory for loading/saving code. */
    protected $base_directory;
    /** @var int Permissions for file/directory creation. */
    protected $create_mode;

    /**
     * Create a new filesystem code store.
     *
     * @param string $base_namespace The base PHP namespace for all code saved.
     * @param string $base_directory Where to load/save the code.
     * @param int    $create_mode    Permissions for file/directory creation.
     */
    public function __construct($base_namespace, $base_directory, $create_mode = 0777)
    {
        parent::__construct($base_namespace);
        $this->base_directory = rtrim($base_directory, DIRECTORY_SEPARATOR . '\\/ ') . DIRECTORY_SEPARATOR;
        $this->create_mode = $create_mode;
    }

    /**
     * @inheritdoc
     */
    protected function autoloadImplementation($class)
    {
        $path = $this->getFilePath($class);
        if (!file_exists($path)) {
            return self::AUTOLOAD_CLASS_NOT_FOUND;
        }
        include $path;
        return self::AUTOLOAD_SUCCESS;
    }

    /**
     * @inheritdoc
     */
    protected function saveImplementation($class, $code)
    {
        $path = $this->getFilePath($class);
        $last_slash = strrpos($path, DIRECTORY_SEPARATOR);
        if ($last_slash !== false) {
            $dir = substr($path, 0, $last_slash);
            if (mkdir($dir, $this->create_mode, true)) {
                return file_put_contents($path, '<?php' . PHP_EOL . $code, LOCK_EX) > 0;
            }
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    protected function removeImplementation($class)
    {
        $path = $this->getFilePath($class);
        if ($path && is_file($path)) {
            return unlink($path);
        }
        return false;
    }

    /**
     * @return string The base directory where code is loaded/saved.
     */
    public function getBaseDirectory()
    {
        return $this->base_directory;
    }

    /**
     * Get the file path for the given class name.
     *
     * @param string $class The fully qualified class name.
     *
     * @return string The filesystem path to the PHP file.
     */
    public function getFilePath($class)
    {
        return $this->base_directory . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    }
}