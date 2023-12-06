<div class="wrap">
	<h1><?php printf(__('Watu PRO %s Categories', 'watupro'), __('Quiz', 'watupro'))?></h1>

	<p><?php _e('Categories can be used to organize your exams by topic. The most useful part of this is that you can limit the access to categories for the different', 'watupro')?> <a href="admin.php?page=watupro_groups"><?php _e('user groups', 'watupro')?></a>.</p>

	<?php if(sizeof($cats)):?>
		<table class="widefat">
        <tr><td colspan="2"><a href="admin.php?page=watupro_cats&do=add"><?php _e('Click here to add new category', 'watupro')?></a></td></tr>
		<tr><th><?php _e('ID', 'watupro')?></th><th><?php _e('Category Name', 'watupro')?></th><th><?php _e('Shortcode for exams list', 'watupro')?></th>
		<th><?php _e('Accessible to', 'watupro');?></th><th><?php _e('Edit', 'watupro')?></th></tr>
		<?php foreach($cats as $cat):
			$class = ('alternate' == @$class) ? '' : 'alternate';?>
			<tr class="<?php echo $class?>">
			<td><?php echo $cat->ID?></td>
			<td><?php echo stripslashes($cat->name);?></a></td>
			<td><input type="text" value="[watuprolist cat_id=<?php echo $cat->ID?>]" onclick="this.select();" readonly="readonly"></td>	
			<td><?php echo $cat->allowed_to;?></td>	
			<td><a href="admin.php?page=watupro_cats&do=edit&id=<?php echo $cat->ID?>"><?php _e('Edit', 'watupro')?></a></td></tr>
		<?php endforeach;?>
		</table>
	<?php else:?>
    <p><?php _e('You have not created any categories yet.', 'watupro')?> <a href="admin.php?page=watupro_cats&do=add"><?php _e('Click here', 'watupro')?></a> <?php _e('to create one.', 'watupro')?></p>
	<?php endif;?>
	
	<h2><?php printf(__('Shortcodes to list published %s', 'watupro'), __('quizzes', 'watupro'))?></h2>
	
	<p><?php printf(__('To list all published %s in the system you can use the shortcode', 'watupro'), __('quizzes', 'watupro'))?> <input type="text" value='[watuprolist cat_id="ALL"]' onclick="this.select();" readonly="readonly"> </p>
	<p><?php printf(__('To list all published uncategorized %s you can use the shortcode', 'watupro'), __('quizzes', 'watupro'))?> <input type="text" value='[watuprolist cat_id=0]' onclick="this.select();" readonly="readonly"></p>
	<p><?php printf(__('To list %s from only one category use the shortcodes given in the table above.', 'watupro'), __('quizzes', 'watupro'))?></p>
	<p><?php printf(__('To list %s from multiple categories with one shortcode just separate their IDs with commas, like this:', 'watupro'), __('quizzes', 'watupro'))?> <input type="text" value='[watuprolist cat_id="1,2"]' onclick="this.select();" readonly="readonly"></p>
	<p><?php printf(__('You can use the same logic to limit the %s shown in user dashboard. Just add category ID(s) to the shortcode like this:', 'watupro'), __('quizzes', 'watupro'))?> <b>[WATUPRO-MYEXAMS 1]</b> or <b>[WATUPRO-MYEXAMS 2,3,5]</b> <?php _e('To sort them by title, or latest on top, add "title" or "latest" to the shortcode like this:', 'watupro')?> <b>[WATUPRO-MYEXAMS 2,3,5 title]</b></p>
	<p><?php printf(__('These shortcodes also accept third argument that allows you to specify the order of listing %s:', 'watupro'), __('quizzes', 'watupro'));?></p>
	
	<ol>
		<li><b>title</b> <?php _e('to order them by title, alphabetically. Example:', 'watupro')?> <b>[WATUPROLIST ALL title]</b></li>
		<li><b>latest</b> <?php _e('to order the most recent on top. Example:', 'watupro')?> <b>[WATUPROLIST 1 latest]</b></li>
	</ol>
	
	<p><?php printf(__('By default all shortcodes list the %s sorted in the order of creation, oldest on top.', 'watupro'), __('quizzes', 'watupro'))?></p>
</div>