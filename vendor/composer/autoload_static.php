<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitdb5c2c1ff1d8611b9e265d5b8df48099
{
    public static $prefixLengthsPsr4 = array (
        'F' => 
        array (
            'FapiMember\\Library\\SmartEmailing\\Types\\' => 39,
            'FapiMember\\Library\\Nette\\Utils\\' => 31,
            'FapiMember\\Library\\Nette\\' => 25,
            'FapiMember\\' => 11,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'FapiMember\\Library\\SmartEmailing\\Types\\' => 
        array (
            0 => __DIR__ . '/../..' . '/libs/smartemailing/types/src',
        ),
        'FapiMember\\Library\\Nette\\Utils\\' => 
        array (
            0 => __DIR__ . '/../..' . '/libs/nette/utils/src/Utils',
        ),
        'FapiMember\\Library\\Nette\\' => 
        array (
            0 => __DIR__ . '/../..' . '/libs/nette/utils/src',
        ),
        'FapiMember\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitdb5c2c1ff1d8611b9e265d5b8df48099::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitdb5c2c1ff1d8611b9e265d5b8df48099::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitdb5c2c1ff1d8611b9e265d5b8df48099::$classMap;

        }, null, ClassLoader::class);
    }
}
