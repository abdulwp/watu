<div class="wrap">
	<h1><?php printf(__('Manage %s Bundle', 'watupro'), __('Quiz', 'watupro'))?></h1>
	
	<p><a href="admin.php?page=watupro_bundles"><?php _e('Back to bundle buttons', 'watupro')?></a></p>
	
	<form method="post" class="watupro">
		<p><label><?php _e('Bundle type:', 'watupro')?></label> <select name="bundle_type" onchange="changeBundleType(this.value);">
			<option value="quizzes" <?php if(!empty($bundle) and $bundle->bundle_type == 'quizzes') echo 'selected'?>><?php printf(__('Selected %s', 'watupro'), __('quizzes', 'watupro'))?></option>		
			<option value="category" <?php if(!empty($bundle) and $bundle->bundle_type == 'category') echo 'selected'?>><?php printf(__('Category of %s', 'watupro'), __('quizzes', 'watupro'))?></option>
		</select></p>
		<p><label><?php _e('Price:', 'watupro')?></label> <input type="text" size="6" name="price" value="<?php echo @$bundle->price?>"> <?php echo $currency?></p>
		
		<p id="bundleQuizzes" style="display:<?php echo (empty($bundle) or $bundle->bundle_type == 'quizzes') ? 'block' : 'none';?>">
			<?php foreach($quizzes as $quiz):?>
				<input type="checkbox" name="quizzes[]" value="<?php echo $quiz->ID?>" <?php if(!empty($qids) and in_array($quiz->ID, $qids)) echo 'checked'?>>&nbsp;<?php echo stripslashes($quiz->name)?>&nbsp; 
			<?php endforeach;?>		
		</p>
		
		<p id="bundleCategory" style="display:<?php echo (!empty($bundle) and $bundle->bundle_type == 'category') ? 'block' : 'none';?>">
			<label><?php _e('Select category:', 'watupro')?></label> <select name="cat_id">
				<?php foreach($cats as $cat):?>
					<option value="<?php echo $cat->ID?>" <?php if(!empty($bundle->cat_id) and $bundle->cat_id == $cat->ID) echo 'selected'?>><?php echo stripslashes($cat->name)?></option>
				<?php endforeach;?>
			</select>
		</p>
		
		<p><label><?php _e('After payment redirect to:', 'watupro')?></label> <input type="text" name="redirect_url" value="<?php echo @$bundle->redirect_url?>" size="60"> <?php _e('Enter full URL where the user should go after payment. This can be for example the URL of the first quiz from the bundle.', 'watupro')?></p>
		
		<p><input type="submit" value="<?php _e('Save Bundle Button', 'watupro')?>"></p>
		<input type="hidden" name="ok" value="1">
	</form>
</div>

<script type="text/javascript" >
function changeBundleType(val) {
	jQuery('#bundleQuizzes').hide();
	jQuery('#bundleCategory').hide();
	
	if(val == 'quizzes') jQuery('#bundleQuizzes').show();
	else jQuery('#bundleCategory').show();
}
</script>