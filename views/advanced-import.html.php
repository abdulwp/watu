<div class="wrap">
	<h1><?php _e("Advanced Questions Import", 'watupro')?></h1>
	
	<h2><?php printf(__('%s:', 'watupro'), __('Quiz', 'watupro'))?> <?php echo $quiz->name?></h2>
	
	<p><a href="admin.php?page=watupro_questions&quiz=<?php echo $quiz->ID?>"><?php _e('Back to manage questions', 'watupro')?></a></p>
	
	<?php if(!empty($_POST['watupro_import'])):
	 // output error/success message ?>
	 	<h2 style="color:green;"><?php echo $result ? __('The questions were imported.', 'watupro') : __('There was an error importing your questions.', 'watupro')?></h2>
	<?php endif;?>
	
	<form method="post" enctype="multipart/form-data">
		<div class="inside watupro">
			<p><label><?php _e('Import format:', 'watupro')?></label> <select name="import_format" onchange="this.value == 'aiken' ? jQuery('#csvOptions').hide() : jQuery('#csvOptions').show();">
				<option value="simple" <?php if(!empty($_POST['import_format']) and $_POST['import_format']=='simple') echo 'selected'?>><?php _e('Simple WatuPRO', 'watupro')?></option>
				<option value="advanced" <?php if(!empty($_POST['import_format']) and $_POST['import_format']=='advanced') echo 'selected'?>><?php _e('Advanced WatuPRO', 'watupro')?></option>
				<option value="aiken" <?php if(!empty($_POST['import_format']) and $_POST['import_format']=='aiken') echo 'selected'?>><?php _e('Aiken (text file)', 'watupro')?></option>			
			</select></p>
			<p><label><?php _e('Upload file:', 'watupro')?></label> <input type="file" name="csv"><br />
			<b><?php _e('Note: if you are uploading file containing non-English characters you should make sure the file is in Unicode format (UTF-8 encoded).', 'watupro');?></b></p>
			
			<div id="csvOptions" style="display:<?php echo (!empty($_POST['import_format']) and $_POST['import_format'] == 'aiken') ? 'none' : 'block'?>">
				<p><label><?php _e('Fields Delimiter:', 'watupro')?></label> <select name="delimiter">
				<option value=","><?php _e('Comma', 'watupro')?></option>
				<option value="tab"><?php _e('Tab', 'watupro')?></option>
				<option value=";"><?php _e('Semicolon', 'watupro')?></option>
				</select></p>
				<p><input type="checkbox" name="skip_title_row" value="1" checked> <?php _e('Skip title row', 'watupro')?></p>		
				<p><?php _e('If you have problems importing files with foreign characters, please', 'watupro')?> <input type="checkbox" name="import_fails" value="1"> <?php _e('check this checkbox and try again.', 'watupro')?></p></div>
			<p><input type="submit" name="watupro_import" value="<?php _e('Import Questions', 'watupro')?>" class="button-primary"></p>
		</div>		
	</form>
	
	<hr>
	<h2><?php _e('About The Import Formats', 'watupro')?></h2>
	
	<p><?php _e('Please visit the following links to learn about each of the formats, download samples and see which is the best for you', 'watupro')?></p>
	
	<ul>
		<li><a href="http://blog.calendarscripts.info/new-imports-from-watupro-4-2-3/#simple" target="_blank"><?php _e('Simple WatuPRO', 'watupro')?></a> <?php _e('- simple CSV format with only a few important fields', 'watupro')?> </li>
		<li><a href="http://blog.calendarscripts.info/new-imports-from-watupro-4-2-3/#advanced" target="_blank"><?php _e('Advanced WatuPRO', 'watupro')?></a> <?php _e('- CSV format including all DB fields', 'watupro')?> </li>
		<li><a href="http://blog.calendarscripts.info/new-imports-from-watupro-4-2-3/#aiken" target="_blank"><?php _e('Aiken (text file)', 'watupro')?></a> <?php _e('The Aiken format', 'watupro')?> </li>		
	</ul>
</div>