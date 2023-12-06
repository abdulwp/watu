	<div class="inside">
			<h3><?php _e('Advanced Final Screen Settings', 'watupro') ?></h3>
			
			<p><input type="checkbox" name="confirm_on_submit" value="1" <?php if(!empty($advanced_settings['confirm_on_submit'])) echo 'checked'?>> <?php _e('Ask for confirmation when the "Submit" button is pressed.', 'watupro')?></p>		
			
			<p><input type="checkbox" name="no_checkmarks" value="1" <?php if(!empty($advanced_settings['no_checkmarks'])) echo 'checked'?>> <?php _e('Do not show correct / incorrect checkmarks at all.', 'watupro')?></p>		
			<p><input type="checkbox" name="no_checkmarks_unresolved" value="1" <?php if(!empty($advanced_settings['no_checkmarks_unresolved'])) echo 'checked'?>> <?php _e('Do not show correct / incorrect checkmarks on unresolved questions to avoid right answers being revealed.', 'watupro')?></p>	
			<?php if(watupro_intel()):?>
			<p><input type="checkbox" name="reveal_correct_gaps" value="1" <?php if(!empty($advanced_settings['reveal_correct_gaps'])) echo 'checked'?>> <?php _e('Reveal the correct answers on unanswered and wrongly answered fields in "Fill the gaps" questions.', 'watupro')?></p>		
			<?php endif;?>
			<p>&nbsp;</p>	
	</div>
			
	<div class="inside">
			<h3><?php _e('Advanced Workflow Settings', 'watupro') ?></h3>
			<p><input type="checkbox" name="dont_prompt_unanswered" value="1" <?php if(!empty($advanced_settings['dont_prompt_unanswered'])) echo 'checked'?>> <?php _e('Do not prompt the user when a non-required question is not answered.', 'watupro')?></p>		
			
			<?php if($exam->single_page==0):?>
				<p><input type="checkbox" name="dont_load_inprogress" value="1" <?php if(!empty($advanced_settings['dont_load_inprogress'])) echo 'checked'?>> <?php _e("Don't load the unfinished quiz when user comes back to continue (Normally the software would let the user continue from where they were).", 'watupro')?></p>		
				<p><input type="checkbox" name="dont_scroll" value="1" <?php if(!empty($advanced_settings['dont_scroll'])) echo 'checked'?>> <?php _e("Don't auto-scroll the screen when user moves from page to page (Auto-scrolling happens to ensure user always sees the top of the page).", 'watupro')?></p>	
			<?php endif;?>
			
			<p><?php _e('When user answers a "single choice" question:', 'watupro')?> <select name="single_choice_action">
				<option value=""><?php _e('Do nothing (default)', 'watupro')?></option>			
				<?php if($exam->single_page == WATUPRO_PAGINATE_ONE_PER_PAGE):?>
					<option value="next" <?php if(!empty($advanced_settings['single_choice_action']) and $advanced_settings['single_choice_action'] == 'next') echo 'selected'?>><?php _e('Go to next question', 'watupro')?></option>
				<?php endif;?>	
				<option value="show" <?php if(!empty($advanced_settings['single_choice_action']) and $advanced_settings['single_choice_action'] == 'show') echo 'selected'?>><?php _e('Show the answer', 'watupro')?></option>				
			</select><br>
			<i><?php _e('(By default nothing happens until the user clicks "Next", "Show answer", or other button.)', 'watupro')?></i></p>
			
			<p><input type="checkbox" name="dont_store_taking" <?php if(!empty($advanced_settings['dont_store_taking'])) echo 'checked'?>> <?php _e("Don't store any data / results of this quiz in the database.", 'watupro');?> <br>
			<?php _e("This setting will be useful in fun quizzes or quizzes for practicing in which you don't need history. Such quizzes will not send the 'watupro_completed_exam' call used by other plugins like WatuPRO Play to assign levels, badges, add to user's point balance etc.", 'watupro');?></p>				
				
			<p>&nbsp;</p>
	</div>		
	
	<div class="inside">		
			
			<h3><?php _e('Student Dashboard Settings', 'watupro') ?></h3>
			
			<p style="display:<?php echo empty($advanced_settings['show_only_snapshot']) ? 'block' : 'none';?>" id="showResultPoints"><input type="checkbox" name="show_result_and_points" value="1" <?php if(!empty($advanced_settings['show_result_and_points'])) echo 'checked'?>> <?php _e('Show results and points of every question in the table view (reveals the correct answer).', 'watupro')?></p>	
			
			<p><input type="checkbox" name="show_only_snapshot" value="1" <?php if(!empty($advanced_settings['show_only_snapshot'])) echo 'checked'?> onclick="this.checked ? jQuery('#showResultPoints').hide() : jQuery('#showResultPoints').show();"> <?php _e('Show only snapshot when user opens taken quiz details pop-up. Admins/teachers will still be able to get the table format and CSV download.', 'watupro')?></p>	
			
			<p>&nbsp;</p>
	</div>		
	
	<?php if(watupro_intel()):?>
	<div class="inside">			
			
			<h3><?php _e('Paginator Settings', 'watupro') ?></h3>
			
			<p><?php _e('This configuration takes effect for quizzes that use numbered pagination. For the colors below you can enter words like "red", "orange", etc, or HTML color value like "#FFCCAA".','watupro')?></p>
			
			<p><label><?php _e('Color of answered question number (defaults to green):','watupro')?></label> <input type="text" size="10" name="answered_paginator_color" value="<?php echo @$advanced_settings['answered_paginator_color']?>"></p>			
			<p><label><?php _e('Color of unanswered question number (defaults to red):','watupro')?></label> <input type="text" size="10" name="unanswered_paginator_color" value="<?php echo @$advanced_settings['unanswered_paginator_color']?>"></p>
			
			<p>&nbsp;</p>
	</div>
	<?php endif;?>
	
	<div class="inside">			
			
    		<h3 class="hndle"><span><?php _e('Advanced Question Randomization', 'watupro') ?></span></h3>
    		<?php if(!$exam->random_per_category):?>
			<p><b><?php _e('Randomization currenty not in effect.', 'watupro');?></b> <?php _e('You need to pull random questions per category on the main page for this to have any effect.', 'watupro')?></p>
		<?php endif?>
    		
    		<?php if($exam->pull_random and $exam->random_per_category):?>
	    		<p><?php printf(__('You have chosen to pull %d random questions per category. Here you can elaborate by selecting specific random number for every question category. If you do not want to include any questions from a given category you should enter "-1" for it. Leaving 0 in the field will actually pull the default %d questions of that category.', 'watupro'), $exam->pull_random, $exam->pull_random);?></p>
    		<?php endif;?>
    		
			<table cellpadding="8">
				<tr><th><?php _e('Order', 'watupro')?></th> <th><?php _e('Category', 'watupro')?></th> <th><?php _e('No. questions', 'watupro')?></th></tr>
				<?php foreach($qcats as $qcat):?>
					<tr><td><input type="text" size="3" name="qcat_order_<?php echo $qcat->ID?>" value="<?php echo $qcat->sort_order?>"></td><td><?php echo stripslashes(apply_filters('watupro_qtranslate', $qcat->name))?></td><td><input type="text" size="4" name="random_per_<?php echo $qcat->ID?>" value="<?php echo isset($advanced_settings['random_per_'.$qcat->ID]) ? $advanced_settings['random_per_'.$qcat->ID] : $exam->pull_random?>"></td></tr>
				<?php endforeach;?>
			</table>
			<p><?php _e('The "Order" field in the above table lets you specify the order categories appear when the questions are grouped by category.', 'watupro');?></p>
	</div>