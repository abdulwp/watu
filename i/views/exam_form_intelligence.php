<div class="postbox watupro-tab-div" id="intel" style="display:none;">        
    <div class="inside">
    		<h3><?php _e('Intelligence Module Settings', 'watupro') ?></h3>
			<label><?php _e('Set exam mode:', 'watupro')?></label> <select name="mode" onchange="this.value == 'practice' ? jQuery('#wtpPracticeExplain').show() : jQuery('#wtpPracticeExplain').hide();">
				<option value="live"<?php if(empty($dquiz->mode) or $dquiz->mode=='live') echo " selected"?>><?php _e('Live exam', 'watupro')?></option>		
				<option value="practice"<?php if(!empty($dquiz->mode) and $dquiz->mode=='practice') echo " selected"?>><?php _e('Practice mode', 'watupro')?></option>
			</select>	
			<span id="wtpPracticeExplain" style="display:<?php echo (!empty($dquiz->mode) and $dquiz->mode=='practice') ? 'inline' : 'none';?>">
				<a href="http://blog.calendarscripts.info/practice-mode-exams-in-watupro/" target="_blank"><?php _e("What's this?", 'watupro')?></a>
			</span>
			<br><br>
			
			<label><input type="checkbox" name="is_personality_quiz" value="1" <?php if(!empty($dquiz->is_personality_quiz)) echo 'checked'?>> <?php printf(__('Treat this quiz as a personality quiz: answers will be matched to results instead of calculating the points. <a href="%s" target="_blank">Learn more here</a>.', 'watupro'), 'http://blog.calendarscripts.info/personality-quizzes-in-watupro/');?></label>
			
			<br><br>
			<?php if(sizeof($other_exams)):?>
		   <h3><?php _e("Dependencies", 'watupro')?></h3>
		   
		   <div id="dependencyWarning" style="display:<?php echo (empty($dquiz->ID) or !$dquiz->require_login)?'block':'none'; ?>">
		   	<p><?php _e("This feature becomes active only when <strong>Require user log-in</strong> option in the <strong>User and Email Related Settings</strong> tab is selected.", 'watupro')?></p>
		   </div>
		   
		   <div id="dependencyDiv" style="display:<?php echo (empty($dquiz->ID) or !$dquiz->require_login)?'none':'block'; ?>">
				<p><?php printf(__("Let the user access this exam only after they have completed %s of the following ones:", 'watupro'), '<select name="dependency_type">
					<option value="all" '.((empty($advanced_settings['dependency_type']) or $advanced_settings['dependency_type']=='all') ? 'selected' : '').'>'.__('all', 'watupro').'</option>
					<option value="any" '.((!empty($advanced_settings['dependency_type']) and $advanced_settings['dependency_type']=='any') ? 'selected' : '').'>'.__('any', 'watupro').'</option>
				</select>')?></p>
				
				<?php foreach($dependencies as $dependency):?>
					<div id="oldDependencyRow<?php echo $dependency->ID?>">
						<?php _e("Exam:", 'watupro')?> <select name="dependency<?php echo $dependency->ID?>" class="watupro-depend-exam">
							<option value=""><?php _e("- Select exam -", 'watupro')?></option>			
							<?php foreach($other_exams as $oexam):?>
								<option value="<?php echo $oexam->ID?>"<?php if($oexam->ID==$dependency->depend_exam) echo " selected"?>><?php echo $oexam->name?></option>
							<?php endforeach;?>	
						</select> <?php _e("is completed with at least", 'watupro')?> <input type="text" name="depend_points<?php echo $dependency->ID?>" value="<?php echo $dependency->depend_points?>" onblur="WatuPRODep.forceNumber(this)" size="4"> <select name="depend_mode<?php echo $dependency->ID?>">
					<option value="points" <?php if(@$dependency->mode=='points') echo 'selected'?>><?php _e('Points', 'watupro')?></option>
					<option value="percent" <?php if(@$dependency->mode=='percent') echo 'selected'?>><?php _e('% correct answers', 'watupro')?></option>
				</select> <?php _e("achieved", 'watupro')?>.</span>
						<span class="submit" id="delDepBtn<?php echo $dependency->ID?>"><input type="button" value="<?php _e('Mark To Delete', 'watupro')?>" onclick="WatuPRODep.del(true, <?php echo $dependency->ID?>);"></span>
						
						<span class="submit" id="restoreDepBtn<?php echo $dependency->ID?>" style="display:none;"><input type="button" value="<?php _e('Restore', 'watupro')?>" onclick="WatuPRODep.del(false, <?php echo $dependency->ID?>);"></span>
					</div>
				<?php endforeach;?>
				
				<div id="dependencyRow"><span id="dependencySpan"><?php _e("Quiz:", 'watupro')?> <select name="dependencies[]" id="dependExam">
					<option value=""><?php _e("- Select exam -", 'watupro')?></option>			
					<?php foreach($other_exams as $oexam):?>
						<option value="<?php echo $oexam->ID?>"><?php echo $oexam->name?></option>
					<?php endforeach;?>	
				</select> <?php _e("is completed with at least", 'watupro')?> <input type="text" name="depend_points[]" value="0" onblur="WatuPRODep.forceNumber(this)" size="4" id="dependPoints"> <select name="depend_modes[]" id="dependMode">
					<option value="points"><?php _e('Points', 'watupro')?></option>
					<option value="percent"><?php _e('% correct answers', 'watupro')?></option>
				</select> <?php _e("achieved", 'watupro')?>.</span>
				<span class="submit"><input type="button" value="<?php _e('Add New Dependency', 'watupro')?>" id="addDependencyButton"></span>
				</div>	
				
				
		   </div>
		   <input type="hidden" id="delDependencies" name="del_dependencies" value="">
		   <?php endif;?>
		   
		   <!-- Payment settings -->
		   <h3><?php _e("Payment Settings", 'watupro')?></h3>
		   
		   <div id="WatuPROPaymentDiv">
					<p><?php _e("Charge", 'watupro')?> <?php echo get_option("watupro_currency")?> <input type="text" name="fee" size="6" value="<?php echo empty($dquiz->fee) ? 0 : $dquiz->fee?>"> <?php _e("for accessing this exam.", 'watupro')?>
					<input type="checkbox" name="pay_always" value="1" <?php if(!empty($dquiz->pay_always)) echo 'checked'?>> <?php _e('Mark payment as used after each quiz attempt (so if multiple attempts are allowed the user will have to pay each time. <b>This is always true for non-logged in users: they can take quiz only once with each payment.</b>)', 'watupro')?></p>
					<p><?php _e("If you leave zero in the box above, there will be no charge for users to access the exam. To manage your payments settings to to ", 'watupro')?> <a href="admin.php?page=watupro_options"><?php _e("WatuPRO Settings", 'watupro')?></a></p>
					<?php do_action('watupro_exam_payment_settings', @$dquiz);?>	   
		   </div>
		   
		   <?php if(count($editors) > 1 and $more_roles and current_user_can('manage_options')):?>
			   <!--  Quiz editor -->
			   <h3><?php _e('Quiz Owner', 'watupro');?></h3>
			   
			   <p><?php printf(__('This settings matters most if you select "Manage only exams created by users" on the <a href="%s" target="_blank">page that lets you fine-tune role settings</a>. Admin can always access all tests.', 'watupro'), 'http://localhost/wordpress/wp-admin/admin.php?page=watupro_multiuser');?></p>
			   
			   <p><?php _e('Choose owner:', 'watupro');?> <select name="editor_id">
			   	<?php foreach($editors as $editor):?>
			   		<option value="<?php echo $editor->ID?>" <?php if(!empty($dquiz->ID) and $dquiz->editor_id == $editor->ID) echo 'selected'?>><?php echo $editor->user_login;?></option>
			   	<?php endforeach;?>
			   </select></p>
			<?php endif;?>   
			
			<h3><?php _e('Runtime Configuration', 'watupro');?></h3>
			
			<p><?php _e('You can configure premature quiz ending or not allow the user to continue if they do not meet certain threshold of correct answers duing the quiz. <b>These settings are available only for quizzes paginated "each question on its own page"</b>.', 'watupro');?></p>
			
			<div id="WatuPROPrematureQuizEnd" style="display:<?php echo (empty($dquiz->ID) or $dquiz->single_page==WATUPRO_PAGINATE_ONE_PER_PAGE) ? 'block' : 'none';?>">
				<p><?php _e('Leave the below boxes empty (or enter zeros) to not configure any run time logic. If you configure any of these logics the setting "Automatically store user progress as they go from page to page" will automatically be set to true at the time of saving the quiz.', 'watupro');?></p>
				<h4><?php _e('Premature quiz ending','watupro');?></h4>
				<?php printf(__('Finalize the quiz if user answers correctly less than %s%% of the questions. Start performing this check after question number %s (min 1).', 'watupro'),
					'<input type="text" name="premature_end_percent" value="'.(empty($advanced_settings['premature_end_percent']) ? 0 : $advanced_settings['premature_end_percent']).'" size="3" maxlength="3">',
					'<input type="text" name="premature_end_question" value="'.(empty($advanced_settings['premature_end_question']) ? 0 : $advanced_settings['premature_end_question']).'" size="4" maxlength="4">');?>
					
				<p><?php _e('In case of premature quiz end, display this message on the "Final screen" before the other content:', 'watupro');?></p>
				<p><?php wp_editor(empty($advanced_settings['premature_text']) ? '' : stripslashes(base64_decode($advanced_settings['premature_text'])), 'premature_text');?></p>	
			</div>		
				
			<div id="WatuPROPreventForward" style="display:<?php echo (empty($dquiz->disallow_previous_button) and (empty($dquiz->ID) or $dquiz->single_page==WATUPRO_PAGINATE_ONE_PER_PAGE)) ? 'block' : 'none';?>">	
				<h4><?php _e('Prevent moving forward','watupro');?></h4>	
				<?php printf(__('Do not allow the user to submit or continue the quiz if they answer correctly less than %s%% of the questions. Start performing this check after question number %s (min 1).', 'watupro'),
					'<input type="text" name="prevent_forward_percent" value="'.(empty($advanced_settings['prevent_forward_percent']) ? 0 : $advanced_settings['prevent_forward_percent']).'" size="3" maxlength="3">',
					'<input type="text" name="prevent_forward_question" value="'.(empty($advanced_settings['prevent_forward_question']) ? 0 : $advanced_settings['prevent_forward_question']).'" size="4" maxlength="4">');?>
			</div>		
    </div>
</div>  

<script type="text/javascript" >
jQuery(function(){
	<?php if(sizeof($other_exams)):?>
		// store the clean dependency row in a var from the beginnign
		var dependencySpan = jQuery('#dependencySpan').html();
		var rowNum = 0;
		WatuPRODep.depsToDel = [];
	
		jQuery('#requieLoginChk').bind('click', function(){			
			if(jQuery(this).attr('checked')) {
				jQuery('#dependencyWarning').hide();
				jQuery('#WatuPROPaymentWarning').hide();
				jQuery('#dependencyDiv').show();
				jQuery('#WatuPROPaymentDiv').show();
			}
			else {
				jQuery('#dependencyWarning').show();
				jQuery('#WatuPROPaymentWarning').show();
				jQuery('#dependencyDiv').hide();
				jQuery('#WatuPROPaymentDiv').hide();
			}	
		});
		
		jQuery('#addDependencyButton').bind('click', function(){
			if(jQuery('#dependExam').val()=='') {
				alert("<?php _e('Please select exam', 'watupro')?>");
				return false;
			}		
			
			// check for duplicate
			var hasDuplicate = false;
			jQuery('.watupro-depend-exam option:selected').each(function(){
				if(this.value == jQuery('#dependExam').val())
				{
					hasDuplicate = true;
				}
			});	
			
			if(hasDuplicate) {
				alert("<?php _e('You already have this dependency!', 'watupro')?>");
				return false;
			}
			
			// add new row
			rowNum++;
			
			// replace the IDs
			html = dependencySpan.replace('dependExam', 'dependExam'+rowNum);			
			html = html.replace('dependPoints', 'dependPoints'+rowNum);
			html = html.replace('dependMode', 'dependMode'+rowNum);
			
			// button
			var but = "<span class='submit'><input type='button' value='<?php _e("Delete", 'watupro')?>' onclick=\"jQuery('#dependencyRow"+rowNum+"').remove();\"></span>";
			
			jQuery('#dependencyDiv').append("<div id='dependencyRow" + rowNum + "'>" + html + but + "</div>");
			
			// set values
			jQuery('#dependExam'+rowNum).val(jQuery('#dependExam').val());
			jQuery('#dependPoints'+rowNum).val(jQuery('#dependPoints').val());
			jQuery('#dependMode'+rowNum).val(jQuery('#dependMode').val());
			
			// add class used for duplicate dependency check
			jQuery('#dependExam'+rowNum).addClass("watupro-depend-exam");
			
			jQuery('#dependExam').val('');
			jQuery('#dependPoints').val('0');
		});
		
		// mark or restore dependency for deletion
		WatuPRODep.del = function(mode, id)
		{
			if(mode) {
				// to delete
				WatuPRODep.depsToDel.push(id);
				jQuery('#delDependencies').val(WatuPRODep.depsToDel.join(","));
				jQuery('#oldDependencyRow'+id).addClass('watupro-for-removal');
				jQuery('#delDepBtn'+id).hide();
				jQuery('#restoreDepBtn'+id).show();
			}
			else {
				// to restore
				WatuPRODep.depsToDel = jQuery.grep(WatuPRODep.depsToDel, function(value){
						return value != id;
					});
				jQuery('#delDependencies').val(WatuPRODep.depsToDel.join(","));
				jQuery('#oldDependencyRow'+id).removeClass('watupro-for-removal');				
				jQuery('#delDepBtn'+id).show();
				jQuery('#restoreDepBtn'+id).hide();
			}
		}
	<?php endif;?>
});

function watuPROChangePagination_i(val) {
		if(val == '<?php echo WATUPRO_PAGINATE_ONE_PER_PAGE?>') {
			jQuery('#WatuPROPrematureQuizEnd').show();
			if(jQuery('#examForm input[name=disallow_previous_button]').is(':checked')) jQuery('#WatuPROPreventForward').hide();
			else jQuery('#WatuPROPreventForward').show();
		}
		else {
			jQuery('#WatuPROPrematureQuizEnd').hide();
			jQuery('#WatuPROPreventForward').hide();
		}
} // end watuPROChangePagination_i
</script>