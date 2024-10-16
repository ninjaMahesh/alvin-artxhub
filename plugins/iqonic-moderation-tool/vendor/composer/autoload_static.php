<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit11fee663277b3f919f25c3ff9a4eb803
{
    public static $files = array (
        '2e08a76dd76372b9c8641c23901a6934' => __DIR__ . '/../..' . '/includes/Utils/template_loader.php',
        '962a2e75b23e9b8eb3c26aa8861ee89f' => __DIR__ . '/../..' . '/includes/Utils/block_helpers.php',
        '612d4d8cfbda25729738007fe34d0929' => __DIR__ . '/../..' . '/includes/Utils/report_helpers.php',
        'c3c53b0e37b0a2b8e7494ef4c63cb111' => __DIR__ . '/../..' . '/includes/Utils/moderation_helpers.php',
    );

    public static $prefixLengthsPsr4 = array (
        'I' => 
        array (
            'IMT\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'IMT\\' => 
        array (
            0 => __DIR__ . '/../..' . '/includes',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit11fee663277b3f919f25c3ff9a4eb803::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit11fee663277b3f919f25c3ff9a4eb803::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
