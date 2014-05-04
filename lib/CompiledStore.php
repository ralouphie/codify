<?php

/**
 * This file is a part of Codify PHP.
 *
 * (c) 2014 Ralph Khattar
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Codify;

/**
 * A code store with dynamic compilation.
 *
 * This should be used in situations where code is generated dynamically based on class name.
 */
class CompiledStore implements StoreInterface
{
    /** @var \Codify\StoreInterface The base code store. */
    private $store;
    /** @var callable The code compiler to be called when a class is not found. */
    private $compiler;

    /**
     * Create a new compiled code store.
     *
     * @param StoreInterface $store    The base code store.
     * @param callable       $compiler The code compiled to be called when a class is not found.
     *   This callable only receives a single argument, which is the fully-qualified name of
     *   the class to compile.
     */
    public function __construct(StoreInterface $store, callable $compiler)
    {
        $this->store = $store;
        $this->compiler = $compiler;
    }

    /**
     * Compiles the class with the given name into the code store.
     *
     * @param string $class The fully-qualified name of the class to compile.
     *
     * @return bool Whether the class was compiled successfully.
     */
    public function compile($class)
    {
        $code = call_user_func($this->compiler, $class);
        if ($code && $this->store->save($class, $code)) {
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function autoload($class)
    {
        $result = $this->store->autoload($class);
        if ($result === StoreInterface::AUTOLOAD_CLASS_NOT_FOUND) {
            if ($this->compile($class)) {
                $result = $this->store->autoload($class);
            }
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function save($class, $code)
    {
        return $this->store->save($class, $code);
    }

    /**
     * @inheritdoc
     */
    public function remove($class)
    {
        return $this->store->remove($class);
    }

    /**
     * @inheritdoc
     */
    public function inBaseNamespace($class)
    {
        return $this->store->inBaseNamespace($class);
    }

    /**
     * @inheritdoc
     */
    public function getBaseNamespace()
    {
        return $this->store->getBaseNamespace();
    }
}