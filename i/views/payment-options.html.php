	<div class="postbox">
		<h3 class="hndle"><span><?php printf(__('Payment Settings For %s That Require Payment', 'watupro'), __('Quizzes', 'watupro')) ?></span></h3>
		<div class="inside">
			<p><strong><?php printf(__("WatuPRO Intelligence module allows you to require payment to access selected %s.", 'watupro'), __('quizzes', 'watupro'))?></strong></p>
			<p><?php printf(__('If the existing payment methods are not sufficient you can get access to a lot more via the free <a href="%s" target="_blank">WooCommerce</a> plugin and our <a href="%s" target="_blank">free bridge</a>. If you choose this route you will be selling access to quizzes as products in your WooCommerce store.', 'watupro'), 'http://www.woothemes.com/woocommerce/', 'http://blog.calendarscripts.info/woocommerce-bridge-in-watupro/')?></p>
			<p><input type="checkbox" name="nodisplay_paid_quizzes" value="1" <?php if(get_option('watupro_nodisplay_paid_quizzes')) echo 'checked'?>> <?php printf(__('Do not display these %s in user dashboard until access to them is purchased (Users with admin rights will always see them).', 'watupro'), __('quizzes', 'watupro'))?></p>
			
			<p><label><?php _e("Payment currency:", 'watupro');?></label>
			<select name="currency" onchange="WatuPROChangeCurrency(this.value);">
			<?php foreach($currencies as $key=>$val):
            if($key==$currency) $selected='selected';
            else $selected='';?>
        		<option <?php echo $selected?> value='<?php echo $key?>'><?php echo $val?></option>
         <?php endforeach; ?>
			 <option value="" <?php if(!in_array($currency, $currency_keys)) echo 'selected'?>><?php _e('Custom', 'watupro')?></option>
				</select> <input type="text" id="customCurrency" name="custom_currency" style="display:<?php echo in_array($currency, $currency_keys) ? 'none' : 'inline';?>" value="<?php echo $currency?>"></p>
			
			<p><label><?php _e("Paypal Email ID:", 'watupro')?></label>
			<input type="text" name="paypal" value="<?php echo get_option('watupro_paypal');?>"> - <?php _e("If you provide this, a Paypal payment button will be generated and automatically instant payment notification will enable the user's access to the exam they have paid for.", 'watupro')?></p>
			
			<p><input type="checkbox" name="paypal_sandbox" <?php if(get_option('watupro_paypal_sandbox') == 1) echo 'checked'?> value="1"> <?php _e('Use Paypal in sandbox mode', 'watupro');?></p>
			
			<p><b><?php _e('Note: Paypal IPN will not work if your site is behind a "htaccess" login box or running on localhost. Your site must be accessible from the internet for the IPN to work. In cases when IPN cannot work you need to use Paypal PDT.', 'watupro')?></b></p>
			
			<p><input type="checkbox" name="use_pdt" value="1" <?php if($use_pdt == 1) echo 'checked'?> onclick="this.checked ? jQuery('#paypalPDTToken').show() : jQuery('#paypalPDTToken').hide();"> <?php printf(__('Use Paypal PDT instead of IPN (<a href="%s" target="_blank">Why and how</a>)', 'watupro'), 'http://blog.calendarscripts.info/watupro-intelligence-module-using-paypal-data-transfer-pdt-instead-of-ipn/');?></p>
			
			<div id="paypalPDTToken" style="display:<?php echo ($use_pdt == 1) ? 'block' : 'none';?>">
				<p><label><?php _e('Paypal PDT Token:', 'watupro');?></label> <input type="text" name="pdt_token" value="<?php echo get_option('watupro_pdt_token');?>" size="60"></p>
			</div>
			
			<p><input type="checkbox" name="accept_stripe" <?php if($accept_stripe) echo 'checked';?> value="1" onclick="this.checked ? jQuery('#watuproStripe').show() : jQuery('#watuproStripe').hide();"> <?php _e('Accept Stripe payments', 'watupro')?></p>
			
			<div id="watuproStripe" style="display:<?php echo $accept_stripe ? 'block' : 'none';?>">
				<p><label><?php _e('Your Public Key:', 'watupro')?></label> <input type="text" name="stripe_public" value="<?php echo get_option('watupro_stripe_public')?>"></p>
				<p><label><?php _e('Your Secret Key:', 'watupro')?></label> <input type="text" name="stripe_secret" value="<?php echo get_option('watupro_stripe_secret')?>"></p>
			</div>
			
			<?php do_action('watupro-view-payment-options');?>
			
			<label><b><?php _e("Other payment instructions or button code (optional):", 'watupro');?></b></label><br>
			
			<textarea name="other_payments" rows="7" cols="50" class="i18n-multilingual"><?php echo stripslashes($other_payments);?></textarea>
			
			<p><?php _e("Use this if you don't want to use Paypal or as additional manual or automated payment method. You can either textual instructions here, insert a link, or even payment button HTML code from a different payment system like 2Checkout.com, Authorize.net etc. ", 'watupro')?></p>
			<p><?php _e("In this case you can use the following shortcodes: <strong>[AMOUNT], [USER_ID], [EXAM_TITLE], and [EXAM_ID]</strong>.", 'watupro')?><br>
			<?php _e('When rendering bundle buttons the shortcodes [EXAM_TITLE] and [EXAM_ID] will be replaced by a bundle title and bundle ID.', 'watupro');?></p>	
			
			<?php if(!empty($payment_errors)):?>
				<p><a href="#" onclick="jQuery('#watuProPaymentErrors').toggle();return false;"><?php _e('View payments error log.','watupro')?></a></p>
				<div id="watuProPaymentErrors" style="display:none;"><?php echo nl2br($payment_errors)?></div>
			<?php endif;?>	
			
			<p><a href="admin.php?page=watupro_bundles"><?php _e('Create payment buttons for selling bundles of quizzes', 'watupro')?></a></p>	
			<p><a href="admin.php?page=watupro_coupons"><?php _e('Manage discount codes.', 'watupro')?></a></p>
		</div>
	</div>
	
<script type="text/javascript" >
function WatuPROChangeCurrency(val) {
	if(val) {
		jQuery('#customCurrency').hide();		
	}
	else {
		jQuery('#customCurrency').show();		
	}
}
</script>