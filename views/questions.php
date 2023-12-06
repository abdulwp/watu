<div class="wrap">
	<h1><?php _e("Manage Questions in ", 'watupro')?> <?php echo apply_filters('watupro_qtranslate', stripslashes($exam_name)); ?></h1>
	
	<?php watupro_display_alerts(); ?>
	
	<p><a href="admin.php?page=watupro_exams"><?php printf(__('Back to %s', 'watupro'), __('quizzes', 'watupro'));?></a> 
	&nbsp;|&nbsp;
	<a href="edit.php?page=watupro_exam&quiz=<?php echo $_GET['quiz']?>&action=edit"><?php printf(__('Edit this %s', 'watupro'), __('quiz', 'watupro'));?></a>
	&nbsp;|&nbsp;
	<a href="admin.php?page=watupro_grades&quiz=<?php echo $_GET['quiz']?>"><?php if(empty($exam->is_personality_quiz)) _e('Manage Grades', 'watupro');
	else  _e('Manage Personality Types', 'watupro');?></a>
	&nbsp;|&nbsp;
	<a href="admin.php?page=watupro_questions&export=1&exam_id=<?php echo $_GET['quiz']?>&noheader=1&copy=1"><?php _e('Export Questions', 'watupro')?></a>
	&nbsp;|&nbsp;
	<a href="admin.php?page=watupro_advanced_import&id=<?php echo $_GET['quiz']?>"><?php _e('Import questions', 'watupro')?></a>	
	&nbsp;|&nbsp;
	<a href="#" onclick="jQuery('#importQuestions').toggle();"><?php _e('Legacy import/export', 'watupro')?></a>	
	&nbsp;|&nbsp;
	<a href="admin.php?page=watupro_copy_exam&id=<?php echo $_GET['quiz']?>&comefrom=questions"><?php _e('Copy quiz and / or questions', 'watupro')?></a></p>
	
<p class="note"><?php printf(__('Note: The fields in the CSV export files are currently separated by <b>%s</b>. You can change this <a href="%s">at the Settings page</a>.', 'watupro'), ((get_option('watupro_csv_delim') == 'tab') ? __('TAB', 'watupro') : __('COMMA', 'watupro')), 'admin.php?page=watupro_options');?> </p>
	
