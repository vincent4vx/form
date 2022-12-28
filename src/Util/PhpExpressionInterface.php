<?php

namespace Quatrevieux\Form\Util;

use Stringable;

/**
 * Base type for PHP expressions generator objects
 * This interface simply provides a `__toString()` method to get the generated PHP expression
 *
 * @see Code::raw() To create a new instance of this interface with a simple code string
 */
interface PhpExpressionInterface extends Stringable
{
}
