<?php 
// Don't forged this page is included from a function!
global $question_catids;
$single_page = $exam->single_page;
// force start if we are continuing on limited time exam     
if($exam->full_time_limit>0 and !empty($timer_warning) and empty($_POST['watupro_start_timer'])): echo "<p class='watupro-warning' id='timerRuns'>".$timer_warning."</p>"; endif;
if($exam->full_time_limit > 0):?>
	    <div id="timeNag" <?php if(!empty($_POST['watupro_start_timer'])):?>style="display:none;"<?php endif;?>>
		    <?php $button = $_exam->maybe_show_description($exam);
		    printf(__('This %s must be completed in %d minutes.', 'watupro'), __('quiz', 'watupro'), $exam->time_limit);
		    if(empty($button)):?> <a href="#" onclick="WatuPRO.InitializeTimer(<?php echo $exam->time_limit*60?>, <?php echo $exam->ID?>, 1);return false;"><?php printf(__('Click here to start the %s', 'watupro'), __('quiz', 'watupro'))?></a><?php endif;?>		   
	    </div>
	    <div id="timerDiv" <?php if(empty($_POST['watupro_start_timer'])):?>style="display:none;"<?php endif;?>><?php _e('Time left:', 'watupro')?> <?php echo $exam->time_limit*60;?></div>
<?php endif;?>
<?php echo watupro_onpage_css();?>
<script type="text/javascript" >
if(!window.jQuery) alert("<?php _e('jQuery is not properly loaded in the header. Your blog theme is probably missing the wp_head() call. Please contact your theme developer.', 'watupro');?>");
</script>    
<div <?php if($exam->time_limit>0 and empty($_POST['watupro_start_timer'])):?>style="display:none;"<?php endif;?> id="watupro_quiz" class="quiz-area <?php if($single_page) echo 'single-page-quiz'; ?>">
<p id="submittingExam<?php echo $exam->ID?>" style="display:none;text-align:center;"><img src="<?php echo plugins_url('watupro/img/loading.gif')?>"></p>

<?php $button = $_exam->maybe_show_description($exam, true);?>

<?php if(!count($all_question)):?>
	<p><?php _e('There are no questions to answer.', 'watupro');?>
	<?php if(is_user_logged_in() and $exam->require_login and (!empty($advanced_settings['dont_show_answered']) or !empty($advanced_settings['dont_show_correctly_answered']))):?>
		<?php _e('Looks like you have already answered them all!', 'watupro');?>
	<?php endif;?></p>
<?php return false; 
endif;?>

<form action="" method="post" class="quiz-form" id="quiz-<?php echo $exam_id?>" <?php if(!$exam->time_limit and $button):?>style="display:none;"<?php endif;?> enctype="multipart/form-data" <?php if(!empty($exam->no_ajax)):?>onsubmit="return WatuPRO.submitResult(this)"<?php endif;?>>
<?php
if($exam->email_taker and !is_user_logged_in()) watupro_ask_for_email($exam);
// the exam is shown below
$question_count = $cat_count = $page_count = $num_pages = 1;
$question_ids = '';
$total = sizeof($all_question);
if($exam->single_page == WATUPRO_PAGINATE_CUSTOM_NUMBER) {
	if($exam->custom_per_page == 0) $exam->custom_per_page = 1; // this should never be zero 	
	$num_pages = ceil( $total / $exam->custom_per_page );	
}

if($exam->show_pagination) echo WTPExam::paginator($total, @$in_progress);
if(!empty($advanced_settings['show_category_paginator'])) echo WTPExam :: category_paginator($all_question, $exam, @$in_progress);
if(!empty($advanced_settings['show_progress_bar'])) echo WatuPROExams :: progress_bar($all_question, $exam, @$in_progress);

