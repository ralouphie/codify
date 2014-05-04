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
 * A codify exception.
 */
class Exception extends \Exception
{
    const CLASS_NOT_IN_NAMESPACE = 1;

    /**
     * @inheritdoc
     */
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}