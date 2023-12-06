<?php
class WatuPROIMultiUser {
	static function manage() {
		global $wpdb, $wp_roles;
		$roles = $wp_roles->roles;
		
		// this sets the setting of a selected role
		if(!empty($_POST['config_role'])) {
			$role_settings = unserialize(get_option('watupro_role_settings'));	
			
			// overwrite the settings for the selected role
			$role_settings[$_POST['role_key']] = array("exams_access" => $_POST['exams_access'], "certificates_access" => $_POST['certificates_access'],
				"cats_access" => $_POST['cats_access'], "usergroups_access" => $_POST['usergroups_access'], "qcats_access" => $_POST['qcats_access'],
				"settings_access" => $_POST['settings_access'], 'apply_usergroups'=>@$_POST['apply_usergroups']);
				
			update_option('watupro_role_settings', serialize($role_settings));	
		} // end config_role
		
		$role_settings = unserialize(get_option('watupro_role_settings'));
		
		// get the currently enabled roles
		$enabled_roles = array();
		foreach($roles as $key => $role) {
			$r=get_role($key);
			if(!empty($r->capabilities['watupro_manage_exams'])) $enabled_roles[] = $key;
		}
		
		if(@file_exists(get_stylesheet_directory().'/watupro/i/multiuser.html.php')) require get_stylesheet_directory().'/watupro/i/multiuser.html.php';
		else require WATUPRO_PATH."/i/views/multiuser.html.php";
	}
	
	// checks the access of the current user
	static function check_access($what, $noexit = false) {
		global $user_ID, $wp_roles;
		
		$role_settings = unserialize(get_option('watupro_role_settings'));
		$roles = $wp_roles->roles;
		// get all the currently enabled roles
		$enabled_roles = array();
		foreach($roles as $key => $role) {
			$r=get_role($key);
			if(!empty($r->capabilities['watupro_manage_exams'])) $enabled_roles[] = $key;
		}
				
		// admin can do everything
		if(current_user_can('administrator')) return 'all';		
		$user = new WP_User( $user_ID );
				
		$has_access = false;
		foreach($user->roles as $role) {
			if(!empty($role_settings[$role])) {				
				// empty is also true because we have to keep the defaults
				if(empty($role_settings[$role][$what]) or $role_settings[$role][$what] == 'all') {
					if(empty($role_settings[$role]['apply_usergroups']) or $what!='exams_access') return 'all';
					else $has_access = 'group'; // for exams we may want to apply user group restrictions
				}
				elseif($role_settings[$role][$what] == 'own' and $has_access != 'group') $has_access = 'own';	
				
				if(empty($has_access) and $what == 'exams_access' and $role_settings[$role][$what] == 'view') {
					$has_access = empty($role_settings[$role]['apply_usergroups']) ? 'view' : 'group_view'; 
				}
				// when none of the above, we just leave $has_access as false			
			}
			elseif(in_array($role, $enabled_roles)) $has_access = 'all'; // role was not specified in fine-tune so we just use the default full access
		}
		
		// if we are here, it means none of his roles had 'all'
		if($has_access) return $has_access;
		
		// when no access, die
		if($noexit) return false;
		else wp_die(__('You are not allowed to do this.', 'watupro'));
	}
}