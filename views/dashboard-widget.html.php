<table class="watupro-dashboard" width="100%" cellspacing="0" cellpadding="0">
	<tr><td><?php _e('Quizzes taken today:', 'watupro');?></td><td align="right"><?php echo $num_today?> / <?php echo $percent_today.'%';?> <img src="<?php echo WATUPRO_URL?>/img/arrow-<?php echo $today_arrow?>.png" alt="arrow"></td></tr>
	<tr><td><?php _e('Quizzes taken last 7 days:', 'watupro');?></td><td align="right"><?php echo $num_7_days?> / <?php echo $percent_7_days.'%';?> <img src="<?php echo WATUPRO_URL?>/img/arrow-<?php echo $sevenday_arrow?>.png" alt="arrow"></td></tr>
	<tr><td><?php _e('Quizzes taken this month:', 'watupro');?></td><td align="right"><?php echo $num_this_month?> / <?php echo $percent_this_month.'%';?> <img src="<?php echo WATUPRO_URL?>/img/arrow-<?php echo $month_arrow?>.png" alt="arrow"></td></tr>
	<tr><td><?php _e('Total published quizzes:', 'watupro');?></td><td align="right"><?php echo $total?></td></tr>
	<?php if(!empty($most_popular->ID)):?>
	<tr><td><?php _e('Most popular quiz:', 'watupro');?></td><td align="right"><a href="admin.php?page=watupro_takings&exam_id=<?php echo $most_popular->ID?>"><?php echo stripslashes($most_popular->name);?></a></td></tr>
	<?php endif;
	if(!empty($latest_attempt->ID)):?>
		<tr><td><?php _e('Latest quiz attempt on:', 'watupro');?></td><td align="right"><a href="admin.php?page=watupro_takings&exam_id=<?php echo $latest_attempt->ID?>&taking_id=<?php echo $latest_attempt->taking_id;?>"><?php printf(__('%s at %s', 'watupro'), stripslashes($latest_attempt->name), date_i18n(get_option('date_format').' '.get_option('time_format'), strtotime($latest_attempt->end_time)));?></a></td></tr>
	<?php endif;?>
</table>
