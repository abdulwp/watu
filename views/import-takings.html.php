<div class="wrap watupro-wrap">
	<h1><?php _e("Import Quiz Results", 'watupro')?></h1>
	
	<h2><?php printf(__('%s:', 'watupro'), ucfirst(WATUPRO_QUIZ_WORD))?> <?php echo stripslashes($quiz->name)?></h2>
	
	<p><a href="admin.php?page=watupro_takings&exam_id=<?php echo $quiz->ID?>"><?php _e('Back to the results', 'watupro')?></a></p>
	
	<p><strong><?php printf(__('You can import new or updated results for a %1$s. <a href="%2$s" target="_blank">Learn more here</a>.', 'watupro'), WATUPRO_QUIZ_WORD, 'https://blog.calendarscripts.info/importing-results-entries-in-watu-pro');?></strong></p>
	
	<?php if(!empty($_POST['watupro_import'])):
	 // output error/success message ?>
	 	<h2 style="color:green;"><?php echo $result ? $message : __('There was an error importing your results.', 'watupro')?></h2>
	<?php
		if(!empty($non_utf8_error)):
			echo "<p style='color:red;'>".sprintf(__('The imported file was not UTF-8 encoded. If it contains non-English (non-ASCII) characters there may be problems with the imported questions. If you notice such problems you will have to delete your questions, <a href="%s" target="_blank">convert your file to UTF-8</a> and import it then.', 'watupro'),
				'https://www.ablebits.com/office-addins-blog/2014/04/24/convert-excel-csv/')."</p>";
		endif;
	endif;?>
	
	<form method="post" enctype="multipart/form-data">
		<div class="inside watupro">			
			<p><label><?php _e('Upload file:', 'watupro')?></label> <input type="file" name="csv" required><br />
			<b><?php _e('Note: if you are uploading file containing non-English characters you should make sure the file is in Unicode format (UTF-8 encoded).', 'watupro');?></b></p>			
			
            <p><label><?php _e('Fields Delimiter:', 'watupro')?></label> <select name="delimiter">
            <option value=","><?php _e('Comma', 'watupro')?></option>
            <option value="tab"><?php _e('Tab', 'watupro')?></option>
            <option value=";"><?php _e('Semicolon', 'watupro')?></option>
            </select></p>
            
            <p><?php _e('If you have problems importing files with foreign characters, please', 'watupro')?> <input type="checkbox" name="import_fails" value="1"> <?php _e('check this checkbox and try again.', 'watupro')?></p>
            
			<p><input type="checkbox" name="fire_hooks" value="1">  <?php printf(__('Fire <a href="%1$s" target="_blank">action hooks</a>. The hook %2$s or %3$s will be fired on each imported record.', 'watupro'), 'https://blog.calendarscripts.info/watupro-developers-api/', 'watupro_completed_exam', 'watupro_completed_exam_edited')?> </p>
			
			<p><input type="submit" name="watupro_import" value="<?php _e('Import Results', 'watupro')?>" class="button-primary"></p>
		</div>		
		<?php wp_nonce_field('watupro_import_takings');?>
	</form>
	

</div>
