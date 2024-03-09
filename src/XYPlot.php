<?php

namespace gaswelder\plot;

use \gaswelder\Canvas;

define('LABEL_FONT_SIZE', 10);

class XYPlot
{
	private $dataSources;
	private $settings = [];

	static function make(...$dataSources)
	{
		if (count($dataSources) == 0) {
			throw new Exception("empty list of data sources");
		}
		$plot = new XYPlot;
		$plot->dataSources = $dataSources;
		return $plot;
	}

	function title(string $title)
	{
		$this->settings['title'] = $title;
		return $this;
	}

	function grid()
	{
		$this->settings['grid'] = true;
		return $this;
	}

	function fullBorder()
	{
		$this->settings['full-border'] = true;
		return $this;
	}

	function ticLength($x)
	{
		$this->settings['tic-length'] = $x;
		return $this;
	}
	function subticLength($x)
	{
		$this->settings['subtic-length'] = $x;
		return $this;
	}
	function xtitle($x)
	{
		$this->settings['xtitle'] = $x;
		return $this;
	}
	function ytitle($x)
	{
		$this->settings['ytitle'] = $x;
		return $this;
	}

	function render()
	{
		return render($this->dataSources, $this->settings);
	}
}

class Bounds
{
	public $minX;
	public $maxX;
	public $minY;
	public $maxY;

	function take(Coordinate $x, Coordinate $y)
	{
		if ($this->minX === null || $x->num < $this->minX->num) {
			$this->minX = $x;
		}
		if ($this->maxX === null || $x->num > $this->maxX->num) {
			$this->maxX = $x;
		}
		if ($this->minY === null || $y->num < $this->minY->num) {
			$this->minY = $y;
		}
		if ($this->maxY === null || $y->num > $this->maxY->num) {
			$this->maxY = $y;
		}
	}
}

