<div class="wrap">
	<h1><?php printf(__('Copy %s "%s"', 'watupro'), __('quiz', 'watupro'), $exam->name)?></h1>

	<p><a href="admin.php?page=watupro_exam&quiz=<?php echo $exam->ID?>&action=edit"><?php printf(__('Edit %s', 'watupro'), __('quiz', 'watupro'))?></a>
	| <a href="admin.php?page=watupro_questions&quiz=<?php echo $exam->ID?>"><?php _e('Manage questions', 'watupro')?></a></p>	

	<form method="post" action="#">
	<div id="copyExam" class="postbox">
		<div class="inside">
			<p><?php printf(__("This will copy the entire %s along with its grades and questions into another exam.", 'watupro'), __('quiz','watupro'))?></p>
			
			<p><input type="radio" name="copy_option" value="new" checked="true" onclick="jQuery('#otherExams').hide();"> <?php printf(__("Copy into a new %s. The %s will have the same name with '(Copy)' at the end. You can edit the exam, change its name, remove questions etc, just like with every other %s that you create.", 'watupro'), __('quiz', 'watupro'), __('quiz', 'watupro'), __('quiz', 'watupro'))?></p>
			
			<?php if(sizeof($other_exams)):?>
			<p><input type="radio" name="copy_option" value="exsiting" onclick="jQuery('#otherExams').show();"> <?php printf(__("Copy into existing %s. Selecting this will result in copying only the questions and grades. If you select to copy in the same quiz, this will create clones / duplicates of the selected questions and grades.", 'watupro'), __('quiz', 'watupro'))?> </p>		
			
			<div id="otherExams" style="display:none;"><?php printf(__("Select existing %s to copy questions to:", 'watupro'), __('quiz', 'watupro'))?> <select name="copy_to">
				<?php foreach($other_exams as $other_exam):?>
					<option value="<?php echo $other_exam->ID?>"><?php echo $other_exam->name;
					if($other_exam->ID == $exam->ID) echo " ".__('(This is the same quiz)', 'watupro');?></option>
				<?php endforeach;?>		
				</select></div>
			<?php endif;?>
			
			<p><input type="checkbox" name="copy_select" value="1" onclick="this.checked ? jQuery('#copySelection').show() : jQuery('#copySelection').hide();"> <?php _e('Select which questions and grades to copy', 'watupro')?></p>
			
			<div style="display:none;" id="copySelection">
				<h3><?php _e('Questions:', 'watupro')?></h3>
				<?php foreach($qcats as $qcat):?>
					<h4><input type="checkbox" checked onclick="this.checked ? jQuery('.watupro-qcat-<?php echo $qcat->ID?>').attr('checked', 'true') : jQuery('.watupro-qcat-<?php echo $qcat->ID?>').removeAttr('checked');"><?php printf(__("Category '%s'", 'watupro'), $qcat->name)?></h4>
					<?php foreach($questions as $question):
					   if($question->cat_id != $qcat->ID) continue;?>
						<div style="padding-left:15px;padding-bottom:5px;"><input type="checkbox" value="<?php echo $question->ID?>" name="question_ids[]" checked class="watupro-qcat-<?php echo $qcat->ID?>"> <?php echo $question->question?></div>
					<?php endforeach;
				endforeach;?>	
				<p>&nbsp;</p><hr>
				<h3><?php _e('Grades:', 'watupro')?></h3>
				<?php foreach($grades as $grade):?>
					<div><input type="checkbox" value="<?php echo $grade->ID?>" name="grade_ids[]" checked> <?php echo $grade->gtitle?></div>
				<?php endforeach;?>
			</div>
			
		<p align="center" class="submit"><input type="submit" name="copy_exam" value="<?php printf(__('OK, Copy This %s', 'watupro'), __('quiz', 'watupro'))?>" class="button-primary">
		<input type="button" value="<?php _e('Cancel', 'watupro');?>" onclick="window.location='admin.php?page=watupro_exam&quiz=<?php echo $exam->ID?>&action=edit';" class="button"></p>
		</div>
	</div>
	</form>

</div>