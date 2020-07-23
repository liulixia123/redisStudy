<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit6b66e63297a33feb608598190b801fb6
{
    public static $prefixLengthsPsr4 = array (
        'L' => 
        array (
            'Lixia18\\Redis\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Lixia18\\Redis\\' => 
        array (
            0 => __DIR__ . '/../..' . '/',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit6b66e63297a33feb608598190b801fb6::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit6b66e63297a33feb608598190b801fb6::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
