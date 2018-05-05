<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit858ea49cdfa3f1e7cdb79cc38c8594db
{
    public static $prefixLengthsPsr4 = array (
        'l' => 
        array (
            'libphonenumber\\' => 15,
        ),
        'G' => 
        array (
            'Giggsey\\Locale\\' => 15,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'libphonenumber\\' => 
        array (
            0 => __DIR__ . '/..' . '/giggsey/libphonenumber-for-php/src',
        ),
        'Giggsey\\Locale\\' => 
        array (
            0 => __DIR__ . '/..' . '/giggsey/locale/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit858ea49cdfa3f1e7cdb79cc38c8594db::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit858ea49cdfa3f1e7cdb79cc38c8594db::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
