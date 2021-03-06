<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit12e0ea73a65be76023f167d28ef696c4
{
    public static $prefixLengthsPsr4 = array (
        'Q' => 
        array (
            'QuickBooksOnline\\API\\' => 21,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'QuickBooksOnline\\API\\' => 
        array (
            0 => __DIR__ . '/..' . '/quickbooks/v3-php-sdk/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit12e0ea73a65be76023f167d28ef696c4::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit12e0ea73a65be76023f167d28ef696c4::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit12e0ea73a65be76023f167d28ef696c4::$classMap;

        }, null, ClassLoader::class);
    }
}
