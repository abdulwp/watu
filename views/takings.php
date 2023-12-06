<?php if(!$in_shortcode):?>
<div class="wrap">
	<?php if(!empty($exam->ID)):?>
		<h1><?php printf(__('Users who took quiz "%s"', 'watupro'), stripslashes(apply_filters('watupro_qtranslate', $exam->name)));?></h1>
	<?php else:?>
		<h1><?php _e('All Quiz Submissions', 'watupro');?></h1>
	<?php endif;?>	
	
	<?php if(!empty($_GET['msg'])):?>
		<p class="watupro-success"><?php echo $_GET['msg']?></p>
	<?php endif;?>

	<?php if(!empty($exam->ID)):?>
		<p><a href="admin.php?page=watupro_exams"><?php _e('Back to quizzes list', 'watupro')?></a> 
	&nbsp;
	<a href="edit.php?page=watupro_exam&quiz=<?php echo $exam->ID?>&action=edit"><?php _e('Edit this quiz', 'watupro')?></a></p>
	<?php endif; // if(!empty($exam->ID))?>	
		<p>		
		<a href="#" onclick="jQuery('#filterForm').toggle('slow');return false;"><?php _e('Filter/search these records', 'watupro')?></a> 
		
	<?php if(!empty($exam->ID)):?>	
		| <a href="admin.php?page=watupro_takings&exam_id=<?php echo $exam->ID?>&ob=<?php echo $ob?>&dir=<?php echo $dir;?>&<?php echo $filters_url;?>&export=1&noheader=1"><?php _e('Export this page', 'watupro')?><?php if($display_filters):?> <?php _e('(Filters apply)', 'watupro')?><?php endif;?></a>
		| <a href="admin.php?page=watupro_takings&exam_id=<?php echo $exam->ID?>&ob=<?php echo $ob?>&dir=<?php echo $dir;?>&<?php echo $filters_url;?>&export=1&details=1&noheader=1"><?php _e('Export with details', 'watupro')?><?php if($display_filters):?> <?php _e('(Filters apply)', 'watupro')?><?php endif;?></a> 
		| <a href="admin.php?page=watupro_questions_feedback&quiz_id=<?php echo $exam->ID?>"><?php _e('Feedback on questions', 'watupro')?></a>
		<?php if(watupro_module('reports')):?>| <a href="admin.php?page=watupro_question_stats&exam_id=<?php echo $exam->ID?>"><?php _e('Stats per question', 'watupro')?></a>
		| <a href="admin.php?page=watupro_cat_stats&exam_id=<?php echo $exam->ID?>"><?php _e('Stats per category', 'watupro')?></a>
		| <a href="admin.php?page=watupro_question_chart&exam_id=<?php echo $exam->ID?>"><?php _e('Chart by grade', 'watupro')?></a><?php endif;?> 	
		<?php if(watupro_intel() and @$exam->fee > 0):?>
		| <a href="admin.php?page=watupro_payments&exam_id=<?php echo $exam->ID?>"><?php _e('View Payments', 'watupro')?></a>
		<?php endif;?></p>
		<p><?php printf(__('Export files are currently delimited by "%s". You can change the delimiter at <a href="%s">WatuPRO Settings</a> page.', 'watupro'), get_option('watupro_csv_delim'), 'admin.php?page=watupro_options');?> </p>
		
		<?php if($in_progress):?><p><a href="admin.php?page=watupro_takings&exam_id=<?php echo $exam->ID?>"><?php _e('Back to completed results', 'watupro')?></a></p><?php endif;
		if(!empty($num_unfinished)):?><p><a href="admin.php?page=watupro_takings&exam_id=<?php echo $exam->ID?>&in_progress=1"><?php printf(__('There are %d unfinished attempt(s).','watupro'), $num_unfinished);?></a></p><?php endif;?>
		
		<p><?php _e('Shortcode to publish a simplified version of this page:', 'watupro');?> <input type="text" size="30" value='[watupro-takings quiz_id=<?php echo $exam->ID?>]' readonly="readonly" onclick="this.select();"></p>
	<?php endif; // if(!empty($exam->ID))?>
	
	<div id="filterForm" style="display:<?php echo $display_filters?'block':'none';?>;margin-bottom:10px;padding:5px;" class="widefat">
	<form method="get" class="watupro" action="admin.php">
	<input type="hidden" name="page" value="watupro_takings">
			<div><label><?php _e('Quiz:', 'watupro');?></label> <select name="exam_id" onchange="wtpPopulateGrades(this.value);">
				<option value="0"><?php _e('- All quizzes -', 'watupro');?></option>
				<?php foreach($dd_quizzes as $q):
					$selected = ($q->ID == @$exam->ID) ? ' selected' : '';?>
					<option value="<?php echo $q->ID?>"<?php echo $selected?>><?php echo stripslashes(apply_filters('watupro_qtranslate', $q->name));?></option>
				<?php endforeach;?>
			</select></div>
		<div><label><?php _e('Username', 'watupro')?></label> <select name="dnf">
			<option value="equals" <?php if(empty($_GET['dnf']) or $_GET['dnf']=='equals') echo "selected"?>><?php _e('Equals', 'watupro')?></option>
			<option value="starts" <?php if(!empty($_GET['dnf']) and $_GET['dnf']=='starts') echo "selected"?>><?php _e('Starts with', 'watupro')?></option>
			<option value="ends" <?php if(!empty($_GET['dnf']) and $_GET['dnf']=='ends') echo "selected"?>><?php _e('Ends with', 'watupro')?></option>
			<option value="contains" <?php if(!empty($_GET['dnf']) and $_GET['dnf']=='contains') echo "selected"?>><?php _e('Contains', 'watupro')?></option>
		</select> <input type="text" name="dn" value="<?php echo @$_GET['dn']?>"></div>
		<div><label><?php _e('Email', 'watupro')?></label> <select name="emailf">
			<option value="equals" <?php if(empty($_GET['emailf']) or $_GET['emailf']=='equals') echo "selected"?>><?php _e('Equals', 'watupro')?></option>
			<option value="starts" <?php if(!empty($_GET['emailf']) and $_GET['emailf']=='starts') echo "selected"?>><?php _e('Starts with', 'watupro')?></option>
			<option value="ends" <?php if(!empty($_GET['emailf']) and $_GET['emailf']=='ends') echo "selected"?>><?php _e('Ends with', 'watupro')?></option>
			<option value="contains" <?php if(!empty($_GET['emailf']) and $_GET['emailf']=='contains') echo "selected"?>><?php _e('Contains', 'watupro')?></option>
		</select> <input type="text" name="email" value="<?php echo @$_GET['email']?>"></div>
		<?php if(!empty($advanced_settings['ask_for_contact_details'])): 
		if(!empty($advanced_settings['contact_fields']['company'])):?>
			<div><label><?php echo $advanced_settings['contact_fields']['company_label'];?></label> <select name="companyf">
			<option value="equals" <?php if(empty($_GET['companyf']) or $_GET['companyf']=='equals') echo "selected"?>><?php _e('Equals', 'watupro')?></option>
			<option value="starts" <?php if(!empty($_GET['companyf']) and $_GET['companyf']=='starts') echo "selected"?>><?php _e('Starts with', 'watupro')?></option>
			<option value="ends" <?php if(!empty($_GET['companyf']) and $_GET['companyf']=='ends') echo "selected"?>><?php _e('Ends with', 'watupro')?></option>
			<option value="contains" <?php if(!empty($_GET['companyf']) and $_GET['companyf']=='contains') echo "selected"?>><?php _e('Contains', 'watupro')?></option>
		</select> <input type="text" name="field_company" value="<?php echo @$_GET['field_company']?>"></div>
		<?php endif;
		if(!empty($advanced_settings['contact_fields']['phone'])):?>
			<div><label><?php echo $advanced_settings['contact_fields']['phone_label'];?></label> <select name="phonef">
			<option value="equals" <?php if(empty($_GET['phonef']) or $_GET['phonef']=='equals') echo "selected"?>><?php _e('Equals', 'watupro')?></option>
			<option value="starts" <?php if(!empty($_GET['phonef']) and $_GET['phonef']=='starts') echo "selected"?>><?php _e('Starts with', 'watupro')?></option>
			<option value="ends" <?php if(!empty($_GET['phonef']) and $_GET['phonef']=='ends') echo "selected"?>><?php _e('Ends with', 'watupro')?></option>
			<option value="contains" <?php if(!empty($_GET['phonef']) and $_GET['phonef']=='contains') echo "selected"?>><?php _e('Contains', 'watupro')?></option>
		</select> <input type="text" name="field_phone" value="<?php echo @$_GET['field_phone']?>"></div>
		<?php endif;
		endif; // end if ask for cobntact ?>
		<div><label><?php _e('IP Address', 'watupro')?></label> <select name="ipf">
			<option value="equals" <?php if(empty($_GET['ipf']) or $_GET['ipf']=='equals') echo "selected"?>><?php _e('Equals', 'watupro')?></option>
			<option value="starts" <?php if(!empty($_GET['ipf']) and $_GET['ipf']=='starts') echo "selected"?>><?php _e('Starts with', 'watupro')?></option>
			<option value="ends" <?php if(!empty($_GET['ipf']) and $_GET['ipf']=='ends') echo "selected"?>><?php _e('Ends with', 'watupro')?></option>
			<option value="contains" <?php if(!empty($_GET['ipf']) and $_GET['ipf']=='contains') echo "selected"?>><?php _e('Contains', 'watupro')?></option>
		</select> <input type="text" name="ip" value="<?php echo @$_GET['ip']?>"></div>
		<div><label><?php _e('Date Taken', 'watupro')?></label> <select name="datef">
			<option value="equals" <?php if(empty($_GET['datef']) or $_GET['datef']=='equals') echo "selected"?>><?php _e('Equals', 'watupro')?></option>
			<option value="before" <?php if(!empty($_GET['datef']) and $_GET['datef']=='before') echo "selected"?>><?php _e('Is before', 'watupro')?></option>
			<option value="after" <?php if(!empty($_GET['datef']) and $_GET['datef']=='after') echo "selected"?>><?php _e('Is after', 'watupro')?></option>			
		</select> <input type="text" name="date" value="<?php echo @$_GET['date']?>"> <i>YYYY-MM-DD</i></div>
		<div><label><?php _e('Points received', 'watupro')?></label> <select name="pointsf">
			<option value="equals" <?php if(empty($_GET['pointsf']) or $_GET['pointsf']=='equals') echo "selected"?>><?php _e('Equal', 'watupro')?></option>
			<option value="less" <?php if(!empty($_GET['pointsf']) and $_GET['pointsf']=='less') echo "selected"?>><?php _e('Are less than', 'watupro')?></option>
			<option value="more" <?php if(!empty($_GET['pointsf']) and $_GET['pointsf']=='more') echo "selected"?>><?php _e('Are more than', 'watupro')?></option>			
		</select> <input type="text" name="points" value="<?php echo @$_GET['points']?>"></div>
		
		<div><label><?php _e('% correct answers', 'watupro')?></label> <select name="percentf">
			<option value="equals" <?php if(empty($_GET['percentf']) or $_GET['percentf']=='equals') echo "selected"?>><?php _e('Equal', 'watupro')?></option>
			<option value="less" <?php if(!empty($_GET['percentf']) and $_GET['percentf']=='less') echo "selected"?>><?php _e('Is less than', 'watupro')?></option>
			<option value="more" <?php if(!empty($_GET['percentf']) and $_GET['percentf']=='more') echo "selected"?>><?php _e('Is more than', 'watupro')?></option>			
		</select> <input type="text" name="percent_correct" value="<?php echo @$_GET['percent_correct']?>"></div>		
		
		
		<div style="display:<?php echo empty($exam->ID) ? 'none' : 'block';?>" id="wtpSelectGrade"><label><?php _e('Grade equals', 'watupro')?></label> <select name="grade" id="wtpGradeSelector">
		<option value="" <?php if(empty($_GET['grade'])) echo "selected"?>>------</option>
		<?php foreach($grades as $grade):?>
			<option value="<?php echo $grade->ID?>" <?php if(!empty($_GET['grade']) and $_GET['grade']==$grade->ID) echo "selected"?>><?php echo $grade->gtitle;?></option>
		<?php endforeach;?>
		</select></div>
		
		
		<div><label><?php _e('User role is', 'watupro')?></label> <select name="role">
		<option value=""><?php _e('Any role', 'watupro')?></option>
		<?php foreach($roles as $key => $role):?>
			<option value="<?php echo $key?>" <?php if(!empty($_GET['role']) and $_GET['role']==$key) echo 'selected'?>><?php echo _x($role['name'],'User role', 'watupro')?></option>
		<?php endforeach;?>		
		</select></div>
		
		<?php if(!get_option('watupro_use_wp_roles') and sizeof($groups)):?>
		<div><label><?php _e('User is in group', 'watupro')?></label> <select name="ugroup">
		<option value=""><?php _e('Any group', 'watupro')?></option>
		<?php foreach($groups as $group):?>
			<option value="<?php echo $group->ID?>" <?php if(!empty($_GET['ugroup']) and $_GET['ugroup']==$group->ID) echo 'selected'?>><?php echo $group->name?></option>
		<?php endforeach;?>		
		</select></div>
		<?php endif;?>		
		
		<div><input type="submit" value="<?php _e('Search/Filter', 'watupro')?>" class="button-primary">
		<input type="button" value="<?php _e('Clear Filters', 'watupro')?>" onclick="window.location='admin.php?page=watupro_takings&exam_id=<?php echo @$exam->ID;?>';" class="button"></div>
	</form>
	</div>
	<?php endif; // end if not in shortcode
	if(!sizeof($takings)):?>
		<p><?php _e('There are no records that match your search criteria', 'watupro')?></p>
	<?php else:?>
		<table class="<?php echo $in_shortcode ? 'watupro-table' : 'widefat' ?>">
		<tr><?php if(empty($exam->ID)):?>
			<th><a href="<?php echo $target_url?>&ob=exam_name&dir=<?php echo $odir;?>&<?php echo $filters_url;?>"><?php _e('Quiz Name', 'watupro')?></a></th>
		<?php endif;?>
		<th><a href="<?php echo $target_url?>&ob=display_name&dir=<?php echo $odir;?>&<?php echo $filters_url;?>"><?php _e('Name', 'watupro')?></a></th><th><a href="<?php echo $target_url?>&ob=user_email&dir=<?php echo $odir;?>&<?php echo $filters_url;?>"><?php _e('Email', 'watupro')?></a></th>
		<?php if(!$in_shortcode):?><th><a href="<?php echo $target_url?>&ob=ip&dir=<?php echo $odir;?>&<?php echo $filters_url;?>"><?php _e("IP", 'watupro')?></a></th><?php endif;?>
		<th><a href="<?php echo $target_url?>&ob=date&dir=<?php echo $odir;?>&<?php echo $filters_url;?>"><?php _e('Date', 'watupro')?></a></th>
		<th><a href="<?php echo $target_url?>&ob=points&dir=<?php echo $odir;?>&<?php echo $filters_url;?>"><?php _e('Points', 'watupro')?></a></th>
		<th><a href="<?php echo $target_url?>&ob=percent_correct&dir=<?php echo $odir;?>&<?php echo $filters_url;?>"><?php _e('% correct', 'watupro')?></a></th><th><a href="<?php echo $target_url?>&ob=result&dir=<?php echo $odir;?>&<?php echo $filters_url;?>"><?php _e('Grade', 'watupro')?></a>
		<?php if($has_catgrades):?>[<a href="#" onclick="jQuery('.watupro_global_grade').toggle();jQuery('.watupro_cat_grade').toggle();return false;"><?php _e('Toggle per category', 'watupro');?></a>]<?php endif;?>		
		</th>
		<th><?php _e('Time spent', 'watupro')?></th>
		<?php if(!$in_shortcode):?>
			<th><?php _e('Details', 'watupro')?></th>
			<?php if($multiuser_access != 'view' and $multiuser_access != 'group_view'):?><th><?php _e('Delete', 'watupro')?></th><?php endif;
		endif; // end if not in shortcode?>
		</tr>
		<?php foreach($takings as $taking):
			$taking_name_braces = empty($taking->name) ? "" : "<br>(".stripslashes($taking->name).")"; // used to show for logged in users
			$class = ('alternate' == @$class) ? '' : 'alternate';
			$taking_email = empty($taking->email) ? $taking->user_email : $taking->email;?>
			<tr id="taking<?php echo $taking->ID?>" class="<?php echo $class?>">
			<?php if(empty($exam->ID)):?>
				<td><a href="admin.php?page=watupro_takings&exam_id=<?php echo $taking->exam_id?>"><?php echo stripslashes(apply_filters('watupro_qtranslate', $taking->exam_name))?></a></td>
			<?php endif;?>
			<td><?php $user_link = class_exists('WTPReports') ? "admin.php?page=watupro_reports&user_id=".$taking->user_id : "admin.php?page=my_watupro_exams&user_id=" . $taking->user_id; 
			echo ($taking->user_id and !$in_shortcode) ? "<a href='".$user_link."' target='_blank'>".$taking->display_name."</a>" . $taking_name_braces : (empty($taking->name) ? "N/A" : stripslashes($taking->name)); 
			if(!empty($taking->contact_data) and !$in_shortcode) echo '<br>'.$taking->contact_data;
			if(!empty($taking->user_groups) and !$in_shortcode) echo '<br>'.sprintf(__('User groups: %s', 'watupro'), $taking->user_groups);?></td>
			<td><?php echo !empty($taking_email) ? "<a href='mailto:".$taking_email."'>".$taking_email."</a>" : "N/A"?></td>
			<?php if(!$in_shortcode):?><td><?php echo $taking->ip;?></td><?php endif;?>
			<td><?php echo date_i18n($dateformat.' '.$timeformat, strtotime(($taking->end_time == '2000-01-01 00:00:00') ? $taking->date : $taking->end_time)) ?></td>
			<td><?php echo $taking->in_progress ? __('N/A', 'watupro') : $taking->points;?></td>
			<td><?php echo $taking->in_progress ? __('N/A', 'watupro') : sprintf(__('%%%d', 'watupro'), $taking->percent_correct);?></td>
			<td><div class="watupro_global_grade"><?php if($in_shortcode): $taking->result = stripslashes($taking->grade_title); endif; 
			echo $taking->result ? preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", apply_filters('watupro_content', apply_filters('watupro_qtranslate', $taking->result))) : _e('N/A', 'watupro');
			if(trim(strip_tags($taking->result)) == __('None', 'watupro') and empty($none_info)):
				printf(' '.__('(<a href="%s" target="_blank">Why?</a>)', 'watupro'), 'http://blog.calendarscripts.info/receiving-grade-none-or-unexpected-grade-watupro/');
				$none_info = true;
			endif;?></div>
			<?php if($has_catgrades):?>
			<div class="watupro_cat_grade" style="display:none;"><?php $catgrades = unserialize(stripslashes($taking->catgrades_serialized));
				if(is_array($catgrades)):?>
				<table class="widefat">
					<tr><th><?php _e('Category', 'watupro');?></th><th><?php _e('% correct', 'watupro');?></th><th><?php _e('Points', 'watupro');?></th><th><?php _e('Grade', 'watupro');?></th></tr>
					<?php foreach($catgrades as $catgrade):
						$cls = ('alternate' == @$cls) ? '' : 'alternate';?>
						<tr class="<?php echo $cls;?>"><td><?php echo stripslashes($catgrade['name']);?></td><td><?php echo $catgrade['percent'];?>%</td><td><?php echo $catgrade['points'];?></td>
						<td><?php echo $catgrade['gtitle'] ? stripslashes($catgrade['gtitle']) : __('N/a', 'watupro');?></td></tr>
					<?php endforeach;?>	
				</table>
				<?php endif; // end if is_array($catgrades);?>
			</div>
			<?php endif;?></td>
			<td><?php echo WTPRecord :: time_spent_human(WTPRecord :: time_spent($taking));?></td>
			<?php if(!$in_shortcode):?>			
				<td><?php if($taking->in_progress): _e('N/A', 'watupro'); else:?><a href="#" onclick="WatuPRO.takingDetails('<?php echo $taking->ID?>');return false;"><?php _e('view', 'watupro')?></a>
				<?php if(watupro_intel() and $multiuser_access != 'view' and $multiuser_access != 'group_view'):?>
				/ <a href="admin.php?page=watupro_edit_taking&id=<?php echo $taking->ID?>"><?php _e('edit', 'watupro')?></a>
				<?php if(!empty($taking->last_edited)) printf("<br>".__('Last edited on %s', 'watupro'), date($dateformat, strtotime($taking->last_edited)));  
					endif;// end if Intelligence enabled
				endif;// end if not in progress?>		
				</td>
				<?php if($multiuser_access != 'view' and $multiuser_access != 'group_view'):?><td><a href="#" onclick="deleteTaking(<?php echo $taking->ID?>);return false;"><?php _e('delete', 'watupro')?></a></td><?php endif;
				endif; // end of not in shortcode?>
			</tr>
		<?php endforeach;?>
		</table>
		
		<p><?php _e('Showing', 'watupro')?> <?php echo ($offset+1)?> - <?php echo ($offset+10)>$count?$count:($offset+10)?> <?php _e('from', 'watupro')?> <?php echo $count;?> <?php _e('records', 'watupro')?></p>
		
		<p align="center">
		<?php if($offset>0):?>
			<a href="<?php echo $target_url?>&offset=<?php echo $offset-10;?>&ob=<?php echo $ob?>&dir=<?php echo $dir?>&<?php echo $filters_url;?>"><?php _e('previous page', 'watupro')?></a>
		<?php endif;?>
		&nbsp;
		<?php if($count>($offset+10)):?>
			<a href="<?php echo $target_url?>&offset=<?php echo $offset+10;?>&ob=<?php echo $ob?>&dir=<?php echo $dir?>&<?php echo $filters_url;?>"><?php _e('next page', 'watupro')?></a>
		<?php endif;?>
		</p>
		
		<?php if(!$in_shortcode):?>
		<form method="post" onsubmit="return validateCleanup(this)">
			<p><input type="checkbox" name="yesiamsure" value="1" onclick="this.checked ? jQuery('#cleanupBtn').show() : jQuery('#cleanupBtn').hide()"> <?php _e('Show me a button to cleanup all submitted data on this quiz.', 'watupro')?></p> 
			
			<div  style="display:none;" id="cleanupBtn">
				<p><?php _e('Cleaning up all data may affect user levels and points, and the reports. Alternatively you can just blank out the data which will keep all user points and reports and will only remove the textual data from some fields. This will reduce less DB space but will keep most of the things intact.', 'watupro')?></p>
				<p style="color:red;font-weight:bold;"><?php _e('These operations cannot be undone!', 'watupro')?></p>
				<p><input type="submit" name="blankout" value="<?php _e('Blank out data', 'watupro')?>" class="button-primary">
				<input type="submit" name="cleanup" value="<?php _e('Cleanup all data', 'watupro')?>" class="button-primary"></p>
			</div>
			<?php wp_nonce_field('watupro_cleanup');?>
		</form>
	<?php endif; // end if there are takings ?>	
<?php if(!$in_shortcode):?></div><?php endif;?>

<script type="text/javascript">
function deleteTaking(id) {
	// delete taking data by ajax and remove the row with jquery
	if(!confirm("Are you sure?")) return false;
	
	data={"action":'watupro_delete_taking', "id": id};
	jQuery.get(ajaxurl, data, function(msg) {
		if(msg!='') {
			alert(msg);
			return false;
		}
			
		// empty msg means success, remove the row
		jQuery('#taking'+id).remove();
	});	
}

function validateCleanup(frm) {
	if(confirm("<?php _e('Are you sure? This operation cannot be undone!', 'watupro')?>")) return true;
	return false;
}

// populates the grades drop-down depending on the selected exam
function wtpPopulateGrades(examID) {
	if(examID > 0) jQuery('#wtpSelectGrade').show();
	else jQuery('#wtpSelectGrade').hide(); 
	
	var data = {"exam_id": examID, 'action': 'watupro_ajax', 'do': 'select_grades'};
	
	jQuery.post("<?php echo admin_url('admin-ajax.php')?>", data, function(msg) {
		jQuery('#wtpGradeSelector').html(msg);
	});
}
</script>
<?php endif; // end if not in shortcode ?>

<div id="takingDiv"></div>