<div id="importQuestions" style="display:none;padding:10px;" class="widefat">
		<p> <?php _e('Legacy exports:', 'watupro')?>
	<a href="admin.php?page=watupro_questions&export=1&exam_id=<?php echo $_GET['quiz']?>&noheader=1&copy=1&legacy=1"><?php _e('Export To Copy', 'watupro')?></a>
	&nbsp;|&nbsp;
	<a href="admin.php?page=watupro_questions&export=1&exam_id=<?php echo $_GET['quiz']?>&noheader=1&legacy=1"><?php _e('Export To Edit', 'watupro')?></a></p>
	
		<form method="post" enctype="multipart/form-data" onsubmit="return validateWatuproImportForm(this);" action="admin.php?page=watupro_questions&quiz=<?php echo $_REQUEST['quiz']?>&noheader=1">
		
			<h3><?php _e('What file are you importing?', 'watupro')?></h3>
			
			<div><input type="radio" name="file_type" value="new" checked="true" onclick="jQuery('#previousFileImport').hide();jQuery('#newFileImport').show();"> <?php _e("I'm importing new questions. (This is a file exported by clicking on 'Export to copy' or a file that you created yourself.)", 'watupro')?></div>
			<div id="newFileImport"><p><?php _e('In this case all questions and answers will be added as new. The format of the CSV file should be: <strong>Question; Answer Type: "radio", "checkbox", or "textarea", Order (leave "0" to auto-order the questions); Category (optional); Explanation/Feedback (optional); Required: "0" for not required, "1" for required question; Correct answer condition (any or all); Fill the gaps or sorting points (correct/incorrect); Sortable question values; Max selections allowed; Is inactive (0 or 1); Is survey (0 or 1); Elaborate asnwer feedback ("exact" or "boolean"). After that answers start like this: answer; points; answer; points;', 'watupro')?></p>
			<p><?php _e('Optional columns should still be present but their values can be empty.', 'watupro')?></p></strong>
			<p style="color:red;"><?php _e('For a working example just click on "Export to copy" link on this page. Of course you must have created at least one question in the quiz.', 'watupro')?></p></div>				
			
			
			<div><input type="radio" name="file_type" value="old" onclick="jQuery('#previousFileImport').show();jQuery('#newFileImport').hide();"> <?php _e("I'm importing a file with edited questions. (This file was created by clicking 'Export to edit' link)", 'watupro')?></div>
			<div id="previousFileImport"><p><?php _e('In this case we will assume you are keeping the same format that was exported. If you have added new questions or answers please make sure their ID columns contain "0". Questions or answers with unrecognized IDs will be ignored.', 'watupro')?></p></div>			
		</fieldset>	
	
		<p><label><?php _e('CSV File:', 'watupro')?></label> <input type="file" name="csv"></p>
		
		<p><label><?php _e('Fields Delimiter:', 'watupro')?></label> <select name="delimiter">
			<option value="tab"><?php _e('Tab', 'watupro')?></option>
			<option value=";"><?php _e('Semicolon', 'watupro')?></option>			
			<option value=","><?php _e('Comma', 'watupro')?></option>		
			</select></p>
		
		<p><input type="checkbox" name="skip_title_row" value="1" checked> <?php _e('Skip title row', 'watupro')?></p>		
		
		<p><?php _e('If you have problems importing files with foreign characters, please', 'watupro')?> <input type="checkbox" name="import_fails" value="1"> <?php _e('check this checkbox and try again.', 'watupro')?></p>
		
		<p><input type="submit" value="Import Questions" class="button-primary">
		<input type="button" value="Cancel" onclick="jQuery('#importQuestions').hide();" class="button"></p>
		<input type="hidden" name="watupro_import" value="1">
	</form>
</div>

	<p style="color:green;"><?php _e('To add this exam to your blog, insert the code ', 'watupro') ?> <b>[watupro <?php echo $_REQUEST['quiz'] ?>]</b> <?php _e('into any post or page.', 'watupro') ?></p>
	
	<?php $intelligence_display=""; // variable used to hide the div with own questions if required
	if(watupro_intel()):
	require_once(WATUPRO_PATH."/i/models/question.php");
	WatuPROIQuestion::reuse_questions($exam, $intelligence_display);
	endif;?>
	
