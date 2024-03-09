<?php

namespace gaswelder\plot;

require_once __DIR__ . "/Coordinate.php";
require_once __DIR__ . "/primitives.php";

spl_autoload_register(function ($className) {
    $prefix = "gaswelder\\plot\\";
    if (strpos($className, $prefix) !== 0) return;
    $className = substr($className, strlen($prefix));
    $paths = [
        __DIR__ . "/$className.php",
        __DIR__ . "/Elements/$className.php"
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once($path);
            break;
        }
    }
});

class F
{
    static function XYPlot(...$objects)
    {
        return XYPlot::make(...$objects);
    }

    static function XYSeries($xy)
    {
        return XYSeries::make($xy);
    }

    static function Func($f, $x1, $x2)
    {
        return new Func($f, $x1, $x2);
    }

    static function Hist($data, $nbins = null)
    {
        return Histogram::make($data, $nbins);
    }

    static function dataline($a, $b, $x1, $x2)
    {
        return new DataLine($a, $b, $x1, $x2);
    }

    static function label($text, $xy)
    {
        return Label::make($text, $xy);
    }

    static function xline($x)
    {
        return new XLine($x);
    }
}
