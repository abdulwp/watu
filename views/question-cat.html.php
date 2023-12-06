<div class="wrap">
	<?php if(empty($_GET['parent_id'])):?>
		<h1><?php printf(__('%s Question Category', 'watupro'), empty($_GET['id']) ? __('Add', 'watupro') : __('Edit watupro'))?></h1>
		<p><a href="admin.php?page=watupro_question_cats"><?php _e('Back to question categories', 'watupro')?></a></p>
	<?php else:?>
		<h1><?php printf(__('%s Subcategory Under "%s"', 'watupro'), empty($_GET['id']) ? __('Add', 'watupro') : __('Edit watupro'), apply_filters('watupro_qtranslate', stripslashes($parent->name)))?></h1>
		<p><a href="admin.php?page=watupro_question_cats&parent_id=<?php echo $_GET['parent_id']?>"><?php _e('Back to all subcategories', 'watupro')?></a></p>
	<?php endif;?>		

	<form method="post" id="watuPROQCatForm" onsubmit="return watuPROValidate(this);">
	<div >
		<p ><label><strong><?php _e('Category name:', 'watupro')?></strong></label>
		 <input type="text" name="name" size="30" value="<?php echo @$cat->name?>" class="i18n-multilingual">
		</p> 
		<p>
				<label><strong><?php _e('Category description:', 'watupro')?></strong></label><br>				
				<?php wp_editor(stripslashes(@$cat->description), 'description', array("editor_class" => 'i18n-multilingual'))?>
	
		<p><input type="checkbox" name="exclude_from_reports" value="1" <?php if(!empty($cat->exclude_from_reports)) echo 'checked'?>> <?php _e('Exclude from reports and result exports', 'watupro');?></p>
		<p>
			<input type="submit" value="<?php _e('Save category', 'watupro')?>" name="ok" class="button-primary">
	
	</div>
	</form>
	
</div>

<script type="text/javascript" >
function watuPROValidate(frm) {
	if(frm.name.value == '') {
		alert("<?php _e('Please enter category name', 'watupro')?>");
		frm.name.focus();
		return false;
	}
}	
</script>