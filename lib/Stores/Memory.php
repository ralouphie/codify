<?php

/**
 * This file is a part of Codify PHP.
 *
 * (c) 2014 Ralph khattar
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Codify\Stores;

use Codify\Store;

/**
 * Store code in memory. This should generally only be used for development or testing purposes.
 */
class Memory extends Store
{
    /** @var array The code to hold in memory. */
    protected $code = [];

    /**
     * @inheritdoc
     */
    protected function autoloadImplementation($class)
    {
        if (!isset($this->code[$class])) {
            return self::AUTOLOAD_CLASS_NOT_FOUND;
        }
        eval($this->code[$class]);
        return self::AUTOLOAD_SUCCESS;
    }

    /**
     * @inheritdoc
     */
    protected function saveImplementation($class, $code)
    {
        $this->code[$class] = $code;
        return true;
    }

    /**
     * @inheritdoc
     */
    protected function removeImplementation($class)
    {
        unset($this->code[$class]);
        return true;
    }
}