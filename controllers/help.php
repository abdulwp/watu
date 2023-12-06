<?php
// user manual - added in version 3.1, to be developed
function watupro_help() {	
   global $wpdb;
		
	if($wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix. "watu_master"."'") == $wpdb->prefix. "watu_master") {	
			$watu_exams=$wpdb->get_results("SELECT * FROM ".$wpdb->prefix. "watu_master ORDER BY ID");
	}	   
   
	if(@file_exists(get_stylesheet_directory().'/watupro/help.php')) require get_stylesheet_directory().'/watupro/help.php';
	else require WATUPRO_PATH."/views/help.php";
}