<div id="watuproContactDetails-<?php echo $exam->ID?>-<?php echo $position?>" style="display:<?php echo ($position == 'start') ? 'block' : 'none';?>;" class="watupro-ask-for-contact watupro-ask-for-contact-quiz-<?php echo $exam->ID?>">
	<?php if(!empty($advanced_settings['contact_fields']['intro_text'])) echo wpautop(stripslashes(base64_decode($advanced_settings['contact_fields']['intro_text'])));?>
	<?php if(!empty($advanced_settings['contact_fields']['email'])):?>
		<p><?php echo $advanced_settings['contact_fields']['email_label'];?> <br>	<input type="text" size="30" name="watupro_taker_email" id="watuproTakerEmail<?php echo $exam->ID?>" class="<?php if($advanced_settings['contact_fields']['email'] != 'required') {echo 'optional';} else {echo 'watupro-contact-required';}?> watupro-contact-field" value="<?php echo empty($_POST['watupro_taker_email'])? @$user_email : $_POST['watupro_taker_email']?>"></p>
	<?php endif;?>	
	<?php if(!empty($advanced_settings['contact_fields']['name'])):?>
		<p><?php echo $advanced_settings['contact_fields']['name_label'];?> <br> <input type="text" size="30" name="watupro_taker_name" id="watuproTakerName<?php echo $exam->ID?>" class="<?php if($advanced_settings['contact_fields']['name'] != 'required') {echo 'optional';} else {echo 'watupro-contact-required';}?> watupro-contact-field" value="<?php echo empty($_POST['watupro_taker_name'])? @$user_identity : $_POST['watupro_taker_name']?>"></p>
	<?php endif;?>	
	<?php if(!empty($advanced_settings['contact_fields']['phone'])):?>
		<p><?php echo $advanced_settings['contact_fields']['phone_label'];?> <br> <input type="text" size="30" name="watupro_taker_phone" id="watuproTakerPhone<?php echo $exam->ID?>" class="<?php if($advanced_settings['contact_fields']['phone'] != 'required') {echo 'optional';} else {echo 'watupro-contact-required';}?> watupro-contact-field" value="<?php echo @$_POST['watupro_taker_phone']?>"></p>
	<?php endif;?>	
	<?php if(!empty($advanced_settings['contact_fields']['company'])):?>
		<p><?php echo $advanced_settings['contact_fields']['company_label'];?> <br> <input type="text" size="30" name="watupro_taker_company" id="watuproTakerCompany<?php echo $exam->ID?>" class="<?php if($advanced_settings['contact_fields']['company'] != 'required') {echo ' optional';} else {echo ' watupro-contact-required';}?> watupro-contact-field" value="<?php echo @$_POST['watupro_taker_company']?>"></p>
	<?php endif;?></div>