$question_catids = array(); // used for category based pagination and category header
$qct = 0;
if(empty($exam->time_limit) or !empty($_POST['watupro_start_timer'])): // on timed exams questions should not be shown before the timer starts
	foreach ($all_question as $ques):        
	   echo watupro_cat_header($exam, $qct, $ques);
	   if($exam->single_page == WATUPRO_PAGINATE_CUSTOM_NUMBER) echo watupro_paginate_header($exam, $qct, $num_pages);
	   $qct++;
		echo "<div class='watu-question' id='question-$question_count'>";			 
			 if(!$single_page and $cnt_questions > 1) echo WatuPROExams :: show_qXofY($qct, $total, $advanced_settings, 'top');
			 echo $_question->display($ques, $qct, $question_count, @$in_progress, $exam);		 	
			 $question_ids .= $ques->ID.',';
		    if(!$single_page and $cnt_questions > 1) echo WatuPROExams :: show_qXofY($qct, $total, $advanced_settings); 
		    
		   if($exam->live_result or (!empty($advanced_settings['single_choice_action']) and $advanced_settings['single_choice_action']=='show')):
			   if(empty($_question->inprogress_snapshots[$ques->ID])):?>
					<div style="display:none;" id='liveResult-<?php echo $question_count?>'>		   
						<img src="<?php echo plugins_url('watupro/img/loading.gif')?>" width="16" height="16" alt="<?php _e('Loading...', 'watu', 'watupro')?>" title="<?php _e('Loading...', 'watu', 'watupro')?>" />&nbsp;<?php _e('Loading...', 'watu', 'watupro')?>
					</div>	
				<?php else: echo stripslashes($_question->inprogress_snapshots[$ques->ID]); endif; // end if displaying snapshot	
			endif; // end if live_result      
	   echo "</div>";
	   
	   // "live result" when the quiz is not paginated
	   if($exam->live_result and !$exam->single_page==WATUPRO_PAGINATE_ONE_PER_PAGE and empty($_question->inprogress_snapshots[$ques->ID])):?> 
	   	<p id="liveResBtn-<?php echo $ques->ID?>" style="clear:both;"><input type="button" id="liveResultBtn<?php echo $ques->ID?>" class="liveResultBtn<?php echo $qct?> watupro-live-result-btn" value="<?php _e('See Answer', 'watupro')?>" onclick="WatuPRO.liveResult(<?php echo $ques->ID?>, <?php echo $qct?>);"></p>
	   <?php endif;
	      
	   if(!in_array($ques->cat_id, $question_catids)) $question_catids[] = $ques->cat_id; 
	   $question_count++;        
	endforeach;
	if($single_page == WATUPRO_PAGINATE_PAGE_PER_CATEGORY and $exam->group_by_cat) echo "</div>"; // close last category div
	if($single_page == WATUPRO_PAGINATE_CUSTOM_NUMBER) echo "</div>"; // close last custom pagination div	
	$_exam->maybe_ask_for_contact($exam, 'end'); // maybe display div with contact details
endif; // end if hiding because of timer	
 
$num_cats = sizeof($question_catids);
if($exam->single_page == WATUPRO_PAGINATE_CUSTOM_NUMBER) $num_cats = $num_pages;
// show we hide the submit button? by default yes which means $submit_button_style is empty
$submit_button_style = '';
if(($exam->single_page == WATUPRO_PAGINATE_ONE_PER_PAGE and !$exam->submit_always_visible and sizeof($all_question)>1)
	or ( ($exam->single_page == WATUPRO_PAGINATE_PAGE_PER_CATEGORY or $exam->single_page == WATUPRO_PAGINATE_CUSTOM_NUMBER) 
			and !$exam->submit_always_visible and $num_cats>1)) $submit_button_style="style='display:none;'"; ?>
<div style='display:none' id='question-<?php echo $question_count?>'>
	<div class='question-content'>
		<img src="<?php echo plugins_url('watupro/img/loading.gif')?>" width="16" height="16" alt="<?php _e('Loading...', 'watu', 'watupro')?>" title="<?php _e('Loading...', 'watu', 'watupro')?>" />&nbsp;<?php _e('Loading...', 'watu', 'watupro')?>
	</div>
</div>

