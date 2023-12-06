<div class="wrap">
	<h1><?php echo empty($cat->ID)?__("Create New", 'watupro'):__("Edit", 'watupro')?> <?php _e("Category", 'watupro')?></h1>

	<form method="post" onsubmit="return validate(this);">
	<div class="postbox">
		<h3 class="hndle"><span>&nbsp;<?php _e('Category Name', 'watupro') ?></span></h3>
		
		<div class="inside">
		<input type='text' name='name' value='<?php echo stripslashes(@$cat->name); ?>' />
		</div>
	</div>

	<div class="postarea postbox">
	<h3 class="hndle">&nbsp;<?php _e('Accessible For:', 'watupro') ?> 
	<?php if($use_wp_roles) _e('(Wordpress User Roles)', 'watupro');
	else _e('(Watupro User Groups)', 'watupro')?></h3>
	<div class="inside">
	<?php if($use_wp_roles):?>
		<select name="ugroups[]" multiple size="5">
		<option value="" <?php if(empty($cat->ID) or $cat->ugroups=="||") echo "selected"?>><?php _e('- All Users -', 'watupro')?></option>
		<?php foreach($roles as $key=>$r):			
			if(!empty($cat->ID) and strstr($cat->ugroups, "|".$key."|")) $selected='selected';
			else $selected='';?>
			<option value="<?php echo $key?>" <?php echo $selected?>><?php echo $key?></option>
		<?php endforeach;?>
		</select>
	<?php else: // using watupro user groups?>
		<select name="ugroups[]" multiple size="5">
			<option value="" <?php if(empty($cat->ID) or $cat->ugroups=="||") echo "selected"?>><?php _e('- All Users -', 'watupro')?></option>
			<?php foreach($groups as $group):
			if(!empty($cat->ID) and strstr($cat->ugroups, "|".$group->ID."|")) $selected='selected';
			else $selected='';?>
				<option value="<?php echo $group->ID?>" <?php echo $selected;?>><?php echo $group->name;?></option>
			<?php endforeach;?>
		</select>
	<?php endif;?>
	
	<p class="note"><?php _e('Please note this restriction here is valid only for a specific exam category.', 'watupro')?></p>

	<p class="submit">	
	<input type="submit" name="ok" value="<?php _e('Save Category', 'watupro') ?>" style="font-weight: bold;" tabindex="4" class="button-primary" />
	<?php if(!empty($cat->ID)):?>
		<input type="button" value="<?php _e('Delete Category', 'watupro')?>" onclick="confirmDelete(this.form);" class="button">
	<?php endif;?>
	</p>
	</div>
	<input type="hidden" name="del" value="0">
	</form>
</div>

<script type="text/javascript">
function validate(frm)
{
	if(frm.name.value=='')
	{
		alert("<?php _e("Please enter title of this category", 'watupro')?>");
		frm.name.focus();
		return false;
	}

	return true;
}

function confirmDelete(frm)
{
	if(confirm("<?php _e('Are you sure?', 'watupro')?>"))
	{
		frm.del.value=1;
		frm.submit();
	}
}
</script>