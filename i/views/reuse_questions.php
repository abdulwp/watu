<form method="post">
	<p><input type="checkbox" name="reuse_questions" <?php if(!empty($reused_exams) and !empty($reused_exams[0])) echo "checked"?> onclick="watuProIReuseQuestions(this);" value="1"> <?php _e('Reuse questions from another quiz.', 'watupro')?> <select name="reuse_questions_from[]" multiple="true" size="3" id="watuProQuestionsReuseSelector" <?php if(empty($reused_exams) or empty($reused_exams[0])) echo "style='display:none;'"?>>
		<option value="0"><?php _e('- Please select -', 'watupro')?></option>
		<?php foreach($exams as $ex):?>
			<option value="<?php echo $ex->ID?>" <?php if(@in_array($ex->ID, $reused_exams)) echo "selected"?>><?php echo $ex->name . ' (ID '.$ex->ID.')'?></option>
		<?php endforeach;?>
	</select>
	&nbsp;
	<input type="submit" value="<?php _e('Save', 'watupro')?>"></p>
	<input type="hidden" name="save_reuse" value="1">
</form>

<script type="text/javascript" >
function watuProIReuseQuestions(chk) {
	if(chk.checked) {
		jQuery('#watuProQuestions').hide();
		jQuery('#watuProQuestionsReuseSelector').show();
	}
	else {
		jQuery('#watuProQuestions').show();
		jQuery('#watuProQuestionsReuseSelector').hide();
	}
}
</script>