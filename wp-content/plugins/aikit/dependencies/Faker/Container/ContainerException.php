<?php

declare(strict_types=1);

namespace AIKit\Dependencies\Faker\Container;

use AIKit\Dependencies\Psr\Container\ContainerExceptionInterface;

/**
 * @experimental This class is experimental and does not fall under our BC promise
 */
final class ContainerException extends \RuntimeException implements ContainerExceptionInterface
{
}