<?php
$question_ids = preg_replace('/,$/', '', $question_ids );
echo @$recaptcha_html;
echo @$text_captcha_html;?><br />
	
	<table class="watupro_buttons" id="watuPROButtons<?php echo $exam->ID?>"><tr>	
	<?php if(empty($exam->disallow_previous_button)):?>
		<td id="prev-question" style="display:none;"><input type="button" value="&lt; <?php _e('Previous', 'watupro') ?>" onclick="WatuPRO.nextQuestion(event, 'previous');"/></td>
	<?php else: // to prevent JS error just output empty hidden field?><input type="hidden" id="prev-question"><?php endif;?>
  <?php if($exam->single_page == WATUPRO_PAGINATE_ONE_PER_PAGE):?><td id="next-question"><input type="button" value="<?php _e('Next', 'watupro') ?> &gt;" onclick="WatuPRO.nextQuestion(event);" /></td><?php endif;?>
  <?php if($exam->live_result and $exam->single_page==WATUPRO_PAGINATE_ONE_PER_PAGE):?> <td><input type="button" id="liveResultBtn" value="<?php _e('See Answer', 'watupro')?>" onclick="WatuPRO.liveResult();"></td><?php endif;?>
  <?php if( ($single_page==WATUPRO_PAGINATE_PAGE_PER_CATEGORY and $num_cats>1 and $exam->group_by_cat)
  	or ($single_page == WATUPRO_PAGINATE_CUSTOM_NUMBER and $num_pages>1)):?>
  	<?php if(empty($exam->disallow_previous_button)):?><td style="display:none;" id="watuproPrevCatButton"><input type="button" onclick="WatuPRO.nextCategory(<?php echo $num_cats?>, false);" value="<?php _e('Previous page', 'watupro');?>"></td><?php endif;?><td id="watuproNextCatButton"><input type="button" onclick="WatuPRO.nextCategory(<?php echo $num_cats?>, true);" value="<?php _e('Next page', 'watupro');?>"></td> 
  <?php endif; // endif paginate per category ?>
  <?php if(($exam->single_page or $exam->store_progress == 0) and is_user_logged_in() and $exam->enable_save_button):?>
  	<td><input type="button" name="action" onclick="WatuPRO.saveResult(event)" id="save-button" value="<?php _e('Save', 'watupro') ?>" /></td>
  <?php endif;?>
	<td><?php if(empty($exam->no_ajax)):?><input type="button" name="action" onclick="WatuPRO.submitResult(event)" id="action-button" value="<?php _e('Submit', 'watupro') ?>" <?php echo $submit_button_style?> />
	<?php else:?>
		<input type="submit" name="submit_no_ajax" id="action-button" value="<?php _e('Submit', 'watupro') ?>" <?php echo $submit_button_style?>/>
	<?php endif;?></td>
	</tr></table>
	<input type="hidden" name="quiz_id" value="<?php echo  $exam_id ?>" />
	<input type="hidden" name="start_time" id="startTime" value="<?php echo current_time('mysql');?>" />
	<input type="hidden" name="question_ids" value="<?php echo @$qidstr?>" />
	<input type="hidden" name="watupro_questions" value="<?php echo watupro_serialize_questions($all_question);?>" />
	<input type="hidden" name="no_ajax" value="<?php echo $exam->no_ajax?>"><?php if(!empty($exam->no_ajax)):?>
	<input type="hidden" name="action" value="watupro_submit">
	<?php endif;?>
	<?php if(!empty($exam->no_ajax)  and !empty($advanced_settings['ask_for_contact_details']) and $advanced_settings['ask_for_contact_details'] == 'start'):
	// when no-ajax exam requires contact fields we have to place them here too?>	
	<input type="hidden" name="watupro_taker_email" value="<?php echo @$_POST['watupro_taker_email']?>">
	<input type="hidden" name="watupro_taker_name" value="<?php echo @$_POST['watupro_taker_name']?>">
	<input type="hidden" name="watupro_taker_phone" value="<?php echo @$_POST['watupro_taker_phone']?>">
	<input type="hidden" name="watupro_taker_company" value="<?php echo @$_POST['watupro_taker_company']?>">
	<?php endif;?>
	<?php if($exam->single_page==WATUPRO_PAGINATE_ONE_PER_PAGE and (!empty($advanced_settings['premature_end_question']) or !empty($advanced_settings['prevent_forward_question']))):?>
		<input type="hidden" id="watuproEvaluateOnTheFly<?php echo $exam->ID?>" value="1" name="evaluate_on_the_fly">
	<?php endif;
	if(!empty($advanced_settings['use_honeypot'])):?>		
		<input class="watupro-beehive" type="text" value="_<?php echo md5('honeyforme' . $_SERVER['REMOTE_ADDR']) /* honeypot value */?>" id="watuPROAppSourceID<?php echo $exam->ID?>">
		<input class="watupro-beehive" name="h_app_id" type="text" value="_<?php echo microtime() /* honeypot value */?>" id="watuPROAppID<?php echo $exam->ID?>">
	<?php endif;?>
	</form>
	<p>&nbsp;</p>
