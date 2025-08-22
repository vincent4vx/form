<?php

namespace Quatrevieux\Form\Util;

use Closure;

/**
 * Factory methods for closures
 */
final class Functions
{
    /**
     * Create the save path resolver closure
     * Takes as parameter the generated class name, and returns save path
     *
     * @param string|null $basePath Base save path. If null, will use `sys_get_temp_dir()`
     *
     * @return Closure(string):string
     */
    public static function savePathResolver(?string $basePath = null): Closure
    {
        $basePath ??= sys_get_temp_dir();
        return fn(string $className) => $basePath . DIRECTORY_SEPARATOR . str_replace('\\', '_', $className) . '.php';
    }

    /**
     * Create the generated class name resolver closure
     * Takes as parameter the DTO class name and returns expected generated class name
     *
     * @param string $suffix Generated class name suffix
     * @return Closure(class-string):string
     */
    public static function classNameResolver(string $suffix): Closure
    {
        return fn(string $dataClassName) => str_replace('\\', '_', $dataClassName) . $suffix;
    }
}
