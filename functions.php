<?php
	add_action('after_setup_theme', function(){
		function get_child_folder_contents($folder_path) {
			return scandir(get_stylesheet_directory() . '/' . $folder_path);
		}

		$folder_path = 'functions';
		$folder_contents = get_child_folder_contents($folder_path);

		foreach($folder_contents as $file) {
			$pathinfo = pathinfo($file);
			if(!empty($pathinfo['extension']) && $pathinfo['extension'] == 'php') {
				require_once($folder_path . '/' . $file);
			}
		}
	});
