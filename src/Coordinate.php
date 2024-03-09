<?php

namespace gaswelder\plot;

class Coordinate
{
    private $type;
    public $num;
    public $label;

    private function __construct($type, $num)
    {
        $this->type = $type;
        $this->num = $num;
        switch ($type) {
            case "date":
                $this->label = date("Y-m-d", $num);
                break;
            default:
                $this->label = "$num";
        }
    }

    static function parse($s)
    {
        if ($s instanceof Coordinate) {
            return $s;
        }
        if (is_numeric($s)) {
            return new Coordinate("num", $s);
        }
        $n = strtotime($s);
        if (!$n) {
            throw new Exception("failed to parse value: $s");
        }
        return new Coordinate("date", $n);
    }

    function add($step)
    {
        return new Coordinate($this->type, $this->num + $step);
    }

    function maxExponent()
    {
        return floor(log10($this->num));
    }

    function digitAt($exponent)
    {
        return floor($this->num / pow(10, $exponent)) % 10;
    }

    function step($exponent)
    {
        return pow(10, $exponent);
    }

    function left($exponent)
    {
        $e = $this->step($exponent);
        return new Coordinate($this->type, floor($this->num / $e) * $e);
    }
    function right($exponent)
    {
        $e = $this->step($exponent);
        return new Coordinate($this->type, ceil($this->num / $e) * $e);
    }

    function __toString()
    {
        return (string) $this->num;
    }
}