<div id="watuProQuestions" <?php echo $intelligence_display;?>>
	<?php if(!empty($qcats) and sizeof($qcats)):?>
	<form method="get" action="admin.php">
		<input type="hidden" name="page" value="watupro_questions">
		<input type="hidden" name="quiz" value="<?php echo $exam->ID?>">
		<p><label><?php _e('Show questions from category:', 'watupro')?></label> <select name="filter_cat_id">
		<option value=""><?php _e('- All categories -', 'watupro')?></option>
		<?php foreach($qcats as $cat):?>
			<option value="<?php echo $cat->ID?>"<?php if(!empty($_GET['filter_cat_id']) and $_GET['filter_cat_id']==$cat->ID) echo ' selected'?>><?php echo $cat->name?></option>
			<?php foreach($cat->subs as $sub):?>
					<option value="<?php echo $sub->ID?>" <?php if(@$question->cat_id==$sub->ID) echo "selected"?>> - <?php echo stripslashes(apply_filters('watupro_qtranslate', $sub->name));?></option>
		<?php endforeach; 
		endforeach;?>
		<option value="-1"<?php if(!empty($_GET['filter_cat_id']) and $_GET['filter_cat_id']==-1) echo ' selected'?>><?php _e('Uncategorized', 'watupro')?></option>
		</select>
		&nbsp;
		<?php _e('and tagged as:', 'watupro')?> <input type="text" name="filter_tag" value="<?php echo @$_GET['filter_tag']?>">
		<?php if(!empty($difficulty_levels) and count($difficulty_levels)):?>
			&nbsp;
			<?php _e('with difficulty level:', 'watupro');?> <select name="filter_dlevel">
				<option value=""><?php _e('- Any level -', 'watupro');?></option>
				<?php foreach($difficulty_levels as $dlev):
					$selected = (!empty($_GET['filter_dlevel']) and $_GET['filter_dlevel'] == trim($dlev)) ? ' selected' : ''; ?>
					<option value="<?php echo trim($dlev);?>"<?php echo $selected;?>><?php echo trim($dlev);?></option>
				<?php endforeach;?>
			</select>
		<?php endif;?>	
		&nbsp;
		<?php _e('with ID (you can separate multiple IDs with comma):', 'watupro')?> <input type="text" name="filter_id" value="<?php echo @$_GET['filter_id']?>" size="6"> <input type="submit" value="<?php _e('Filter questions', 'watupro')?>" class="button-primary"></p>	
	</form>
	<?php endif;?>	

	<p><a href="admin.php?page=watupro_question&amp;action=new&amp;quiz=<?php echo $_GET['quiz'] ?>"><?php _e('Create New Question', 'watupro')?></a></p>
	
	<form method="post">
	<table class="widefat">
		<thead>
		<tr>
			<th scope="col"><input type="checkbox" onclick="WatuPROSelectAll(this);"></th>
			<th scope="col"><div style="text-align: center;"><a href="admin.php?page=watupro_questions&quiz=<?php echo $_GET['quiz']?>&fix_sort_order=1" title="<?php _e('Click here to automatically fix the order of questions.', 'watupro')?>">#</a></div></th>
			<th scope="col"><?php _e('ID', 'watupro') ?></th>
			<th scope="col"><?php _e('Question', 'watupro') ?></th>
			<th scope="col"><?php _e('Type', 'watupro') ?></th>
			<th scope="col"><?php _e('Category', 'watupro') ?></th>
			<?php if($low_memory_mode != 1):?><th scope="col"><?php _e('Number Of Answers', 'watupro') ?></th><?php endif;?>
			<?php if(!empty($advanced_settings['accept_rating'])):?>
				<th><?php _e('Avg. users rating', 'watupro');?></th>
			<?php endif;?>
			<th scope="col" colspan="3"><?php _e('Action', 'watupro') ?></th>
		</tr>
		</thead>
	
		<tbody id="the-list">
	<?php
	if (count($all_question)) :
		$bgcolor = '';		
		$question_count = 0;
		foreach($all_question as $question) :
			$question_count++;
			if($question->answer_type == 'sort') $question->answer_count = sizeof(explode(PHP_EOL, @$question->sorting_answers));
			$class = ('alternate' == @$class) ? '' : 'alternate';
			print "<tr id='question-{$question->ID}' class='$class'>\n"; ?>
			<th><input type="checkbox" name="qids[]" value="<?php echo $question->ID?>" class="qids" onclick="toggleMassDelete();"></th>
			<td scope="row" style="text-align: center;">
			<div style="float:left;<?php if(!empty($_POST['filter_cat_id'])) echo 'display:none;'?>">
				<?php if(($question_count+$offset)>1):?>
					<a href="admin.php?page=watupro_questions&quiz=<?php echo $_GET['quiz']?>&move=<?php echo $question->ID?>&dir=up"><img src="<?php echo plugins_url('watupro/img/arrow-up.png')?>" alt="<?php _e('Move Up', 'watupro')?>" border="0"></a>
				<?php else:?>&nbsp;<?php endif;?>
				<?php if(($question_count+$offset) < $num_questions):?>	
					<a href="admin.php?page=watupro_questions&quiz=<?php echo $_GET['quiz']?>&move=<?php echo $question->ID?>&dir=down"><img src="<?php echo plugins_url("watupro/img/arrow-down.png")?>" alt="<?php _e('Move Down', 'watupro')?>"></a>
				<?php else:?>&nbsp;<?php endif;?>
			</div>			
			<?php echo $question_count + $offset; ?></td>
			<td><?php echo $question->ID ?></td>
			<td><?php echo apply_filters('watupro_qtranslate', stripslashes($question->question)); ?></td>
			<td><?php switch($question->answer_type):
				case 'sorting': _e('Sorting', 'watupro'); break;
				case 'gaps': _e('Fill the gaps', 'watupro'); break;
				case 'textarea': _e('Open-end (essay)', 'watupro'); break;
				case 'checkbox': _e('Multiple choices', 'watupro'); break;			
				case 'sort': _e('Sort the values', 'watupro'); break;	
				case 'matrix': case 'nmatrix': _e('Match / Matrix', 'watupro'); break;
				case 'slider': _e('Slider', 'watupro'); break;
				case 'radio':
				default:
					if($question->truefalse) _e('True/False', 'watupro');
					else _e('Single choice', 'watupro');
				break;
			endswitch;
			if($question->is_inactive) echo " <font color='red'>".__('(Inactive)', 'watupro')."</font>";
			if($question->importance) echo "<p style='color:green;'><em>".__('(Important!)', 'watupro')."</em></p>";?></td>
			<td><?php echo $question->cat ? stripslashes(apply_filters('watupro_qtranslate', $question->cat)) : __("Uncategorized", 'watupro')?></td>
			<?php if($low_memory_mode != 1):?><td><?php if($question->answer_type != 'slider' and $question->answer_type != 'gaps') echo  $question->answer_count;
			else echo __('n/a', 'watupro');?></td><?php endif;?>
			<?php if(!empty($advanced_settings['accept_rating'])):?>
				<th><?php echo number_format_i18n($question->rating, 2)?></th>
			<?php endif;?>
			<td><a href='admin.php?page=watupro_question&amp;question=<?php echo $question->ID?>&amp;action=edit&amp;quiz=<?php echo $_GET['quiz']?>' class='edit'><?php _e('Edit', 'watupro'); ?></a></td>
			<td><a href='admin.php?page=watupro_questions&amp;action=delete&amp;question=<?php echo $question->ID?>&amp;quiz=<?php echo $_GET['quiz']?>' class='delete' onclick="return confirm('<?php echo addslashes(__("You are about to delete this question. This will delete the answers to this question. Press 'OK' to delete and 'Cancel' to stop.", 'watupro'))?>');"><?php _e('Delete', 'watupro')?></a></td>
			</tr>
	<?php endforeach;
		else: ?>
		<tr style='background-color: <?php echo @$bgcolor; ?>;'>
			<td colspan="4"><?php _e('No questions found.', 'watupro') ?></td>
		</tr>
	<?php endif;?>
		</tbody>
	</table>
	
	<p align="center"><?php if($offset > 0):?>
		<a href="admin.php?page=watupro_questions&quiz=<?php echo $exam->ID?>&filter_cat_id=<?php echo @$_GET['filter_cat_id']?>&filter_tag=<?php echo @$_GET['filter_tag']?>&filter_id=<?php echo @$_GET['filter_id']?>&filter_dlevel=<?php echo @$_GET['filter_dlevel']?>&offset=<?php echo $offset - $page_limit?>"><?php _e('Previous page', 'watupro')?></a>
	<?php endif;?>
		<?php if($num_questions > ($offset + $page_limit)):?>
			&nbsp; <a href="admin.php?page=watupro_questions&quiz=<?php echo $exam->ID?>&filter_cat_id=<?php echo @$_GET['filter_cat_id']?>&filter_tag=<?php echo @$_GET['filter_tag']?>&filter_id=<?php echo @$_GET['filter_id']?>&filter_dlevel=<?php echo @$_GET['filter_dlevel']?>&offset=<?php echo $offset + $page_limit?>"><?php _e('Next page', 'watupro')?></a>
		<?php endif;?>	
	</p>
	
	<p align="center" style="display:none;" id="massDeleteQuesions">
	<?php _e('Change category of selected questions to:', 'watupro');?> <select name="mass_cat_id">			
			<?php foreach($qcats as $cat):?>
				<option value="<?php echo $cat->ID?>" <?php if(@$question->cat_id==$cat->ID) echo "selected"?>><?php echo stripslashes($cat->name);?></option>
				<?php foreach($cat->subs as $sub):?>
					<option value="<?php echo $sub->ID?>" <?php if(@$question->cat_id==$sub->ID) echo "selected"?>> - <?php echo stripslashes($sub->name);?></option>
				<?php endforeach;?>
			<?php endforeach;?>
			</select>

 	<input type="submit" name="mass_change_category" value="<?php _e('Assign selected category', 'watupro')?>" class="button-primary">	
 	&nbsp;
	<input type="submit" name="mass_activate" onclick="if(!confirm('<?php _e('Are you sure?', 'watupro')?>')) return false;" value="<?php _e('Activate Selected', 'watupro')?>" class="button-primary">
	&nbsp;
	<input type="submit" name="mass_deactivate" onclick="if(!confirm('<?php _e('Are you sure?', 'watupro')?>')) return false;" value="<?php _e('Deactivate Selected', 'watupro')?>" class="button-primary">
	&nbsp;
	<input type="submit" name="mass_delete" onclick="if(!confirm('<?php _e('Are you sure?', 'watupro')?>')) return false;" value="<?php _e('Delete Selected', 'watupro')?>" class="button"></p>
	<?php if(!empty($_POST['filter_cat_id'])):?>
		<input type="hidden" name="filter_cat_id" value="<?php echo $_POST['filter_cat_id']?>">
	<?php endif;?>
	</form>
	
	<p><a href="admin.php?page=watupro_question&amp;action=new&amp;quiz=<?php echo $_GET['quiz'] ?>"><?php _e('Create New Question', 'watupro')?></a></p>
	
	<p><?php _e("Note: you can use the up/down arrows to reorder questions. This will take effect for quizzes whose questions are <b>not randomized</b>.", 'watupro');?></p>	
