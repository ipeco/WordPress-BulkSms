<?php
	if ( ! defined( 'ABSPATH' ) ) exit;
	
	// Wordpress new version
	if(get_option('wordpress_smsir_notification_new_wp_version')) {
	
		$update = get_site_transient('update_core');
		$update = $update->updates;
		
		if($update[1]->current > $wp_version) {
		
			if(get_option('wp_last_send_notification') == false) {
			
				$webservice = get_option('wordpress_smsir_webservice');
				include_once dirname( __FILE__ ) . "/../classes/wordpress_smsir.class.php";
				include_once dirname( __FILE__ ) . "/../classes/webservice/{$webservice}.class.php";
				
				$sms = new $webservice;
				
				$sms->to = array(get_option('wordpress_smsir_admin_mobile'));
				$sms->msg = sprintf(__('WordPress %s is available! Please update now', 'wordpress_smsir'), $update[1]->current);
				
				$sms->SendSMS();
				
				update_option('wp_last_send_notification', true);
				
			}
		} else {
			update_option('wp_last_send_notification', false);
		}
	}
	
	// Register new user
	function wordpress_smsir_notification_new_user($username_id) {
	
		global $wpdb, $table_prefix, $sms, $date;
		
		$sms->to = array(get_option('wordpress_smsir_admin_mobile'));
		
		$string = get_option('wordpress_smsir_nrnu_tt');
		
		$username_info = get_userdata($username_id);
		$user_id = $username_info->ID;
		$get_mobile = $wpdb->get_results("SELECT * FROM `{$table_prefix}usermeta` WHERE `user_id` = '$user_id' AND `meta_key` = 'mobile'");		
		$mobile = $get_mobile[0]->meta_value;

		$template_vars = array(
			'user_login'	=> $username_info->user_login,
			'user_email'	=> $username_info->user_email,
			'date_register'	=> $date,
		);
		
		$final_message = preg_replace('/%(.*?)%/ime', "\$template_vars['$1']", $string);
		
		$sms->msg = $final_message;
		
		$sms->SendSMS();
		
		if($mobile){
			$sms->inserttosmscustomerclub($username_info->user_login,$mobile);
		}
	}
	
	if(get_option('wordpress_smsir_nrnu_stats'))
		add_action('user_register', 'wordpress_smsir_notification_new_user', 10, 1);
	
	// New Comment
	function wordpress_smsir_notification_new_comment($comment_id, $comment_smsect){
	
		global $sms;
		
		$sms->to = array(get_option('wordpress_smsir_admin_mobile'));
		
		$string = get_option('wordpress_smsir_gnc_tt');
		
		$template_vars = array(
			'comment_author'		=> $comment_smsect->comment_author,
			'comment_author_email'	=> $comment_smsect->comment_author_email,
			'comment_author_url'	=> $comment_smsect->comment_author_url,
			'comment_author_IP'		=> $comment_smsect->comment_author_IP,
			'comment_date'			=> $comment_smsect->comment_date,
			'comment_content'		=> $comment_smsect->comment_content
		);
		
		$final_message = preg_replace('/%(.*?)%/ime', "\$template_vars['$1']", $string);
		
		$sms->msg = $final_message;
		
		$sms->SendSMS();
	}
	
	if(get_option('wordpress_smsir_gnc_stats'))
		add_action('wp_insert_comment', 'wordpress_smsir_notification_new_comment',99,2);
	
	// User login
	function wordpress_smsir_notification_login($username_login, $username){
	
		global $sms;
		
		$sms->to = array(get_option('wordpress_smsir_admin_mobile'));
		
		$string = get_option('wordpress_smsir_ul_tt');

		$template_vars = array(
			'user_login'	=> $username->user_login,
			'user_email'	=> $username->user_email,
			'user_registered'	=> $username->user_registered,
			'display_name'	=> $username->display_name
		);
		
		$final_message = preg_replace('/%(.*?)%/ime', "\$template_vars['$1']", $string);
		
		$sms->msg = $final_message;
		
		$sms->SendSMS();
	}
	
	if(get_option('wordpress_smsir_ul_stats'))
		add_action('wp_login', 'wordpress_smsir_notification_login', 99, 2);
	
	function wordpress_smsir_setup_wpcf7_form($form) {
		
		$options = get_option('wpcf7_sms_' . $form->id);
		
		include_once dirname( __FILE__ ) . "/../templates/wp-sms-wpcf7-form.php";
	}
	
	function wordpress_smsir_save_wpcf7_form($form) {
		update_option('wpcf7_sms_' . $form->id, $_POST['wpcf7-sms']);
	}
	
	function wordpress_smsir_send_wpcf7_sms($form) {
		
		global $sms;
		
		$options = get_option('wpcf7_sms_' . $form->id);
		$options['phone'] = get_option('wordpress_smsir_admin_mobile');
		$options['message'] = get_option('wordpress_smsir_cf7_no_tt');	
		
		$string = get_option('wordpress_smsir_cf7_no_tt');

		$template_vars = array(
			'form_title'	=> $form->title,
			'form_id'	=> $form->id
		);
		
		$final_message = preg_replace('/%(.*?)%/ime', "\$template_vars['$1']", $string);
		
		if( $options['message'] && $options['phone'] ) {
		
			// Replace merged Contact Form 7 fields
			/*if( defined( 'WPCF7_VERSION' ) && WPCF7_VERSION < 3.1 ) {
				$regex = '/\[\s*([a-zA-Z_][0-9a-zA-Z:._-]*)\s*\]/';
			} else {
				$regex = '/(\[?)\[\s*([a-zA-Z_][0-9a-zA-Z:._-]*)\s*\](\]?)/';
			}
			
			$callback = array( &$form, 'mail_callback' );
			
			$message = preg_replace_callback( $regex, $form, $options['message'] );*/
			
			$sms->to = array( $options['phone'] );
			$sms->msg = $final_message;
			
			$sms->SendSMS();
		}
	}

	// Contact Form 7 Hooks
	if( get_option('wordpress_smsir_add_wpcf7') ) {
		//add_action('wpcf7_admin_after_form', 'wordpress_smsir_setup_wpcf7_form'); 
		//add_action('wpcf7_after_save', 'wordpress_smsir_save_wpcf7_form');
		add_action('wpcf7_before_send_mail', 'wordpress_smsir_send_wpcf7_sms');
	}
		
	// Woocommerce Hooks
	function wordpress_smsir_woocommerce_new_order($order_id){
	
		global $sms;
		
		$sms->to = array(get_option('wordpress_smsir_admin_mobile'));
		
		$string = get_option('wordpress_smsir_wc_no_tt');
		
		$template_vars = array(
			'order_id'	=> $order_id,
		);
		
		$final_message = preg_replace('/%(.*?)%/ime', "\$template_vars['$1']", $string);
		
		$sms->msg = $final_message;
		
		$sms->SendSMS();
	}
	
	if(get_option('wordpress_smsir_wc_no_stats'))
		add_action('woocommerce_new_order', 'wordpress_smsir_woocommerce_new_order');
	
	// Easy Digital Downloads Hooks
	function wordpress_smsir_edd_new_order() {
	
		global $sms;
		
		$sms->to = array(get_option('wordpress_smsir_admin_mobile'));
		
		$sms->msg = get_option('wordpress_smsir_edd_no_tt');
		
		$sms->SendSMS();
	}
	
	if(get_option('wordpress_smsir_edd_no_stats'))
		add_action('edd_complete_purchase', 'wordpress_smsir_edd_new_order');