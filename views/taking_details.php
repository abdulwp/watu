<link type="text/css" rel="stylesheet" href="<?php echo plugins_url('/watupro/style.css') ?>" />
<div class="wrap">
	<h2><?php printf(__('Details for completed %s ', 'watupro'), __('quiz', 'watupro'))?>"<?php echo stripslashes($exam->name)?>"</h2>
	<?php if(current_user_can(WATUPRO_MANAGE_CAPS) and empty($_GET['export'])):?>
		<p><?php _e('User:', 'watupro')?> <?php
		if(!empty($taking->user_id)): $full_name = $student->first_name ?  $student->first_name.' '.$student->last_name : $student->user_login; endif; 
		echo $taking->user_id?"<a href='user-edit.php?user_id=".$taking->user_id."&wp_http_referer=".urlencode("admin.php?page=watupro_takings&exam_id=".$exam->ID)."' target='_blank'>".$full_name."</a>":($taking->email?$taking->email:"<b>N/A</b>")?><br>
	<?php endif;?>	
	<?php _e('Date:', 'watupro')?> <?php echo date(get_option('date_format'), strtotime($taking->date)) ?><br>
	<?php _e('Total points collected:', 'watupro')?> <b><?php echo $taking->points;?></b><br>
	<?php if(!empty($gtitle)):?><?php _e('Achieved grade:', 'watupro')?> <b><?php echo $gtitle;?></b><?php endif;?></p>
	
	<?php if(empty($_GET['export'])):?>
		<?php if(current_user_can(WATUPRO_MANAGE_CAPS)):?>
		<p><?php printf(__('The textual details below show exact snapshot of the questions in the way that student have seen them when taking the %s. If you have added, edited or deleted questions since then you will not see these changes here.', 'watupro'), __('quiz', 'watupro'))?></p>
		<?php endif;?>
		<?php if(empty($advanced_settings['show_only_snapshot'])):?>
			<p><a href="#" onclick="jQuery('#detailsText').hide();jQuery('#detailsTable').show();return false;"><?php _e('Table format', 'watupro')?></a>
			&nbsp;<a href="#" onclick="jQuery('#detailsText').show();jQuery('#detailsTable').hide();return false;"><?php _e('Snapshot', 'watupro')?></a>  
			&nbsp; <a href="<?php echo admin_url('admin-ajax.php?action=watupro_taking_details&noheader=1&id='. $taking->ID .'&export=1');?>"><?php _e('Download', 'watupro')?></a></p>
		<?php endif; // end if not $advanced_settings['show_only_snapshot']
	endif; // end if not export?>	
	
	<?php if(empty($_GET['export'])):?>
	<div id="detailsText" style="background:#EEE;padding:5px;<?php if(empty($advanced_settings['show_only_snapshot'])):?>display:none;<?php endif;?>">	
		<?php echo WatuPRO::cleanup($taking->details, 'web'); ?>
	</div>
	<?php endif;?>
	
	<?php if(empty($advanced_settings['show_only_snapshot'])):	
	if(empty($_GET['export'])):?> <div id="detailsTable"> <?php endif;?>	
	<table align="center" class="widefat">
	<tr><th><?php _e('ID', 'watupro')?></th><th><?php _e('Question', 'watupro')?></th><th><?php _e('Answer(s) given', 'watupro')?></th>
		<?php if(current_user_can(WATUPRO_MANAGE_CAPS) or !empty($advanced_settings['show_result_and_points'])):?>			
			<th><?php _e('Points received', 'watupro')?></th>
			<th><?php _e('Result', 'watupro');?></th>
		<?php endif;?>	
	</tr>
	<?php foreach($answers as $answer):
		$class = ('alternate' == @$class) ? '' : 'alternate';?>
		<tr class="<?php echo $class?>"><td><?php echo $answer->question_id?></td><td><?php echo ($answer->question_text == 'data removed' or empty($answer->question_text)) ? wpautop(stripslashes($answer->question)) : wpautop(stripslashes($answer->question_text))?></td>
		<td><?php echo nl2br(stripslashes($answer->answer));
		if(!empty($answer->file->ID)) echo "<p><a href=".site_url('?watupro_download_file=1&id='.$answer->file->ID).">".sprintf(__('Uploaded: %s (%d KB)', 'watupro'), $answer->file->filename, $answer->file->filesize)."</a>";
		if(!empty($answer->feedback)) echo wpautop("<b>".stripslashes($answer->feedback_label)."</b><br>".stripslashes($answer->feedback));
		if(!empty($answer->rating)) echo "<p>".sprintf(__('Rating: %d', 'watupro'), $answer->rating)."</p>";?></td>
		<?php if(current_user_can(WATUPRO_MANAGE_CAPS) or !empty($advanced_settings['show_result_and_points'])):?>		
			<td><?php echo $answer->points?></td>
			<td><?php if($answer->is_survey): _e('N/a (survey question)', 'watupro'); 
			else: 
				echo $answer->is_correct ? __('Correct answer', 'watupro') : __('Wrong answer', 'watupro');
			endif;
			if(!empty($answer->teacher_comments)): echo wpautop($answer->teacher_comments); endif;?></td>
		<?php endif;?>
		</tr>
	<?php endforeach;?>
	</table>
	<?php if(empty($_GET['export'])):?></div><?php endif;
	endif; // end if not $advanced_settings['show_only_snapshot']?>
	
</div>