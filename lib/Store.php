<?php

/**
 * This file is a part of Codify PHP.
 *
 * (c) 2014 Ralph khattar
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Codify;

/**
 * A base class for code stores that implements base namespace logic.
 */
abstract class Store implements StoreInterface
{
    /** @var string The base namespace for this code store. */
    protected $base_namespace;
    /** @var int The string length of the base namespace. */
    protected $base_namespace_length;

    /**
     * Create a code store for the given base namespace.
     *
     * @param string $base_namespace The base namespace for this code store.
     */
    public function __construct($base_namespace)
    {
        $this->base_namespace = trim($base_namespace, '\\ ') . '\\';
        $this->base_namespace_length = strlen($this->base_namespace);
    }

    /**
     * @inheritdoc
     */
    public final function autoload($class)
    {
        if (!$this->inBaseNamespace($class)) {
            return self::AUTOLOAD_CLASS_NOT_IN_NAMESPACE;
        }
        return $this->autoloadImplementation($class);
    }

    /**
     * @inheritdoc
     */
    public final function save($class, $code)
    {
        if (!$this->inBaseNamespace($class)) {
            throw new Exception(
                'Cannot save class ' . $class . '. Not in namespace ' . $this->getBaseNamespace()
            );
        }
        return $this->saveImplementation($class, $code);
    }

    /**
     * @inheritdoc
     */
    public final function remove($class)
    {
        if (!$this->inBaseNamespace($class)) {
            throw new Exception(
                'Cannot remove class ' . $class . '. Not in namespace ' . $this->getBaseNamespace()
            );
        }
        return $this->removeImplementation($class);
    }

    /**
     * @inheritdoc
     */
    public final function inBaseNamespace($class)
    {
        return substr($class, 0, $this->base_namespace_length) === $this->base_namespace;
    }

    /**
     * @inheritdoc
     */
    public final function getBaseNamespace()
    {
        return $this->base_namespace;
    }

    /**
     * The actual autoload implementation.
     * This should try to load the class with the given name.
     *
     * @param string $class The fully qualified class name.
     *
     * @return int The state of the autoload.
     *
     * @see StoreInterface
     */
    abstract protected function autoloadImplementation($class);

    /**
     * The actual class save implementation.
     * This should try to save the class with the given name and code to the store.
     *
     * @param string $class The fully-qualified class name to save the code under.
     * @param string $code  The actual PHP code (without the "<?php" part).
     *
     * @return bool Whether the save was successful.
     */
    abstract protected function saveImplementation($class, $code);

    /**
     * The actual class remove implementation.
     * This should try to remove the class with the given name from the store.
     *
     * @param string $class The fully-qualified class name to remove.
     *
     * @return bool Whether the removal was successful.
     */
    abstract protected function removeImplementation($class);
}