</div>

<?php if(!empty($exam->time_limit) and empty($_POST['watupro_start_timer'])): // start timer form?>
<form method="post" id="watuproTimerForm<?php echo $exam->ID?>">
	<!-- watupro-hidden-fields -->
	<input type="hidden" name="watupro_start_timer" value="0">
	<input type="hidden" name="watupro_taker_email" value="">
	<input type="hidden" name="watupro_taker_name" value="">
	<input type="hidden" name="watupro_taker_phone" value="">
	<input type="hidden" name="watupro_taker_company" value="">
	<input type="hidden" name="watupro_diff_level" value="">
</form>
<?php endif;?>
<script type="text/javascript">
jQuery(function(){
<?php do_action('watupro_show_exam_js', $exam);?>
var question_ids = "<?php print $question_ids ?>";
WatuPROSettings[<?php echo $exam->ID?>] = {};
WatuPRO.qArr = question_ids.split(',');
WatuPRO.exam_id = <?php echo $exam_id ?>;	    
WatuPRO.post_id = <?php echo $post->ID ?>;
WatuPRO.store_progress = <?php echo $exam->store_progress ?>;
WatuPRO.requiredIDs="<?php echo $required_ids_str?>".split(",");
WatuPRO.hAppID = "<?php echo microtime(); /* honeypot */ ?>";
var url = "<?php print plugins_url('watupro/'.basename(__FILE__) ) ?>";
WatuPRO.examMode = <?php echo $exam->single_page?>;
<?php if($single_page==2 and $num_cats>1 and $exam->group_by_cat): echo 'WatuPRO.numCats ='. $num_cats.";\n"; endif;?>
WatuPRO.siteURL="<?php echo admin_url( 'admin-ajax.php' ); ?>";
<?php if(!empty($advanced_settings['confirm_on_submit'])):?>WatuPRO.confirmOnSubmit = true;<?php echo "\n"; endif;
if(!empty($advanced_settings['dont_prompt_unanswered'])):?>WatuPRO.dontPromtUnanswered = true;<?php echo "\n"; endif;  
if(!empty($advanced_settings['dont_scroll'])):?>WatuPRO.dontScroll = true;<?php echo "\n"; endif;
if(!empty($advanced_settings['single_choice_action'])):?>WatuPROSettings[<?php echo $exam->ID?>].singleChoiceAction = <?php echo "'".$advanced_settings['single_choice_action']."'\n"; endif;  
if(!empty($advanced_settings['email_not_required'])):?>WatuPRO.emailIsNotRequired = 1;<?php echo "\n"; endif;
if(watupro_intel()):?>WatuPROIntel.init(<?php echo $exam->ID?>);<?php echo "\n";endif;
if(!empty($in_progress)): watupro_load_page($in_progress); endif;
if($exam->single_page != WATUPRO_PAGINATE_ONE_PER_PAGE):?>WatuPRO.inCategoryPages=1;<?php endif;
if($exam->time_limit > 0):?>
WatuPRO.secs=0;
WatuPRO.timerID = null;
WatuPRO.timerRunning = false;		
WatuPRO.delay = 1000;
<?php if(!empty($_POST['watupro_start_timer'])):
echo "WatuPRO.InitializeTimer(".(round($exam->time_limit*60)).",".$exam->ID.", 0);"; // auto-start timer
endif;
endif;?>});    	 
</script>