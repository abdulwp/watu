<div class="wrap">
	<h1><?php _e('Manage multi-user configurations in Watu PRO', 'watupro')?></h1>
	
	<?php if(empty($enabled_roles)):?>
		<p><?php printf(__('To edit this page you need to enable some roles to manage exams on the <a href="%s" target=_blank">Watu PRO Settings page</a>.', 'watupro'), 'admin.php?page=watupro_options')?></p>
		</div>
	<?php return false;
	endif;?>
	
	<form method="post">
		<div class="watupro">
		<p><?php _e('Please select role to configure:', 'watupro')?> <select name="role_key" onchange="this.form.submit();">
			<option value=""><?php _e('- Please select role -', 'watupro')?></option>
			<?php foreach($enabled_roles as $role):?>
				<option value="<?php echo $role?>" <?php if(!empty($_POST['role_key']) and $_POST['role_key'] == $role) echo 'selected'?>><?php echo $role?></option>
			<?php endforeach;?>
		</select></p>
		
		<?php if(!empty($_POST['role_key'])):
			$settings = @$role_settings[$_POST['role_key']];?>
			<p><label><?php _e('Exams access:', 'watupro')?></label> <select name="exams_access">
				<option value="all" <?php if(!empty($settings['exams_access']) and $settings['exams_access'] == 'all') echo "selected"?>><?php _e('Manage all exams','watupro')?></option>
				<option value="own" <?php if(!empty($settings['exams_access']) and $settings['exams_access'] == 'own') echo "selected"?>><?php _e('Manage only exams created by the user','watupro')?></option>
				<option value="view" <?php if(!empty($settings['exams_access']) and $settings['exams_access'] == 'view') echo "selected"?>><?php _e('Only view results','watupro')?></option>
				<option value="no" <?php if(!empty($settings['exams_access']) and $settings['exams_access'] == 'no') echo "selected"?>><?php _e('No access to manage exams','watupro')?></option>
			</select> <input type="checkbox" name="apply_usergroups" value="1" <?php if(!empty($settings['apply_usergroups'])) echo 'checked'?>> <?php _e('Apply user group / user role category restrictions', 'watupro')?></p>
			
			<p><label><?php _e('Certificates access:', 'watupro')?></label> <select name="certificates_access">
				<option value="all" <?php if(!empty($settings['certificates_access']) and $settings['certificates_access'] == 'all') echo "selected"?>><?php _e('Manage all certificates','watupro')?></option>
				<option value="own" <?php if(!empty($settings['certificates_access']) and $settings['certificates_access'] == 'own') echo "selected"?>><?php _e('Manage only certificates created by the user','watupro')?></option>
				<option value="no" <?php if(!empty($settings['certificates_access']) and $settings['certificates_access'] == 'no') echo "selected"?>><?php _e('No access to manage certificates','watupro')?></option>
			</select></p>
			
			<p><label><?php _e('Exam categories access:', 'watupro')?></label> <select name="cats_access">
				<option value="all" <?php if(!empty($settings['cats_access']) and $settings['cats_access'] == 'all') echo "selected"?>><?php _e('Manage all categories','watupro')?></option>
				<option value="own" <?php if(!empty($settings['cats_access']) and $settings['cats_access'] == 'own') echo "selected"?>><?php _e('Manage only categories created by the user','watupro')?></option>
				<option value="no" <?php if(!empty($settings['cats_access']) and $settings['cats_access'] == 'no') echo "selected"?>><?php _e('No access to manage categories','watupro')?></option>
			</select></p>
			
			<p><label><?php _e('User group access:', 'watupro')?></label> <select name="usergroups_access">
				<option value="all" <?php if(!empty($settings['usergroups_access']) and $settings['usergroups_access'] == 'all') echo "selected"?>><?php _e('Manage all user groups','watupro')?></option>			
				<option value="no" <?php if(!empty($settings['usergroups_access']) and $settings['usergroups_access'] == 'no') echo "selected"?>><?php _e('No access to manage user groups','watupro')?></option>
			</select></p>
			
			<p><label><?php _e('Question categories access:', 'watupro')?></label> <select name="qcats_access">
				<option value="all" <?php if(!empty($settings['qcats_access']) and $settings['qcats_access'] == 'all') echo "selected"?>><?php _e('Manage all question categories','watupro')?></option>
				<option value="own" <?php if(!empty($settings['qcats_access']) and $settings['qcats_access'] == 'own') echo "selected"?>><?php _e('Manage only question categories created by the user','watupro')?></option>
				<option value="no" <?php if(!empty($settings['qcats_access']) and $settings['qcats_access'] == 'no') echo "selected"?>><?php _e('No access to manage question categories','watupro')?></option>
			</select></p>
			
			<p><label><?php _e('Settings page access:', 'watupro')?></label> <select name="settings_access">
				<option value="all" <?php if(!empty($settings['settings_access']) and $settings['settings_access'] == 'all') echo "selected"?>><?php _e('Manage settings','watupro')?></option>
				<option value="no" <?php if(!empty($settings['settings_access']) and $settings['settings_access'] == 'no') echo "selected"?>><?php _e('No access to manage settings','watupro')?></option>				
			</select></p>
			
			<p><input type="submit" value="<?php _e('Save configuration for this role','watupro')?>" name="config_role" class="button-primary"></p>
		<?php endif;?>
		</div>
	</form>
</div>