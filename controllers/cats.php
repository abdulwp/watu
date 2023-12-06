<?php
// exam categories
function watupro_cats() {	
	global $wpdb, $wp_roles, $user_ID;	
	$groups_table=WATUPRO_GROUPS;
	
	$multiuser_access = 'all';
	if(watupro_intel()) $multiuser_access = WatuPROIMultiUser::check_access('cats_access');
	
	// are we using WP Roles or Watupro groups
	$use_wp_roles = get_option('watupro_use_wp_roles');
	
	// select all groups
	if(!$use_wp_roles) $groups=$wpdb->get_results("SELECT * FROM ".WATUPRO_GROUPS." ORDER BY name");
	else $roles = $wp_roles->roles;		
	
	switch(@$_GET['do']) {
		case 'add':
			if(!empty($_POST['ok'])) {
				$wpdb->query($wpdb->prepare("INSERT INTO ".WATUPRO_CATS." (name, ugroups, editor_id)
					VALUES (%s, %s, %d)", $_POST['name'], "|".@implode("|",$_POST['ugroups'])."|", $user_ID));
				echo "<meta http-equiv='refresh' content='0;url=admin.php?page=watupro_cats' />"; 
				exit;
			}
		
			if(@file_exists(get_stylesheet_directory().'/watupro/cat.php')) require get_stylesheet_directory().'/watupro/cat.php';
			else require WATUPRO_PATH."/views/cat.php";
		break;
	
		case 'edit':
			if($multiuser_access == 'own') {
				$cat=$wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_CATS." WHERE ID=%d", $_GET['id']));
				if($cat->editor_id != $user_ID) wp_die(__('You can manage only your own categories', 'watupro'));	
			}				
		
			if(!empty($_POST['del'])) {
				$wpdb->query($wpdb->prepare("DELETE FROM ".WATUPRO_CATS." WHERE ID=%d", $_GET['id']));
	         
	         // set cat_id=0 to all exams that were in this cat		
				$wpdb -> query( $wpdb->prepare("UPDATE ".WATUPRO_EXAMS." SET cat_id=0 WHERE cat_id=%d", $_GET['id']));
	
				echo "<meta http-equiv='refresh' content='0;url=admin.php?page=watupro_cats' />"; 
				exit;
			}
			
			if(!empty($_POST['ok'])) {
				$wpdb->query($wpdb->prepare("UPDATE ".WATUPRO_CATS." SET
					name=%s, ugroups=%s WHERE ID=%d", $_POST['name'], "|".@implode("|",$_POST['ugroups'])."|", $_GET['id']));
				echo "<meta http-equiv='refresh' content='0;url=admin.php?page=watupro_cats' />"; 
				exit;
			}
	
			$cat=$wpdb->get_row($wpdb->prepare("SELECT * FROM ".WATUPRO_CATS." WHERE ID=%d", $_GET['id']));
			
			if(@file_exists(get_stylesheet_directory().'/watupro/cat.php')) require get_stylesheet_directory().'/watupro/cat.php';
			else require WATUPRO_PATH."/views/cat.php";
		break;
	
		default:
			// select my cats
			$own_sql = ($multiuser_access == 'own') ? $wpdb->prepare(" WHERE editor_id = %d ", $user_ID) : "";
			$cats=$wpdb->get_results("SELECT * FROM ".WATUPRO_CATS." $own_sql ORDER BY name");
			
			// for each category prepare a string that shows which user groups or WP roles it's accessible to
			foreach($cats as $cnt => $cat) {			
				$allowed_str = '';	
				$cat_items = explode("|", $cat->ugroups);
				$cat_items = array_filter($cat_items);
				
				if(empty($cat_items)) $allowed_str = __('Everyone', 'watupro');
				
				if($use_wp_roles) {	
					foreach($cat_items as $ct => $role) {
						if(is_numeric($role)) continue; // avoid funny display if user has switched between roles and groups but has old groups saved
						if($ct > 1) $allowed_str .= ', ';
						$allowed_str .= $role;
					}  
				}
				else {
					// user groups. Go through groups and match to ID
					foreach($cat_items as $ct => $group_id) {
						if(!is_numeric($group_id)) continue; // avoid funny display if user has switched between roles and groups but has old roles saved
						if($ct > 1) $allowed_str .= ', ';
						foreach($groups as $group) {
							if($group_id == $group->ID) $allowed_str .= stripslashes($group->name);
						}
					}
				}
				
				if(empty($allowed_str)) $allowed_str = __('Everyone', 'watupro');
				
				$cats[$cnt]->allowed_to = $allowed_str;
			} // end foreach cat
			
			if(@file_exists(get_stylesheet_directory().'/watupro/cats.php')) require get_stylesheet_directory().'/watupro/cats.php';
			else require WATUPRO_PATH."/views/cats.php";
		break;
	}
}