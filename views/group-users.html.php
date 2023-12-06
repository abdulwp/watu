<<<<<<< HEAD
<div class="wrap">
=======
<style type="text/css">
<?php watupro_resp_table_css(900);?>
</style>
<div class="wrap watupro-wrap">
>>>>>>> branch/6.7.2
	<h1><?php printf(__('Manage users in group "%s"', 'watupro'), stripslashes($group->name));?></h1>

	<p><?php _e('This page allows you to mass-assign users to a WatuPRO group. You can individually manage user groups for a given users from the Users -> Edit user page.', 'watupro');?></p>
	
	<p><a href="admin.php?page=watupro_groups"><?php _e('Back to user groups', 'watupro');?></a></p>
	
<<<<<<< HEAD
	<form method="post">
		<table class="widefat">
			<tr><th><input type="checkbox" onclick="watuPROSelectUsers(this.checked);"></th><th><?php _e('User ID', 'watupro');?></th><th><?php _e('Username', 'watupro');?></th><th><?php _e('Email', 'watupro');?></th></tr>
			<?php foreach($users as $user):
				$class = ('alternate' == @$class) ? '' : 'alternate';
				$user_groups=get_user_meta($user->ID, "watupro_groups", true);?>
=======
	<form method="get" action="admin.php">
	<h3><?php _e('Filter users:', 'watupro');?></h3>
	<input type="hidden" name="page" value="watupro_group_assign">
	<input type="hidden" name="group_id" value="<?php echo $group->ID;?>">
	<p><label><?php _e('Username', 'watupro')?></label> <select name="usernamef">
			<option value="equals" <?php if(empty($_GET['usernamef']) or $_GET['usernamef'] == 'equals') echo "selected"?>><?php _e('Equals', 'watupro')?></option>
			<option value="starts" <?php if(!empty($_GET['usernamef']) and $_GET['usernamef']=='starts') echo "selected"?>><?php _e('Starts with', 'watupro')?></option>
			<option value="ends" <?php if(!empty($_GET['usernamef']) and $_GET['usernamef']=='ends') echo "selected"?>><?php _e('Ends with', 'watupro')?></option>
			<option value="contains" <?php if(!empty($_GET['usernamef']) and $_GET['usernamef']=='contains') echo "selected"?>><?php _e('Contains', 'watupro')?></option>
		</select> <input type="text" name="username" value="<?php echo esc_attr(@$_GET['username'])?>">
		&nbsp;		
		<label><?php _e('Email', 'watupro')?></label> <select name="emailf">
			<option value="equals" <?php if(empty($_GET['emailf']) or $_GET['emailf']=='equals') echo "selected"?>><?php _e('Equals', 'watupro')?></option>
			<option value="starts" <?php if(!empty($_GET['emailf']) and $_GET['emailf']=='starts') echo "selected"?>><?php _e('Starts with', 'watupro')?></option>
			<option value="ends" <?php if(!empty($_GET['emailf']) and $_GET['emailf']=='ends') echo "selected"?>><?php _e('Ends with', 'watupro')?></option>
			<option value="contains" <?php if(!empty($_GET['emailf']) and $_GET['emailf']=='contains') echo "selected"?>><?php _e('Contains', 'watupro')?></option>
		</select> <input type="text" name="email" value="<?php echo @$_GET['email']?>">
		&nbsp;
		<label><?php _e('Role', 'watupro');?></label>
		<select name="role">
		<option value=""><?php _e('Any role', 'watupro')?></option>
		<?php foreach($roles as $key => $role):?>
			<option value="<?php echo $key?>" <?php if(!empty($_GET['role']) and $_GET['role']==$key) echo 'selected'?>><?php echo _x($role['name'],'User role', 'watupro')?></option>
		<?php endforeach;?>		
		</select>
		
		<input type="submit" class="button button-primary" value="<?php _e('Apply Filters', 'watupro');?>">
		<?php if(!empty($filters_sql)):?>
			<input type="button" onclick="window.location='admin.php?page=watupro_group_assign&group_id=<?php echo $group->ID;?>';" value="<?php _e('Clear filters', 'watupro');?>" class="button">
		<?php endif;?>		
		</p>
	</form>
	
	<form method="post">
		<table class="widefat watupro-table">
			<thead>
				<tr><th><input type="checkbox" onclick="watuPROSelectUsers(this.checked);"></th>
				<th><a href="admin.php?page=watupro_group_assign&group_id=<?php echo $group->ID?>&ob=ID&dir=<?php echo watupro_order_dir($ob, $dir, 'ID')?>"><?php _e('User ID', 'watupro');?></a></th>
				<th><a href="admin.php?page=watupro_group_assign&group_id=<?php echo $group->ID?>&ob=user_login&dir=<?php echo watupro_order_dir($ob, $dir, 'user_login')?>"><?php _e('Username', 'watupro');?></a></th>
				<th><a href="admin.php?page=watupro_group_assign&group_id=<?php echo $group->ID?>&ob=display_name&dir=<?php echo watupro_order_dir($ob, $dir, 'display_name')?>"><?php _e('Full Name', 'watupro');?></a></th>
				<th><a href="admin.php?page=watupro_group_assign&group_id=<?php echo $group->ID?>&ob=user_email&dir=<?php echo watupro_order_dir($ob, $dir, 'user_email')?>"><?php _e('Email', 'watupro');?></a></th></tr>
			</thead>
			<tbody>	
			<?php foreach($users as $user):
				$class = ('alternate' == @$class) ? '' : 'alternate';
				$user_groups=get_user_meta($user->ID, "watupro_groups", true);
				if(!is_array($user_groups)) $user_groups = array($user_groups);?>
