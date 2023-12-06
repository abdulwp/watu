<?php if(!$in_shortcode):?><h1><?php printf(__("My %s", 'watupro'), __('Quizzes', 'watupro'));?></h1><?php endif;?>
<div class="wrap">
	<?php if($user_id != $user_ID):?>
		<p><?php printf(__('Showing %s of ', 'watupro'), __('quizzes', 'watupro'))?> <strong><?php echo $user->user_login?></strong></p>
	<?php endif;?>
	
	<?php if(empty($status) or $status == 'all' or $status == 'todo'):?>
		<h2><?php printf(__("%s to complete", 'watupro'), __('Quizzes', 'watupro'))?></h2>
		<?php if($num_to_take):?>
			<table class="widefat">
			<tr><th><?php printf(__('%s title', 'watupro'), __('Quiz', 'watupro'))?></th><th><?php _e("Category", 'watupro')?></th></tr>
			<?php foreach($my_exams as $exam):
				if($exam->is_taken) continue;
				$class = ('alternate' == @$class) ? '' : 'alternate';?>
				<tr class="<?php echo $class?>">
				<td><?php if(empty($exam->locked)):?>
					<a href="<?php echo!empty($exam->post->ID) ? get_permalink($exam->post->ID) : $exam->published_odd_url;?>" target="_blank"><?php echo stripslashes(apply_filters('watupro_qtranslate', $exam->name))?></a>
				<?php else:?> 
					<a href="#" onclick="WatuPRODep.lockDetails(<?php echo $exam->ID?>, '<?php echo admin_url()?>');return false;"><b><?php _e("Locked", 'watupro')?></b></a> 
				<?php endif;?></td>
				<td><?php echo $exam->cat?$exam->cat:__('Uncategorized', 'watupro');?></td></tr>
			<?php endforeach;?>
			</table>
		<?php else:?>
			<p><?php _e('There are no open quizzes to complete at this time.', 'watupro')?></p>
		<?php endif;
	endif; // end status condition?>
	
	<?php if(empty($status) or $status == 'all' or $status == 'completed'):?>
		<h2><?php _e('Completed quizzes', 'watupro')?></h2>
		<?php if($num_taken):?>
			<?php if(!empty($_GET['user_id']) and $multiuser_access == 'all' and current_user_can(WATUPRO_MANAGE_CAPS)):?>
				<p><a href="#" onclick="WatuPROdeleteAllResults();"><?php _e('Delete all results of this user', 'watupro')?></a></p>
			<?php endif;?>
			<table class="widefat">
			<tr><th><?php _e('Quiz title', 'watupro')?></th><th><?php _e('Points', 'watupro')?></th>
			<th><?php _e('% Correct', 'watupro')?></th>
			<th><?php _e('Result/Grade', 'watupro')?></th><th><?php _e('Details', 'watupro')?></th></tr>
			<?php foreach($my_exams as $exam):
				if(!$exam->is_taken) continue;
				$tclass = ('alternate' == @$tclass) ? '' : 'alternate';
				$num_takings = sizeof($exam->takings);?>
				<tr class="<?php echo $tclass?>">
				<td><a href="<?php echo !empty($exam->post->ID) ? get_permalink($exam->post->ID) : $exam->published_odd_url;?>" target="_blank"><?php echo stripslashes(apply_filters('watupro_qtranslate', $exam->name))?></a>
				<?php if($num_takings > 1):?>
					<br> <a href="#" onclick="jQuery('.prevAttempts<?php echo $exam->ID?>').toggle();return false;"><?php printf(__('+ Toggle %d previous attempts', 'watupro'), $num_takings-1)?></a>
				<?php endif;?></td>
				<td><?php echo $exam->taking->points;?></td>
				<td><?php printf(__('%d%%', 'watupro'), $exam->taking->percent_correct)?></td>
				<td><?php echo apply_filters('watupro_content', $exam->taking->result);?></td>
				<td><?php if($in_shortcode):?>
						<a href="<?php echo $target_url .'&id=' . $exam->taking->ID;?>"><?php _e('view', 'watupro')?></a>
					<?php else:?>
						<a href="#" onclick="WatuPRO.takingDetails('<?php echo $exam->taking->ID?>','<?php echo admin_url()?>');return false;"><?php _e('view', 'watupro')?></a>
					<?php endif;?>	
				</td></tr>
				<?php if($num_takings > 1):
					foreach($exam->takings as $ttt=>$taking):
					if($ttt == 0) continue;?>
					<tr class="<?php echo $tclass?> prevAttempts<?php echo $exam->ID?>" style="display:none;">
						<td><?php echo date_i18n($dateformat, strtotime($taking->date))?></td><td><?php echo $taking->points?></td>
						<td><?php printf(__('%d%%', 'watupro'), $taking->percent_correct)?></td><td><?php echo apply_filters('watupro_content', $taking->result)?></td>
						<td><?php if($in_shortcode):?>
							<a href="<?php echo $target_url .'&id=' . $taking->ID?>"><?php _e('view', 'watupro')?></a>
						<?php else:?>
							<a href="#" onclick="WatuPRO.takingDetails('<?php echo $taking->ID?>','<?php echo admin_url()?>');return false;"><?php _e('view', 'watupro')?></a>
						<?php endif;?></td>
					</tr>	
				<?php endforeach; 
				endif;?>
			<?php endforeach;?>
			</table>
		<?php else:?>
			<p><?php _e('There are no completed quizzes yet.', 'watupro')?></p>
		<?php endif;
	endif; // end status condition ?>
</div>	

<script type="text/javascript" >
function WatuPROdeleteAllResults() {
	if(confirm("<?php _e('Are you sure? There is no undo!', 'watupro')?>")) {
		window.location = "admin.php?page=my_watupro_exams&user_id=<?php echo $_GET['user_id']?>&del_all_results=1";
	}
}
</script>