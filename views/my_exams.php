<<<<<<< HEAD
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
=======
<style type="text/css">
<?php watupro_resp_table_css(800);?>
#TB_window {
  min-width:60% !important;
}
</style>

<?php if(!$in_shortcode):?><h1><?php printf(__("My %s", 'watupro'), ucfirst(WATUPRO_QUIZ_WORD_PLURAL));?></h1><?php endif;?>
<div class="watupro-wrap">
	<?php if($user_id != $user_ID):?>
		<p><?php printf(__('Showing %s of ', 'watupro'), WATUPRO_QUIZ_WORD_PLURAL)?> <strong><?php echo $user->user_login?></strong></p>
	<?php endif;?>
	
	<?php if(empty($status) or $status == 'all' or $status == 'todo'):?>
		<h2><?php printf(__("%s to complete", 'watupro'), ucfirst(WATUPRO_QUIZ_WORD_PLURAL))?></h2>
		<?php if($num_to_take):
			if(!empty($myquizzes_grouped_by_cat)): 
			// show grouped by category, no table
				foreach($cats as $cat):
					echo '<h4>'.stripslashes($cat['name']).'</h4>';
					foreach($cat['quizzes'] as $exam):
						if(empty($exam->locked)):?>
							<p><a href="<?php echo!empty($exam->post->ID) ? get_permalink($exam->post->ID) : $exam->published_odd_url;?>" target="_blank"><?php echo stripslashes(apply_filters('watupro_qtranslate', $exam->name))?></a></p>
						<?php else:?>
						<?php echo stripslashes(apply_filters('watupro_qtranslate', $exam->name))?></a> 
							<a href="#" onclick="WatuPRODep.lockDetails(<?php echo $exam->ID?>, '<?php echo admin_url()?>');return false;"><b><?php _e("(Locked)", 'watupro')?></b></a> 
						
						<?php endif;
					endforeach;
				endforeach;
				echo '<p>&nbsp;</p>';
			else: // standard/old version design - ungrouped table ?>
			<table class="widefat watupro-table">
			<thead>
			<tr><th><?php printf(__('%s title', 'watupro'), ucfirst(WATUPRO_QUIZ_WORD))?></th><th><?php _e("Category", 'watupro')?></th></tr>
			</thead>
			<tbody>
>>>>>>> branch/6.7.2
			<?php foreach($my_exams as $exam):
				if($exam->is_taken) continue;
				$class = ('alternate' == @$class) ? '' : 'alternate';?>
				<tr class="<?php echo $class?>">
				<td><?php if(empty($exam->locked)):?>
					<a href="<?php echo!empty($exam->post->ID) ? get_permalink($exam->post->ID) : $exam->published_odd_url;?>" target="_blank"><?php echo stripslashes(apply_filters('watupro_qtranslate', $exam->name))?></a>
<<<<<<< HEAD
				<?php else:?> 
					<a href="#" onclick="WatuPRODep.lockDetails(<?php echo $exam->ID?>, '<?php echo admin_url()?>');return false;"><b><?php _e("Locked", 'watupro')?></b></a> 
				<?php endif;?></td>
				<td><?php echo $exam->cat?$exam->cat:__('Uncategorized', 'watupro');?></td></tr>
			<?php endforeach;?>
			</table>
		<?php else:?>
			<p><?php _e('There are no open quizzes to complete at this time.', 'watupro')?></p>
=======
				<?php else:?>
					<?php echo stripslashes(apply_filters('watupro_qtranslate', $exam->name))?></a> 
					<a href="#" onclick="WatuPRODep.lockDetails(<?php echo $exam->ID?>, '<?php echo admin_url()?>');return false;"><b><?php _e("(Locked)", 'watupro')?></b></a> 
					
				<?php endif;?></td>
				<td><?php echo $exam->cat?$exam->cat:__('Uncategorized', 'watupro');?></td></tr>
			<?php endforeach;?>
			</tbody>
			</table>
		<?php endif; // end if showing ungrouped in a table 
		else:?>
			<p><?php printf(__('There are no open %s to complete at this time.', 'watupro'), WATUPRO_QUIZ_WORD)?></p>