</div>

<h3><?php _e('Question Hints Settings:', 'watupro')?></h3>
<form method="post">
<p><input type="checkbox" name="enable_question_hints" <?php if(!empty($enable_question_hints)) echo 'checked'?> onclick="this.checked ? jQuery('#questionHints').show() : jQuery('#questionHints').hide();"> <?php _e('Enable question hints in this quiz.', 'watupro')?> &nbsp; <input type="submit" name="hints_settings" value="<?php _e('Save')?>" class="button-primary"></p>
<div id="questionHints" style="display:<?php echo empty($enable_question_hints) ? 'none' : 'block';?>">
	<p><?php _e('Question hints are optionally displayed to the quiz taker upon request. It is usually a good idea to limit the number of hints the user can see so they have some incentive to try taking the quiz without using all the hints.', 'watupro')?></p>
	<p><?php _e('Allow the user to view up to', 'watupro')?> <input type="text" size="4" name="hints_per_quiz" value="<?php echo @$hints_per_quiz?>"> <?php _e('total hints for the whole quiz (leave 0 for unlimited hints).', 'watupro')?></p> 
	<?php _e('Allow the user to view up to', 'watupro')?> <input type="text" size="4" name="hints_per_question" value="<?php echo @$hints_per_question?>"> <?php _e('hints per question - for questions that have more than one hint available (leave 0 for unlimited hints).', 'watupro')?>
</div>
</form>

<script type="text/javascript" >
function validateWatuproImportForm(frm) {
	if(frm.csv.value=="") {
		alert("<?php _e('Please select CSV file.', 'watupro')?>");
		frm.csv.focus();
		return false;
	}
}

function WatuPROSelectAll(chk) {
	if(chk.checked) {
		jQuery(".qids").attr('checked',true);
	}
	else {
		jQuery(".qids").removeAttr('checked');
	}
	
	toggleMassDelete();
}

// shows or hides the mass delete button
function toggleMassDelete() {
	var len = jQuery(".qids:checked").length;
	
	if(len) jQuery('#massDeleteQuesions').show();
	else jQuery('#massDeleteQuesions').hide();
}
</script>