>>>>>>> branch/6.7.2
				<tr class="<?php echo $class?>">
					<td><input type="checkbox" name="uids[]" value="<?php echo $user->ID?>" <?php if(@in_array($group->ID, $user_groups)) echo "checked"?> class="watupro_chk"></td>
					<td><?php echo $user->ID?></td>					
					<td><?php echo $user->user_login?></td>
<<<<<<< HEAD
					<td><?php echo $user->user_email?></td>
				</tr>
			<?php endforeach;?>
		</table>	
		
		<p><input type="submit" value="<?php _e('Assign selected users to the group', 'watupro');?>" name="assign" class="button-primary"></p>
=======
					<td><?php echo $user->display_name?></td>
					<td><?php echo $user->user_email?></td>
				</tr>
			<?php endforeach;?>
			</tbody>
		</table>	
		
		<p><input type="submit" value="<?php _e('Assign selected users to the group', 'watupro');?>" name="assign" class="button-primary"></p>
		<?php wp_nonce_field('watupro_assign');?>
>>>>>>> branch/6.7.2
	</form>
	
	<p align="center">
		<?php if($offset > 0):?>
<<<<<<< HEAD
			<a href="admin.php?page=watupro_group_assign&group_id=<?php echo $group->ID?>&offset=<?php echo $offset-100?>"><?php _e('Previous page', 'watupro');?></a>
		<?php endif;?>
		
		<?php if($offset + 100 < $cnt_users):?>
			<a href="admin.php?page=watupro_group_assign&group_id=<?php echo $group->ID?>&offset=<?php echo $offset+100?>"><?php _e('Next page', 'watupro');?></a>
		<?php endif;?> 
	</p>
=======
			<a href="admin.php?page=watupro_group_assign&group_id=<?php echo $group->ID?>&offset=<?php echo $offset-$per_page?>&ob=<?php echo $ob?>&dir=<?php echo $dir?><?php echo $filters_url;?>"><?php echo _wtpt(__('Previous page', 'watupro'));?></a>
		<?php endif;?>
		
		<?php if($offset + $per_page < $cnt_users):?>
			<a href="admin.php?page=watupro_group_assign&group_id=<?php echo $group->ID?>&offset=<?php echo $offset+$per_page?>&ob=<?php echo $ob?>&dir=<?php echo $dir?><?php echo $filters_url;?>"><?php echo _wtpt(__('Next page', 'watupro'));?></a>
		<?php endif;?> 
	</p>
	
	
	<p>&nbsp;</p>
	<h3><?php _e("Assign users to this group by uploading a CSV file.", 'watupro');?></h3>
	
	<?php if(!empty($_POST['csv_assign'])):?>
        <p class="watupro-success"><?php printf(__('%d users got their group assignment updated', 'watupro'), $num_assigned);?></p>
	<?php endif?>
	
	<p><?php _e('Note: this import will not create new user accounts. Only existing users that are recognized will be assigned to the group.', 'watupro');?>
	
	<form method="post" enctype="multipart/form-data">
        <p><label><?php _e('Number of the column that contains the login information (user login or email address):','watupro');?></label>
        <select name="by"> 
            <option value="login"><?php _e('User login', 'watupro');?></option>
            <option value="email"><?php _e('Email address', 'watupro');?></option>
        </select> <input type="text" pattern="[0-9]" required name="num_column" size="2"> <?php _e('If the file has only one column, enter "1"', 'watupro');?></p>
        <p><label><?php _e('Upload file:', 'watupro');?> <input type="file" name="csv" required accept=".csv"></p>
        <p><input type="submit" value="<?php _e('Import CSV', 'watupro');?>" name="csv_assign" class="button button-primary"></p>
        <?php wp_nonce_field('watupro_csv_assign');?>
	</form>
>>>>>>> branch/6.7.2
</div>

<script type="text/javascript" >
function watuPROSelectUsers(state) {
	if(state) jQuery('.watupro_chk').attr('checked', true);
	else jQuery('.watupro_chk').removeAttr('checked');
}
<<<<<<< HEAD
</script>
=======
<?php watupro_resp_table_js();?>
</script>
>>>>>>> branch/6.7.2