>>>>>>> branch/6.7.2
		<?php endif;
	endif; // end status condition?>
	
	<?php if(empty($status) or $status == 'all' or $status == 'completed'):?>
<<<<<<< HEAD
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
=======
		<h2><?php printf(__('Completed %s', 'watupro'), WATUPRO_QUIZ_WORD_PLURAL)?></h2>
		<?php if($num_taken):?>
			<?php if(!empty($_GET['user_id']) and $multiuser_access == 'all' and current_user_can(WATUPRO_MANAGE_CAPS)):?>
				<p><a href="#" onclick="WatuPROdeleteAllResults();return false;"><?php _e('Delete all results of this user', 'watupro')?></a></p>
			<?php endif;?>
				<?php if(empty($_GET['user_id']) and get_option('watupro_gdpr') == 1):?>
				<p><a href="#" onclick="WatuPROdeleteAllResults();return false;"><?php printf(__('Delete all my %s results and data', 'watupro'), 'watupro')?></a></p>
			<?php endif;?>
			<?php if(($multiuser_access == 'all' and current_user_can(WATUPRO_MANAGE_CAPS))
				or (get_option('watupro_gdpr') == 1)):?>
					<p>
					<a href="<?php if($in_shortcode): echo add_query_arg(['watupro_export_my_exams' => 1, 'export_results'=>1], $current_url); else:?>admin.php?page=my_watupro_exams&export_results=1&noheader=1<?php if(!empty($_GET['user_id'])) echo '&user_id='.intval($_GET['user_id']); endif; ?>"><?php _e('Export this data', 'watupro');?></a>
					</p>
			<?php endif;?>	 
			<table class="widefat watupro-table">
			<thead>
				<tr><th><?php printf(__('%s title', 'watupro'), ucfirst(WATUPRO_QUIZ_WORD));?></th><th><?php _e('Points', 'watupro')?></th>
				<th><?php _e('% Correct', 'watupro')?></th><th><?php _e('% of points', 'watupro')?></th>
				<th><?php _e('Result/Grade', 'watupro')?></th><th><?php _e('Details', 'watupro')?></th></tr>
			</thead>
			<tbody>
			<?php foreach($my_exams as $exam):
				if(!$exam->is_taken) continue;
				$exam->delay_results = WTPUser :: delay_results($exam);
				if(!current_user_can(WATUPRO_MANAGE_CAPS) and $exam->delay_results and current_time('timestamp') < strtotime($exam->delay_results_date)) {
					$exam->taking->points = __('n/a', 'watupro');
					$exam->taking->percent_correct = __('n/a', 'watupro');
					$exam->taking->result = apply_filters('watupro_content', stripslashes($exam->delay_results_content));
					$disallow_results = true;
				}
				$tclass = ('alternate' == @$tclass) ? '' : 'alternate';
				$num_takings = count($exam->takings);?>
				<tr class="<?php echo $tclass?>">
				<td><a href="<?php echo !empty($exam->post->ID) ? get_permalink($exam->post->ID) : $exam->published_odd_url;?>" target="_blank"><?php echo stripslashes(apply_filters('watupro_qtranslate', $exam->name))?></a>
				<?php if($num_takings > 1 and empty($disallow_results)):?>
>>>>>>> branch/6.7.2
					<br> <a href="#" onclick="jQuery('.prevAttempts<?php echo $exam->ID?>').toggle();return false;"><?php printf(__('+ Toggle %d previous attempts', 'watupro'), $num_takings-1)?></a>
				<?php endif;?></td>
				<td><?php echo $exam->taking->points;?></td>
				<td><?php printf(__('%d%%', 'watupro'), $exam->taking->percent_correct)?></td>
<<<<<<< HEAD
				<td><?php echo apply_filters('watupro_content', $exam->taking->result);?></td>
				<td><?php if($in_shortcode):?>
						<a href="<?php echo $target_url .'&id=' . $exam->taking->ID;?>"><?php _e('view', 'watupro')?></a>
					<?php else:?>
						<a href="#" onclick="WatuPRO.takingDetails('<?php echo $exam->taking->ID?>','<?php echo admin_url()?>');return false;"><?php _e('view', 'watupro')?></a>
					<?php endif;?>	
				</td></tr>
				<?php if($num_takings > 1):
