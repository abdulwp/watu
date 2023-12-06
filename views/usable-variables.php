<h3><?php _e('Usable Variables:', 'watupro') ?></strong> [<a href="#" onclick="jQuery('#usableVariables').toggle();return false;"><?php _e('show/hide', 'watupro')?></a>]</h3> 
<p><?php _e('(All the variables can be used in grade descriptions as well.)', 'watupro')?></p>
	<table id='usableVariables'>
	<tr><td colspan="2"><b><?php _e('Quiz Variables', 'watupro')?></b><hr></td></tr>
	<tr><th style="text-align:left;"><?php _e('Variable', 'watupro') ?></th><th style="text-align:left;"><?php _e('Explanation', 'watupro') ?></th></tr>
	<tr><td>%%CORRECT%%</td><td><?php _e('The number of correct answers (the old %%SCORE%% also works)', 'watupro') ?></td></tr>
	<tr><td>%%TOTAL%%</td><td><?php _e('Total number of questions', 'watupro') ?></td></tr>
	<tr><td>%%POINTS%%</td><td><?php printf(__('Total points collected. You can use the variable %s to show the points rounded without decimals.', 'watupro'), '%%POINTS-ROUNDED%%'); ?></td></tr>
	<tr><td>%%MAX-POINTS%%</td><td><?php _e('Maximum number of points that user could collect.', 'watupro') ?></td></tr>
	<tr><td>%%PERCENTAGE%%</td><td><?php _e('Correct answer percentage', 'watupro') ?></td></tr>
	<tr><td>%%PERCENTAGEOFMAX%%</td><td><?php _e('Percentage of points achieved vs. maximum possible points', 'watupro') ?></td></tr>
	<tr><td>%%GRADE%%</td><td><?php printf(__('The assigned grade after taking the %s - title and description together', 'watupro'), __('quiz', 'watupro')) ?>.</td></tr>
	<tr><td>%%GTITLE%%</td><td><?php _e('The assigned grade - title only', 'watupro') ?>.</td></tr>
	<tr><td>%%GDESC%%</td><td><?php _e('The assigned grade - description only', 'watupro') ?>.</td></tr>
	<?php if(empty($edit_mode)):?>
		<tr><td>%%RATING%%</td><td><?php _e("A generic rating of your performance - it could be 'Failed'(0-39%), 'Just Passed'(40%-50%), 'Satisfactory', 'Competent', 'Good', 'Excellent' and 'Unbeatable'(100%)", 'watupro') ?></td></tr>
	<?php endif;?>
	<tr><td>%%QUIZ_NAME%%</td><td><?php printf(__('The name of the %s', 'watupro'), __('quiz', 'watupro')) ?></td></tr>
	<tr><td>%%CERTIFICATE%%</td><td><?php printf(__('Outputs a link to printable certificate. Will be displayed only if certificate is assigned to the achieved grade and you have the user email. For troubleshooting check <a href="%s" target="_blank">this post</a>.', 'watupro'), 'http://blog.calendarscripts.info/when-the-%certificate-variable-in-watupro-shows-nothing/');?></td></tr>
	<?php if(empty($edit_mode)):?>
		<tr><td>%%UNRESOLVED%%</td><td><?php _e('Shows unresolved questions without showing which is the correct answer. Useful if you want to point user attention where they need to work more without exposing the correct results. Questions that are considered unresolved are unanswered ones or the questions where points collected are less or equal to 0.', 'watupro') ?></td></tr>
	<?php endif;?>	
	<tr><td>%%ANSWERS%%</td><td><?php if(empty($edit_mode)) _e('Displays the user answers along with correct/incorrect mark. Shows the same as the setting under "Correct answer display" but without any predefined text before it.', 'watupro');
	else _e('Displays table with user answers, points, and teacher comments', 'watupro')?></td></tr>
	<tr><td nowrap="true">%%ANSWERS-PAGINATED%%</td><td><?php _e('Same as %%ANSWERS%% but one question/answer is shown at a time with a numbered paginator at the bottom.', 'watupro');?></td></tr>
	<tr><td>%%CATGRADES%%</td><td><?php _e('Grades and stats per category in case you have defined such grades.', 'watupro') ?></td></tr>	
	<tr><td>%%DATE%%</td><td><?php printf(__('The date when the %s is completed (Date format comes from your Wordpress Settings page).', 'watupro'), __('quiz', 'watupro')); ?>.</td></tr>
	<tr><td>%%START-TIME%%</td><td><?php _e('The time when the quiz was started', 'watupro'); ?>.</td></tr>
	<tr><td>%%END-TIME%%</td><td><?php _e('The time when the quiz was completed', 'watupro'); ?>.</td></tr>
	<tr><td>%%TIME-SPENT%%</td><td><?php printf(__('The time spent to take the %s.', 'watupro'), __('quiz', 'watupro')); ?></td></tr>	
	<tr><td>%%AVG-POINTS%%</td><td><?php printf(__('Shows the average points achieved by others who took the same %s.', 'watupro'), __('quiz', 'watupro')); ?></td></tr>
	<tr><td>%%AVG-PERCENT%%</td><td><?php printf(__('Shows the average percent correct answer given by others who took the same %s.', 'watupro'), __('quiz', 'watupro')); ?></td></tr>
	<tr><td>%%BETTER-THAN%%</td><td><?php printf(__('Shows the percentage of users performed worse. It will compare by percent correct answers or by points depending on how the %s calculates grades - by points or percent correct answers.', 'watupro'), __('quiz', 'watupro')); ?></td></tr>
	<tr><td>%%ADMIN-URL%%</td><td><?php _e('Direct URL to view this submission in the administration. Do not use this variable in the email sent to user.', 'watupro'); ?></td></tr>
	<tr><td>%%TASK-SUPPORT%%</td><td><?php _e('This is to show the results related to the Task Support Categories', 'watupro'); ?></td></tr>
	<tr><td>%%TASK-INNOVATION%%</td><td><?php _e('This is to show the results related to the Task Innovation Categories', 'watupro'); ?></td></tr>
	<tr><td>%%SOCIAL-RELATIONSHIPS%%</td><td><?php _e('This is to show the results related to the Social Relationships Categories', 'watupro'); ?></td></tr>
	<tr><td>%%PERSONAL-FREEDOM%%</td><td><?php _e('This is to show the results related to the Personal Freedom Categories', 'watupro'); ?></td></tr>
	<tr><td colspan="2"><b><?php _e('User Info Variables', 'watupro')?></b><hr></td></tr>
	<tr><th style="text-align:left;"><?php _e('Variable', 'watupro') ?></th><th style="text-align:left;"><?php _e('Explanation', 'watupro') ?></th></tr>
	<tr><td>%%EMAIL%%</td><td><?php _e('User email address.', 'watupro') ?></td></tr>
	<tr><td>%%USER-NAME%%</td><td><?php _e('The logged in (or requested by a {{{name-field}}} tag, or "Ask for user contact details") user name. If empty, it will display "Guest"', 'watupro'); ?>.</td></tr>
	<tr><td>%%FIELD-COMPANY%%</td><td><?php _e('The value of the "Company" field from the optional "Ask user for contact details" section.', 'watupro'); ?>.</td></tr>
	<tr><td>%%FIELD-PHONE%%</td><td><?php _e('The value of the "Phone" field from the optional "Ask user for contact details" section.', 'watupro'); ?>.</td></tr>
	</table>