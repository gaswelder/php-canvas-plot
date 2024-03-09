<?php

namespace gaswelder;

require __DIR__ . '/base.php';
require __DIR__ . '/paths.php';
require __DIR__ . '/shapes.php';
require __DIR__ . '/transforms.php';
require __DIR__ . '/text.php';

class Exception extends \Exception
{
}

class Canvas
{
	use Base;
	use Transforms;
	use Paths;
	use Shapes;
	use Text;
}