=======
				<td><?php printf(__('%d%%', 'watupro'), $exam->taking->percent_points)?></td>
				<td><?php echo apply_filters('watupro_content', $exam->taking->result);?></td>
				<td><?php if($in_shortcode and !empty($atts['details_no_popup'])):?>
						<a href="<?php echo $target_url .'&id=' . $exam->taking->ID;?>"><?php _e('view', 'watupro')?></a>
					<?php else:?>
						<a href="#" onclick="WatuPRO.takingDetails('<?php echo $exam->taking->ID?>');return false;"><?php _e('view', 'watupro')?></a>
					<?php endif;?>	
				</td></tr>
				<?php if($num_takings > 1 and empty($disallow_results)):
>>>>>>> branch/6.7.2
					foreach($exam->takings as $ttt=>$taking):
					if($ttt == 0) continue;?>
					<tr class="<?php echo $tclass?> prevAttempts<?php echo $exam->ID?>" style="display:none;">
						<td><?php echo date_i18n($dateformat, strtotime($taking->date))?></td><td><?php echo $taking->points?></td>
<<<<<<< HEAD
						<td><?php printf(__('%d%%', 'watupro'), $taking->percent_correct)?></td><td><?php echo apply_filters('watupro_content', $taking->result)?></td>
						<td><?php if($in_shortcode):?>
							<a href="<?php echo $target_url .'&id=' . $taking->ID?>"><?php _e('view', 'watupro')?></a>
						<?php else:?>
							<a href="#" onclick="WatuPRO.takingDetails('<?php echo $taking->ID?>','<?php echo admin_url()?>');return false;"><?php _e('view', 'watupro')?></a>
=======
						<td><?php printf(__('%d%%', 'watupro'), $taking->percent_correct)?></td>
						<td><?php printf(__('%d%%', 'watupro'), $taking->percent_points)?></td>
						<td><?php echo apply_filters('watupro_content', $taking->result)?></td>
						<td><?php if($in_shortcode and !empty($atts['details_no_popup'])):?>
							<a href="<?php echo $target_url .'&id=' . $taking->ID?>"><?php _e('view', 'watupro')?></a>
						<?php else:?>
							<a href="#" onclick="WatuPRO.takingDetails('<?php echo $taking->ID?>');return false;"><?php _e('view', 'watupro')?></a>
>>>>>>> branch/6.7.2
						<?php endif;?></td>
					</tr>	
				<?php endforeach; 
				endif;?>
			<?php endforeach;?>
<<<<<<< HEAD
			</table>
		<?php else:?>
			<p><?php _e('There are no completed quizzes yet.', 'watupro')?></p>
=======
			</tbody>
			</table>
		<?php else:?>
			<p><?php printf(__('There are no completed %s yet.', 'watupro'), WATUPRO_QUIZ_WORD_PLURAL)?></p>
>>>>>>> branch/6.7.2
		<?php endif;
	endif; // end status condition ?>
</div>	

<script type="text/javascript" >
function WatuPROdeleteAllResults() {
	if(confirm("<?php _e('Are you sure? There is no undo!', 'watupro')?>")) {
<<<<<<< HEAD
		window.location = "admin.php?page=my_watupro_exams&user_id=<?php echo $_GET['user_id']?>&del_all_results=1";
	}
}
</script>
=======
		<?php if($in_shortcode):
			$url = add_query_arg(array('del_all_results' => 1, 'user_id' => intval(@$_GET['user_id'])), get_permalink($post->ID));?>
		window.location = "<?php echo $url?>";
		<?php else:?>
		window.location = "admin.php?page=my_watupro_exams&user_id=<?php echo intval(@$_GET['user_id'])?>&del_all_results=1";
		<?php endif;?>
	}
}

<?php watupro_resp_table_js();?>
</script>
>>>>>>> branch/6.7.2
