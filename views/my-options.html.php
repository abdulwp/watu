<<<<<<< HEAD
<div class="wrap">
	<h1><?php printf(__('My %s Settings', 'watupro'), __('Quiz', 'watupro'))?></h1>
	
	<form method="post">
		<p><input type="checkbox" name="no_quiz_mails" value="1" <?php if(get_user_meta($user_ID, 'watupro_no_quiz_mails', true)) echo 'checked'?>> <?php printf(__('Do not send me emails about completed %s.', 'watupro'), __('quizzes', 'watupro'))?></p>	
	
		<p><input type="submit" name="ok" value="<?php _e('Save Settings', 'watupro')?>"></p>
=======
<div class="wrap watupro-wrap">
	<h1><?php printf(__('My %s Settings', 'watupro'), __('Quiz', 'watupro'))?></h1>
	
	<form method="post">
		<p><input type="checkbox" name="no_quiz_mails" value="1" <?php if(get_user_meta($user_ID, 'watupro_no_quiz_mails', true)) echo 'checked'?>> <?php printf(__('Do not send me emails about completed %s.', 'watupro'), WATUPRO_QUIZ_WORD_PLURAL)?></p>	
	
		<p><input type="submit" name="ok" value="<?php _e('Save Settings', 'watupro')?>" class="button button-primary"></p>
>>>>>>> branch/6.7.2
	</form>
</div>