function render(array $dataSources, $settings0)
{
	$area = new CanvasArea;
	$settings = [
		'width' => 600,
		'height' => 340,
		'title' => "",
		'xtitle' => "",
		'ytitle' => "",
		'grid' => false,
		'full-border' => false,
		'tic-length' => 6,
		'subtic-length' => 3,
	];
	foreach ($settings0 as $k => $v) {
		$settings[$k] = $v;
	}

	$canvas = new Canvas($settings['width'], $settings['height']);
	$canvas->font = '10px ' . dirname(__FILE__) . '/latin.ttf';

	$objects = [];
	foreach ($dataSources as $object) {
		if ($object instanceof Label) {
			$objects[] = $object;
			continue;
		}
		$objects = array_merge($objects, $object->render());
	}

	// Calculate data range that we have.
	$bounds = new Bounds;
	foreach ($objects as $obj) {
		if ($obj instanceof Label) {
			[$x, $y] = $obj->xy;
			$bounds->take($x, $y);
			continue;
		}
		if ($obj instanceof Point) {
			[$x, $y] = $obj->xy;
			$bounds->take($x, $y);
			continue;
		}
		if ($obj instanceof Line) {
			foreach ($obj->xylist as $xy) {
				if (!is_finite($xy[1]->num)) continue;
				[$x, $y] = $xy;
				$bounds->take($x, $y);
			}
			continue;
		}
		if ($obj instanceof Rect) {
			[$x, $y] = $obj->p1;
			$bounds->take($x, $y);
			[$x, $y] = $obj->p2;
			$bounds->take($x, $y);
			continue;
		}
		$type = get_class($obj);
		throw new Exception("get bounds from $type");
	}

	$xtics = Ticker::makeTics($bounds->minX, $bounds->maxX);
	$ytics = Ticker::makeTics($bounds->minY, $bounds->maxY);

	$bounds->minX = $xtics[0];
	$bounds->maxX = array_reverse($xtics)[0];
	$bounds->minY = $ytics[0];
	$bounds->maxY = array_reverse($ytics)[0];

	$area->minDataY = $bounds->minY;
	$area->maxDataY = $bounds->maxY;
	$area->minDataX = $bounds->minX;
	$area->maxDataX = $bounds->maxX;

	if ($settings['title']) {
		$titleY = 20;
		$canvas->textAlign = 'center';
		$canvas->fillText($settings['title'], $settings['width'] / 2, $titleY);
		$area->claimSpace('top', $titleY);
	}

	if ($settings['xtitle']) {
		$x = $area->hspace($settings) / 2;
		$y = $settings['height'] - $area->margins['bottom'];
		$canvas->textBaseline = 'bottom';
		$canvas->textAlign = 'center';
		$canvas->fillText($settings['xtitle'], $x, $y);
		$area->claimSpace('bottom', LABEL_FONT_SIZE);
	}

	if ($settings['ytitle']) {
		$x = $area->margins['left'];
		$y = $area->vspace($settings) / 2;
		$canvas->rotate(-deg2rad(90));
		$canvas->textBaseline = 'top';
		$canvas->fillText($settings['ytitle'], -$y, $x);
		$canvas->rotate(deg2rad(90));
		$area->claimSpace('left', LABEL_FONT_SIZE);
	}

	$drawAxisLabels = true;
	if ($drawAxisLabels) {
		// Negative length here means that the tics will be drawn
		// to the other size of the axes. In that case we ignore
		// them by assuming zero.
		$outerTicLength = max(0, $settings['tic-length']);

		// Allocate space for labels.
		$yLabelsWidth = 0;
		foreach ($ytics as $y) {
			$m = $canvas->measureText($y->label);
			$yLabelsWidth = max($yLabelsWidth, $m->width);
		}
		$xLabelsHeight = LABEL_FONT_SIZE;
		$area->claimSpace('left', $yLabelsWidth + $outerTicLength);
		$area->claimSpace('bottom', $xLabelsHeight + $outerTicLength);

		// Draw the labels.
		$canvas->textBaseline = "middle";
		$canvas->textAlign = "right";
		foreach ($ytics as $y) {
			$canvas->fillText($y->label, $area->margins['left'] - $outerTicLength, $area->y($y, $settings));
		}
		$canvas->textBaseline = 'top';
		$canvas->textAlign = "center";
		foreach ($xtics as $x) {
			$y = $settings['height'] - $area->margins['bottom'] + $outerTicLength;
			$y += 2; // move the label slightly down do it doesn't touch the tic.
			$canvas->fillText($x->label, $area->x($x, $settings), $y);
		}
	}

	if ($settings['grid']) {
		$canvas->strokeStyle = '#999';
		$canvas->beginPath();
		foreach ($xtics as $x) {
			$canvas->moveTo($area->x($x, $settings), $area->ymin($settings));
			$canvas->lineTo($area->x($x, $settings), $area->ymax($settings));
		}
		foreach ($ytics as $y) {
			$canvas->moveTo($area->xmin($settings), $area->y($y, $settings));
			$canvas->lineTo($area->xmax($settings), $area->y($y, $settings));
		}
		$canvas->stroke();
	}

	foreach ($objects as $obj) {
		drawNativeShape($area, $canvas, $settings, $obj);
	}

	$drawAxes = true;
	if ($drawAxes) {
		$canvas->strokeStyle = 'black';
		$canvas->beginPath();
		$canvas->moveTo($area->xmin($settings), $area->ymax($settings));
		$canvas->lineTo($area->xmin($settings), $area->ymin($settings));
		$canvas->lineTo($area->xmax($settings), $area->ymin($settings));
		if ($settings['full-border']) {
			$canvas->lineTo($area->xmax($settings), $area->ymax($settings));
			$canvas->lineTo($area->xmin($settings), $area->ymax($settings));
		}
		$canvas->stroke();
	}

	$drawTics = true;
	if ($drawTics) {
		$ticLength = $settings['tic-length'];
		$subticLength = $settings['subtic-length'];

		$canvas->beginPath();
		foreach ($xtics as $datax) {
			$y = $area->ymin($settings);
			$canvas->moveTo($area->x($datax, $settings), $y);
			$canvas->lineTo($area->x($datax, $settings), $y + $ticLength);
		}
		foreach ($ytics as $datay) {
			$x1 = $area->xmin($settings);
			$canvas->moveTo($x1, $area->y($datay, $settings));
			$canvas->lineTo($x1 - $ticLength, $area->y($datay, $settings));
		}
		if ($subticLength) {
			$subtics = Ticker::subtics($xtics);
			foreach ($subtics as $datax) {
				$y = $area->ymin($settings);
				$canvas->moveTo($area->x($datax, $settings), $y);
				$canvas->lineTo($area->x($datax, $settings), $y + $subticLength);
			}
			$subtics = Ticker::subtics($ytics);
			foreach ($subtics as $datay) {
				$x1 = $area->xmin($settings);
				$canvas->moveTo($x1, $area->y($datay, $settings));
				$canvas->lineTo($x1 - $subticLength, $area->y($datay, $settings));
			}
		}
		$canvas->stroke();
	}

	return $canvas->data();
}

