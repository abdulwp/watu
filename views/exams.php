<div class="wrap">
<h2><?php _e("Manage Quizzes", 'watupro'); ?></h2>

<?php watupro_display_alerts(); ?>

<p><?php _e('Go to', 'watupro')?> <a href="admin.php?page=watupro_options"><?php _e('Watu PRO Settings', 'watupro')?></a></p>

<p><a href="admin.php?page=watupro_exam&amp;action=new"><?php _e("Create New Quiz", 'watupro')?></a></p>

<form method="get" action="admin.php">
<input type="hidden" name="page" value="watupro_exams">
	<p><?php _e('Filter by category:', 'watupro')?> <select name="cat_id">
		<option value="-1"><?php _e('All Categories', 'watupro')?></option>
		<option value="0" <?php if(isset($_GET['cat_id']) and $_GET['cat_id'] === '0') echo 'selected'?>><?php _e('Uncategorized', 'watupro')?></option>
		<?php foreach($cats as $cat):?>
			<option value="<?php echo $cat->ID?>" <?php if(!empty($_GET['cat_id']) and $_GET['cat_id'] == $cat->ID) echo 'selected'?>><?php echo $cat->name?></option>
		<?php endforeach;?>	
	</select>
	&nbsp;
	<?php _e('title contains:', 'watupro')?> <input type="text" name="title" value="<?php echo empty($_GET['title']) ? '' : $_GET['title']?>">
	<input type="submit" value="<?php _e('Filter quizzes', 'watupro')?>">
	
	<?php if(!empty($filter_sql)):?><input type="button" value="<?php _e('Clear filters', 'watupro')?>" onclick="window.location='admin.php?page=watupro_exams'"><?php endif;?></p>
</form>

<table class="widefat">
	<thead>
	<tr>
		<th scope="col"><div style="text-align: center;"><a href="admin.php?page=watupro_exams&dir=<?php echo $odir?>&ob=Q.ID<?php echo $filter_params?>"><?php _e('ID', 'watupro') ?></a></div></th>
		<th scope="col"><a href="admin.php?page=watupro_exams&dir=<?php echo $odir?>&ob=Q.name<?php echo $filter_params?>"><?php _e('Title', 'watupro') ?></a></th>
        <th scope="col"><?php _e('Embed Code', 'watupro') ?></th>
		<?php if(empty($low_memory_mode) or $low_memory_mode != 1):?><th scope="col"><?php _e('No. questions', 'watupro') ?></th><?php endif;?>
		<th scope="col"><a href="admin.php?page=watupro_exams&dir=<?php echo $odir?>&ob=added_on<?php echo $filter_params?>"><?php _e('Created on', 'watupro') ?></a></th>
		<th scope="col"><?php _e('Category', 'watupro') ?></th>
		<th scope="col"><?php _e('View Results', 'watupro') ?></th>
		<?php if($multiuser_access != 'view' and $multiuser_access != 'group_view'):?>
			<th scope="col"><?php _e('Manage Questions', 'watupro') ?></th>
			<th scope="col"><?php _e('Manage Grades', 'watupro') ?></th>
			<th scope="col" colspan="2"><?php _e('Edit/Delete', 'watupro') ?></th>
		<?php endif;?>
	</tr>
	</thead>

	<tbody id="the-list">
