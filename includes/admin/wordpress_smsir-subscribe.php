<?php
	include_once("../../../../../wp-load.php");
	
	$name	= trim($_REQUEST['name']);
	$mobile	= trim($_REQUEST['mobile']);
	$group	= $_REQUEST['group'];
	$type	= $_REQUEST['type'];
	
	if(isset($_REQUEST['group'])){
		$group_array = $_REQUEST['group'];
		$group = implode(",",$group_array);
	}

	if($name && $mobile && $group) {
		if(preg_match(WORDPRESS_SMSIR_MOBILE_REGEX, $mobile)) {
		
			global $wpdb, $table_prefix, $sms, $date;
			
			$check_mobile = $wpdb->query($wpdb->prepare("SELECT * FROM `{$table_prefix}smsir_subscribes` WHERE `mobile` = '%s'", $mobile));
			
			if(!$check_mobile || $type != 'subscribe') {
			
				if($type == 'subscribe') {
					date_default_timezone_set('Asia/Tehran');
					$get_current_date = date('Y-m-d H:i:s' ,current_time('timestamp',0));

					if(get_option('wordpress_smsir_subscribes_activation')) {
					
						$key = rand(1000, 9999);
						
						$check = $wpdb->insert("{$table_prefix}smsir_subscribes",
							array(
								'date'			=>	$get_current_date,
								'name'			=>	$name,
								'mobile'		=>	$mobile,
								'status'		=>	'0',
								'activate_key'	=>	$key,
								'group_ID'		=>	$group
							)
						);
						
						$sms->to = array($mobile);
						$sms->msg = __('Your activation code', 'wordpress_smsir') . ': ' . $key;
						
						$sms->SendSMS();

						if($check)
							echo 'success-3';
						
					} else {
						
						$check = $wpdb->insert("{$table_prefix}smsir_subscribes",
							array(
								'date'			=>	$get_current_date,
								'name'			=>	$name,
								'mobile'		=>	$mobile,
								'status'		=>	'1',
								'group_ID'		=>	$group
							)
						);
						
						if($check) {
							do_action('wordpress_smsir_subscribe', $name, $mobile);
							echo 'success-1';
							exit(0);
						}
					}
					
				} else if($type == 'unsubscribe') {
					if($check_mobile) {
					
						$check = $wpdb->delete("{$table_prefix}smsir_subscribes", array('mobile' => $mobile) );
						
						if($check)
							echo 'success-2';
							
					} else {
						_e('Nothing found!', 'wordpress_smsir');
					}
				}
				
			} else {
				_e('Phone number is repeated', 'wordpress_smsir');
			}
		} else {
			_e('Please enter a valid mobile number', 'wordpress_smsir');
		}
	} else {
		_e('Please complete all fields', 'wordpress_smsir');
	}
?>