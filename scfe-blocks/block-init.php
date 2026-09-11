<?php

namespace SquareCoda\Theme\Custom_Block;

use SquareCoda\Field_Editor\Custom_Block;

class Tube_Category extends Custom_Block {
	public $init_file = __FILE__;
	public $category = 'judgebbs-tube';
	public $fields = [];
}


class Tube_Dashboard extends Tube_Category {
	public $class_name = __CLASS__;
	public $fields = [];
}
new Tube_Dashboard;

class Judge_Layout extends Tube_Category {
	public $class_name = __CLASS__;
	public $fields = [];
}
new Judge_Layout;

class Performance_Layout extends Tube_Category {
	public $class_name = __CLASS__;
	public $fields = [];
}
new Performance_Layout;
