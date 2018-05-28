<?php
	function wordpress_smsir_add_meta_links($links, $file) {
		if( $file == 'wordpress_smsir/wordpress_smsir.php' ) {
			$links[] = '<a href="http://codecanyon.net/item/wordpress_smsir-pro/9380372" target="_blank" title="'. __('Upgrade to pro version', 'wordpress_smsir') .'">'. __('Upgrade to pro version', 'wordpress_smsir') .'</a>';
		}
		
		return $links;
	}
	add_filter('plugin_row_meta', 'wordpress_smsir_add_meta_links', 10, 2);