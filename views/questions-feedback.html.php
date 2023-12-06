<div class="wrap">
	<h1><?php printf(__('User feedback on questions from quiz "%s"', 'watupro'), stripslashes($quiz->name))?></h1>
	
	<p><a href="admin.php?page=watupro_takings&exam_id=<?php echo $quiz->ID?>"><?php _e('Back to the results on this quiz', 'watupro')?></a></p>
	
	<?php if(!$count):?>
		<p><?php _e('There is no feedback left for any of the questions on this quiz yet.', 'watupro')?></p>
		</div>
	<?php return;
	endif;?>
	
	<table class="widefat">
		<tr><th><?php _e('Question', 'watupro')?></th><th><?php _e('Answer', 'watupro')?></th>
		<th><?php _e('Feedback', 'watupro')?></th><th><?php _e('Left on', 'watupro')?></th>
		<th><?php _e('Quiz result', 'watupro')?></th><th><?php _e('View details', 'watupro')?></th></tr>
		
		<?php foreach($feedbacks as $feedback):
			$class = ('alternate' == @$class) ? '' : 'alternate';?>
			<tr class="<?php echo $class?>">
				<td><?php echo apply_filters('watupro_content', stripslashes($feedback->question))?></td>
				<td><?php echo apply_filters('watupro_content', stripslashes($feedback->answer))?></td>
				<td><?php echo apply_filters('watupro_content', stripslashes($feedback->feedback))?></td>
				<td><?php echo date($dateformat, strtotime($feedback->taking_date))?></td>
				<td><?php echo stripslashes($feedback->taking_result)?></td>
				<td><a href="admin.php?page=watupro_takings&exam_id=<?php echo $quiz->ID?>&taking_id=<?php echo $feedback->taking_id?>" target="_blank"><?php _e('view', 'watupro')?></a></td>
			</tr>
		<?php endforeach;?>
	</table>
</div>	