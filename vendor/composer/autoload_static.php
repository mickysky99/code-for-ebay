<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitb1bf9af1b9d0554d3b23116e28c24c8d
{
    public static $files = array (
        'c964ee0ededf28c96ebd9db5099ef910' => __DIR__ . '/..' . '/guzzlehttp/promises/src/functions_include.php',
        'a0edc8309cc5e1d60e3047b5df6b7052' => __DIR__ . '/..' . '/guzzlehttp/psr7/src/functions_include.php',
        '37a3dc5111fe8f707ab4c132ef1dbc62' => __DIR__ . '/..' . '/guzzlehttp/guzzle/src/functions_include.php',
        'cb925fa695ce0d7d860d2950bdec995f' => __DIR__ . '/..' . '/dts/ebay-sdk-php/src/functions.php',
    );

    public static $prefixLengthsPsr4 = array (
        'd' => 
        array (
            'duzun\\' => 6,
        ),
        'S' => 
        array (
            'Spatie\\ArrayToXml\\' => 18,
        ),
        'P' => 
        array (
            'Psr\\Http\\Message\\' => 17,
        ),
        'M' => 
        array (
            'MCS\\' => 4,
        ),
        'L' => 
        array (
            'League\\Csv\\' => 11,
        ),
        'G' => 
        array (
            'GuzzleHttp\\Psr7\\' => 16,
            'GuzzleHttp\\Promise\\' => 19,
            'GuzzleHttp\\' => 11,
        ),
        'D' => 
        array (
            'DTS\\eBaySDK\\' => 12,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'duzun\\' => 
        array (
            0 => __DIR__ . '/..' . '/duzun/hquery/psr-4',
        ),
        'Spatie\\ArrayToXml\\' => 
        array (
            0 => __DIR__ . '/..' . '/spatie/array-to-xml/src',
        ),
        'Psr\\Http\\Message\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/http-message/src',
        ),
        'MCS\\' => 
        array (
            0 => __DIR__ . '/..' . '/mcs/amazon-mws/src',
        ),
        'League\\Csv\\' => 
        array (
            0 => __DIR__ . '/..' . '/league/csv/src',
        ),
        'GuzzleHttp\\Psr7\\' => 
        array (
            0 => __DIR__ . '/..' . '/guzzlehttp/psr7/src',
        ),
        'GuzzleHttp\\Promise\\' => 
        array (
            0 => __DIR__ . '/..' . '/guzzlehttp/promises/src',
        ),
        'GuzzleHttp\\' => 
        array (
            0 => __DIR__ . '/..' . '/guzzlehttp/guzzle/src',
        ),
        'DTS\\eBaySDK\\' => 
        array (
            0 => __DIR__ . '/..' . '/dts/ebay-sdk-php/src',
        ),
    );

    public static $classMap = array (
        'hQuery' => __DIR__ . '/..' . '/duzun/hquery/hquery.php',
        'hQuery_Context' => __DIR__ . '/..' . '/duzun/hquery/hquery.php',
        'hQuery_Element' => __DIR__ . '/..' . '/duzun/hquery/hquery.php',
        'hQuery_HTML_Parser' => __DIR__ . '/..' . '/duzun/hquery/hquery.php',
        'hQuery_Node' => __DIR__ . '/..' . '/duzun/hquery/hquery.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitb1bf9af1b9d0554d3b23116e28c24c8d::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitb1bf9af1b9d0554d3b23116e28c24c8d::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitb1bf9af1b9d0554d3b23116e28c24c8d::$classMap;

        }, null, ClassLoader::class);
    }
}
