<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitd9a631456e133bfb9b5f7845b0d14b40
{
    public static $prefixLengthsPsr4 = array (
        'y' => 
        array (
            'yidas\\socketMailer\\' => 19,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'yidas\\socketMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/yidas/socket-mailer/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitd9a631456e133bfb9b5f7845b0d14b40::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitd9a631456e133bfb9b5f7845b0d14b40::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitd9a631456e133bfb9b5f7845b0d14b40::$classMap;

        }, null, ClassLoader::class);
    }
}
