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
 * An autoloader that will used the provided code store to load classes.
 */
class Autoloader
{
    /** @var \Codify\StoreInterface The code store to load code from. */
    protected $store;
    /** @var bool Whether the autoloader has been registered. */
    protected $registered = false;

    /**
     * Create a new codify autoloader.
     *
     * @param StoreInterface $store The store to load code from.
     */
    public function __construct(StoreInterface $store)
    {
        $this->store = $store;
    }

    /**
     * Register the autoloader.
     *
     * @param bool $throw   Whether to throw an exception if the autoloader could not be registered.
     * @param bool $prepend Whether to prepend this autoloader to the autoloader stack.
     *
     * @return bool true on success or false on failure.
     */
    public function register($throw = false, $prepend = false)
    {
        return !$this->registered && spl_autoload_register([$this, 'autoload'], $throw, $prepend);
    }

    /**
     * Unregister the autoloader.
     *
     * @return bool true on success or false on failure.
     */
    public function unregister()
    {
        return $this->registered && spl_autoload_unregister([$this, 'autoload']);
    }

    /**
     * Performs a load for the given class name.
     *
     * @param string $class The fully-qualified name of the class to load.
     *
     * @return bool Whether the class was loaded successfully.
     */
    public function autoload($class)
    {
        return $this->store->autoload($class) === StoreInterface::AUTOLOAD_SUCCESS;
    }
}