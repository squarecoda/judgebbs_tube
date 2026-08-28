<?php

$folder_path = 'functions/post-types';
$folder_contents = get_child_folder_contents($folder_path);

foreach($folder_contents as $file) {
	$pathinfo = pathinfo($file);
	if(!empty($pathinfo['extension']) && $pathinfo['extension'] == 'php') {
		require_once sprintf('%s/%s/%s', get_stylesheet_directory(), $folder_path, $file);
	}
}
