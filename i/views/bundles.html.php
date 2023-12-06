<div class="wrap">
	<h1><?php _e('Manage Paid Quiz Bundles', 'watupro')?></h1>
	
	<p><?php printf(__('You can create payment buttons for selling access to several paid quizzes or a whole category of quizzes at once. Learn more how this works <a href="#" target="_blank">here</a>.', 'watupro'), 'TBD')?></p>
	
	<p><a href="admin.php?page=watupro_bundles&do=add"><?php _e('Create new bundle', 'watupro')?></a></p>
	
	<?php if(sizeof($bundles)):?>
		<h2><?php _e('Existing bundle buttons', 'watupro')?></h2>
		
		<table class="widefat">
			<tr><th><?php _e('Shortcodes', 'watupro')?></th><th><?php _e('Bundle type', 'watupro')?></th>
			<th><?php _e('Price', 'watupro')?></th>
			<th><?php _e('Gives access to', 'watupro')?></th>
			<th><?php _e('View Payments', 'watupro')?></th><th><?php _e('Edit/delete', 'watupro')?></th></tr>
			<?php foreach($bundles as $bundle):
				$class = ('alternate' == @$class) ? '' : 'alternate';?>
				<tr class="<?php echo $class?>">
					<td><?php _e('Paypal button:', 'watupro')?> <input type="text" value='[watupro-quiz-bundle mode="paypal" id="<?php echo $bundle->ID?>"]' onclick="this.select();" readonly="readonly" size="40">
					<?php if($accept_stripe):?>
						<br><?php _e('Stripe button:', 'watupro')?> <input type="text" value='[watupro-quiz-bundle mode="stripe" id="<?php echo $bundle->ID?>"]' onclick="this.select();" readonly="readonly" size="40">
					<?php endif;?>
					<?php if($accept_points):?>
						<br><?php _e('Pay with points:', 'watupro')?> <input type="text" value='[watupro-quiz-bundle mode="paypoints" id="<?php echo $bundle->ID?>"]' onclick="this.select();" readonly="readonly" size="40">
					<?php endif;?>
					<?php if(!empty($other_payments)):?>
						<br><?php _e('Other payment methods:', 'watupro')?> <input type="text" value='[watupro-quiz-bundle mode="custom" id="<?php echo $bundle->ID?>"]' onclick="this.select();" readonly="readonly" size="40">
					<?php endif;?></td>
					<td><?php echo ($bundle->bundle_type == 'quizzes') ? sprintf(__('Selected %s', 'watupro'), __('quizzes', 'watupro')) : 
						sprintf(__('%s category', 'watupro'), __('quiz', 'watupro'))?></td>
					<td><?php printf(__('%s %s', 'watupro'), $currency, $bundle->price);?></td>	
					<td><?php echo ($bundle->bundle_type == 'quizzes') ? stripslashes($bundle->quizzes) : stripslashes($bundle->cat)?> </td>
					<td><a href="admin.php?page=watupro_payments&bundle_id=<?php echo $bundle->ID?>"><?php _e('View/Manage', 'watupro');?></a></td>					
					<td><a href="admin.php?page=watupro_bundles&do=edit&id=<?php echo $bundle->ID?>"><?php _e('Edit', 'watupro')?></a>
					| <a href="#" onclick="WatuPROconfirmDelBundle(<?php echo $bundle->ID?>);return false;"><?php _e('Delete', 'watupro')?></a></td>	
				</tr>
			<?php endforeach;?>
		</table>
	<?php endif;?>
</div>

<script type="text/javascript" >
function WatuPROconfirmDelBundle(id) {
	if(confirm("<?php _e('Are you sure?', 'watupro');?>")) {
		window.location='admin.php?page=watupro_bundles&del=1&id=' + id;
	}
}
</script>