<?php
if ($count):
	foreach($exams as $quiz):
		$advanced_settings = unserialize(stripslashes($quiz->advanced_settings));
		$class = ('alternate' == @$class) ? '' : 'alternate';
		print "<tr id='quiz-{$quiz->ID}' class='$class'>\n";
		?>
		<th scope="row" style="text-align: center;"><?php echo $quiz->ID ?></th>
		<td><?php if(!empty($quiz->post)) echo "<a href='".get_permalink($quiz->post->ID)."' target='_blank'>"; 
		if(empty($quiz->post) and !empty($quiz->published_odd)) echo "<a href='".$quiz->published_odd_url."' target='_blank'>";
		echo apply_filters('watupro_qtranslate', stripslashes($quiz->name));
		if(!empty($quiz->post) or !empty($quiz->published_odd)) echo "</a>";
		if(empty($quiz->is_active)) echo "<br><i>".__('(Inactive)', 'watupro')."</i>";
		if(!empty($advanced_settings['admin_comments'])):
			echo "<p>".nl2br(stripslashes(base64_decode($advanced_settings['admin_comments'])))."</p>";
		endif;?></td>
        <td><input type="text" size="14" value="[watupro <?php echo $quiz->ID ?>]" onclick="this.select();" readonly></td>
		<?php if(empty($low_memory_mode) or $low_memory_mode != 1):?><td><?php echo empty($quiz->reuse_questions_from) ? $quiz->question_count : __('Reuses from other test(s)', 'watupro')?></td><?php endif;?>
		<td><?php echo date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($quiz->added_on)) ?><br>
		<?php printf(__('By %s', 'watupro'), $quiz->author ? $quiz->author : __('admin', 'watupro'))?></td>
		<td><?php echo $quiz->cat?$quiz->cat:__("Uncategorized", 'watupro');?></td>
		<td><a href="admin.php?page=watupro_takings&exam_id=<?php echo $quiz->ID;?>"><?php if(empty($low_memory_mode) or $low_memory_mode != 1): printf(__('Taken %d times', 'watupro'),$quiz->taken); else: _e('view results', 'watupro'); endif;?></a></td>
		<?php if($multiuser_access != 'view' and $multiuser_access != 'group_view'):?>
			<td><a href='admin.php?page=watupro_questions&amp;quiz=<?php echo $quiz->ID?>' class='edit'><?php _e('Questions', 'watupro')?></a></td>
			<td><a href='admin.php?page=watupro_grades&amp;quiz=<?php echo $quiz->ID?>' class='edit'><?php if(empty($quiz->is_personality_quiz)) _e('Grades', 'watupro');
			else _e('Personality types', 'watupro');?></a></td>
			<td><a href='admin.php?page=watupro_exam&amp;quiz=<?php echo $quiz->ID?>&amp;action=edit' class='edit'><?php _e('Edit', 'watupro'); ?></a></td>		
			<td><a href="<?php echo wp_nonce_url('admin.php?page=watupro_exams&amp;action=delete&amp;quiz='.$quiz->ID, 'delete_quiz', 'delete_nonce')?>" class='delete' onclick="return confirm('<?php echo  addslashes(__("You are about to delete this quiz? This will delete all the questions and answers within this quiz. Press 'OK' to delete and 'Cancel' to stop.", 'watupro'))?>');"><?php _e('Delete', 'watupro')?></a></td>
		<?php endif;?>	
		</tr>
<?php endforeach;?>
	<tr><td colspan="8"><p><strong><?php _e('To publish any of the existing tests simply copy the "Embed code" shown in the table above and paste it in a post or page of your blog. Please do not paste more than one of these shortcodes in a single post or page.', 'watupro')?></strong></p></td></tr>	
<?php else:?>
	<tr>
		<td colspan="7"><?php _e('No tests found.', 'watupro') ?></td>
	</tr>
<?php endif;?>
	</tbody>
</table>
<?php if($count):?>
	<p align="center"> <?php if($offset > 0):?><a href="admin.php?page=watupro_exams&dir=<?php echo $dir?>&ob=<?php echo $ob?>&offset=<?php echo ($offset - 50)?><?php echo $filter_params?>"><?php _e('Previous page', 'watupro')?></a><?php endif;?>
	&nbsp;
	 <?php if(($offset + 50) < $count):?><a href="admin.php?page=watupro_exams&dir=<?php echo $dir?>&ob=<?php echo $ob?>&offset=<?php echo ($offset + 50)?><?php echo $filter_params?>"><?php _e('Next page', 'watupro')?></a><?php endif;?> </p>
<?php endif;?>

	<p align="center"><i><?php echo "WatuPRO version ".watupro_get_version()?></i></p>
</div>