<?php
	if ( ! defined( 'ABSPATH' ) ) exit;
	
	function wordpress_smsir_subscribe_meta_box() {
		add_meta_box('subscribe-meta-box', __('Subscribe SMS', 'wordpress_smsir'), 'wordpress_smsir_subscribe_post', 'post', 'normal', 'high');
	}

	if(get_option('wordpress_smsir_subscribes_send'))
		add_action('add_meta_boxes', 'wordpress_smsir_subscribe_meta_box');
	
	function wordpress_smsir_subscribe_post($post) {
	
		$values = get_post_custom($post->ID);
		$selected = isset( $values['subscribe_post'] ) ? esc_attr( $values['subscribe_post'][0] ) : '';
		wp_nonce_field('subscribe_box_nonce', 'meta_box_nonce');
		
		include_once dirname( __FILE__ ) . "/../templates/settings/meta-box.php";
	}

	function wordpress_smsir_subscribe_post_save($post_id) {
	
		if(!current_user_can('edit_post')) return;

		if( isset( $_POST['subscribe_post'] ) )
			update_post_meta($post_id, 'subscribe_post', esc_attr($_POST['subscribe_post']));
			
	}
	add_action('save_post', 'wordpress_smsir_subscribe_post_save');

	function wordpress_smsir_subscribe_send($post_ID) {
	
		if($_REQUEST['subscribe_post'] == 'yes') {
		
			global $wpdb, $table_prefix, $sms;
			
			$sms->to = $wpdb->get_col("SELECT mobile FROM {$table_prefix}smsir_subscribes");
			
			$string = get_option('wordpress_smsir_text_template');
			
			$template_vars = array(
				'title_post'		=> get_the_title($post_ID),
				'url_post'			=> wp_get_shortlink($post_ID),
				'date_post'			=> get_post_time(get_option('date_format'), true, $post_ID)
			);
			
			$final_message = preg_replace('/%(.*?)%/ime', "\$template_vars['$1']", $string);
			
			if( get_option('wordpress_smsir_text_template') ) {
				$sms->msg = $final_message;
			} else {
				$sms->msg = get_the_title($post_ID);
			}
			
			$sms->SendSMS();
			
			return $post_ID;
		}
	}
	if(get_option('wordpress_smsir_subscribes_send'))
		add_action('publish_post', 'wordpress_smsir_subscribe_send');
	
	function wordpress_smsir_register_new_subscribe($name, $mobile) {
	
		global $sms;
		
		$string = get_option('wordpress_smsir_subscribes_text_send');
		
		$template_vars = array(
			'subscribe_name'	=> $name,
			'subscribe_mobile'	=> $mobile
		);
		
		$final_message = preg_replace('/%(.*?)%/ime', "\$template_vars['$1']", $string);
		
		$sms->to = array($mobile);
		$sms->msg = $final_message;
		
		$sms->SendSMS();
	}
	if(get_option('wordpress_smsir_subscribes_send_sms'))
		add_action('wordpress_smsir_subscribe', 'wordpress_smsir_register_new_subscribe', 10, 2);