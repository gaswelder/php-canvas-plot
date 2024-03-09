<?php

namespace gaswelder\plot;

abstract class Element
{
    abstract function render();
}

class Label
{
    public $text;
    public $xy;

    static function make(string $text, $xy)
    {
        $l = new Label;
        $l->text = $text;
        $l->xy = [Coordinate::parse($xy[0]), Coordinate::parse($xy[1])];
        return $l;
    }
}

class Line
{
    public $xylist = [];

    function __construct($xylist)
    {
        foreach ($xylist as $xy) {
            $this->xylist[] = [Coordinate::parse($xy[0]), Coordinate::parse($xy[1])];
        }
    }
}

class Point
{
    public $xy;
    public $style;

    function __construct($xy, $style)
    {
        $this->xy = [Coordinate::parse($xy[0]), Coordinate::parse($xy[1])];
        $this->style = $style;
    }

    function filled()
    {
        $this->style['point-type'] = 'filled-circle';
        return $this;
    }
    function color($color)
    {
        $this->style['point-color'] = $color;
        return $this;
    }
}

class Rect
{
    public $p1;
    public $p2;
    public $style;

    function __construct($p1, $p2, $style)
    {
        $this->p1 = [Coordinate::parse($p1[0]), Coordinate::parse($p1[1])];
        $this->p2 = [Coordinate::parse($p2[0]), Coordinate::parse($p2[1])];
        $this->style = $style;
    }
}
