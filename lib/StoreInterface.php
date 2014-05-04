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
 * An interface for a code store.
 * Code stores support the loading/saving/removing of classes under a namespace.
 *
 * @package Codify
 */
interface StoreInterface
{
    /** When the autoload was successful. */
    const AUTOLOAD_SUCCESS = 1;
    /** When the autoload could not proceed because class was not under the code store's namespace */
    const AUTOLOAD_CLASS_NOT_IN_NAMESPACE = 2;
    /** When the autoload failed because the class is not in the code store. */
    const AUTOLOAD_CLASS_NOT_FOUND = 3;

    /**
     * Load the class with the given name.
     *
     * @param string $class The fully-qualified class name to load.
     *
     * @return int The state of the autoload (see StoreInterface constants).
     */
    function autoload($class);

    /**
     * Save the class with the given name/code to the store.
     *
     * @param string $class The fully-qualified class name to store the code for.
     * @param string $code  The actual PHP code (without the "<?php").
     *
     * @return bool Whether the save was successful.
     */
    function save($class, $code);

    /**
     * Remove the class with the given name from the store.
     *
     * @param string $class The fully-qualified class name to remove.
     *
     * @return bool Whether the removal was successful.
     */
    function remove($class);

    /**
     * Check whether the given fully-qualified class name is in this store's base namespace.
     *
     * @param string $class The fully-qualified class name to check.
     *
     * @return bool Whether the class is in the base namespace.
     */
    function inBaseNamespace($class);

    /**
     * @return string This code store's base namespace.
     */
    function getBaseNamespace();
}