function drawNativeShape(CanvasArea $area, $canvas, $settings, $obj)
{
	if ($obj instanceof Label) {
		$canvas->fillText($obj->text, $area->x($obj->xy[0], $settings), $area->y($obj->xy[1], $settings));
		return;
	}
	if ($obj instanceof Point) {
		$xy = $obj->xy;
		$style = $obj->style;
		$x = $area->x($xy[0], $settings);
		$y = $area->y($xy[1], $settings);
		if ($style['point-type'] == 'filled-circle') {
			$canvas->fillStyle = $style['point-color'];
			$canvas->beginPath();
			$canvas->arc($x, $y, $style['point-size'] / 2, 0, 6.29);
			$canvas->fill();
		} else {
			$canvas->strokeStyle = $style['point-color'];
			$canvas->beginPath();
			$canvas->arc($x, $y, $style['point-size'] / 2, 0, 6.29);
			$canvas->stroke();
		}
		return;
	}
	if ($obj instanceof Line) {
		$points = $obj->xylist;
		$n = count($points);
		for ($i = 0; $i < $n; $i++) {
			if ($points[$i][1]->num == INF) $points[$i][1] = Coordinate::parse($area->maxDataY);
			if ($points[$i][1]->num == -INF) $points[$i][1] = Coordinate::parse($area->minDataY);
		}
		for ($i = 1; $i < $n; $i++) {
			$pA = $points[$i - 1];
			$pB = $points[$i];
			$canvas->moveTo($area->x($pA[0], $settings), $area->y($pA[1], $settings));
			$canvas->lineTo($area->x($pB[0], $settings), $area->y($pB[1], $settings));
			$canvas->stroke();
		}
		return;
	}
	if ($obj instanceof Rect) {
		$a = $obj->p1;
		$b = $obj->p2;
		$style = $obj->style;
		$x1 = $area->x($a[0], $settings);
		$y1 = $area->y($a[1], $settings);
		$x2 = $area->x($b[0], $settings);
		$y2 = $area->y($b[1], $settings);
		$canvas->strokeStyle = $style['stroke-color'];
		$canvas->beginPath();
		$canvas->rect($x1, $y1, ($x2 - $x1), ($y2 - $y1));
		$canvas->stroke();
		return;
	}
	$type = get_class($obj);
	throw new Exception("how to render $type?");
}


function debugLines(CanvasArea $area, $canvas, $settings)
{
	if (!$area->debug) return;
	echo json_encode($area->margins, JSON_PRETTY_PRINT), "\n";
	$x1 = $area->margins['left'];
	$x2 = $settings['width'] - $area->margins['right'];
	$y1 = $area->margins['top'];
	$y2 = $settings['height'] - $area->margins['bottom'];

	$canvas->strokeStyle = 'gray';
	$canvas->moveTo($x1, $y2);
	$canvas->lineTo($x1, $y1);
	$canvas->stroke();

	$canvas->moveTo($x1, $y2);
	$canvas->lineTo($x2, $y2);
	$canvas->stroke();
	$canvas->strokeStyle = 'black';
}
