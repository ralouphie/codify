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
 * A codify exception.
 */
class Exception extends \Exception
{
    /**
     * @inheritdoc
     */
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}