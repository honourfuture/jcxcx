<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit375961b676ffb51a9fe9939c07dafa14
{
    public static $prefixesPsr0 = array (
        'o' => 
        array (
            'org\\bovigo\\vfs' => 
            array (
                0 => __DIR__ . '/..' . '/mikey179/vfsStream/src/main/php',
            ),
        ),
        'D' => 
        array (
            'Detection' => 
            array (
                0 => __DIR__ . '/..' . '/mobiledetect/mobiledetectlib/namespaced',
            ),
        ),
    );

    public static $classMap = array (
        'Format' => __DIR__ . '/../..' . '/application/libraries/Format.php',
        'Mobile_Detect' => __DIR__ . '/..' . '/mobiledetect/mobiledetectlib/Mobile_Detect.php',
        'Restserver\\Libraries\\REST_Controller' => __DIR__ . '/../..' . '/application/libraries/REST_Controller.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr0 = ComposerStaticInit375961b676ffb51a9fe9939c07dafa14::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit375961b676ffb51a9fe9939c07dafa14::$classMap;

        }, null, ClassLoader::class);
    }
}
