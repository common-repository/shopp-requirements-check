<?php
/*
Plugin Name: Shopp Requirements Check
Version: 0.2
Description: Checks the WordPress hosting environment for required technologies to support Shopp the bolt-on ecommerce solution for WordPress.
Plugin URI: http://shopplugin.net
Author: Ingenesis Limited
Author URI: http://ingenesis.net

Copyright 2008 Ingenesis Limited

*/

$ShoppReqCheck = new ShoppReqCheck();

class ShoppReqCheck {
	var $errors = array();
	
	function ShoppReqCheck () {
		$this->path = basename(__FILE__);
		if (dirname(__FILE__) != "plugins")
			$this->path = basename(dirname(__FILE__))."/".$this->path;
		
		$errors = array();
		// Check PHP version, this won't appear much since syntax errors in earlier
		// PHP releases will cause this code to never be executed
		if (!version_compare(PHP_VERSION, '5.0.0', '>=')) 
			$errors[] = __("Shopp requires PHP version 5.0+.  You are using PHP version ").PHP_VERSION;

		// Check WordPress version
		if (!version_compare(get_bloginfo('version'),'2.6','>='))
			$errors[] = __("Shopp requires WordPress version 2.6+.  You are using WordPress version ").get_bloginfo('version');

		// Check for cURL
		if( !function_exists("curl_init") &&
		      !function_exists("curl_setopt") &&
		      !function_exists("curl_exec") &&
		      !function_exists("curl_close") ) $errors[] = __("Shopp requires the cURL library with SSL-support for processing transactions securely. Your web hosting environment does not currently have cURL installed (or built into PHP).");

		// Check for GD
		if (!function_exists("gd_info")) $errors[] = __("Shopp requires the GD image library with JPEG support for generating gallery and thumbnail images.  Your web hosting environment does not currently have GD installed (or built into PHP).");
		else {
			$gd = gd_info();
			if (!$gd['JPG Support']) $errors[] = __("Shopp requires JPEG support in the GD image library.  Your web hosting environment does not currently have a version of GD installed that has JPEG support.");
		}

		if (!empty($errors)) {
			$this->errors = $errors;
			add_action('admin_head-plugins.php',array(&$this,'notsupported'));
		} else {
			add_action('admin_head-plugins.php',array(&$this,'supported'));
		}
		
	}
	
	function notsupported () {
		$active_plugins = get_option('active_plugins');
		if (in_array($this->path,$active_plugins) && isset($_GET['activate']) && $_GET['activate'] == "true"):

		$message = '<p><strong>Sorry! Shopp is not supported here!</strong></p>';
		foreach ($this->errors as $error) $message .= "<p>$error</p>";
		$message .= '<p>You will not be able to use Shopp.  For more information, see the <a href="http://docs.shopplugin.net/Requirements" target="_blank">Shopp requirements documentation</a>.</p>';
		
		?>
		<script type="text/javascript">
		(function($) {
			$(document).ready(function () {
				$('#message').html('<?php echo $message; ?>');
			});
			
		})(jQuery)
		</script>
		<?php
			$key = array_search($this->path,$active_plugins);
			array_splice($active_plugins,$key,1);
			update_option('active_plugins',$active_plugins);
		endif;
	}
	
	function supported () {
		$active_plugins = get_option('active_plugins');
		if (in_array($this->path,$active_plugins) && isset($_GET['activate']) && $_GET['activate'] == "true"):
		?>
		<script type="text/javascript">
		(function($) {
			$(document).ready(function () {
				$('#message').html('<p><strong>Congratulations!</strong> Shopp is supported here! <a href="http://shopplugin.net">Get your copy of Shopp here.</a></p>');
			});
			
		})(jQuery)
		</script>
		<?php
			$key = array_search($this->path,$active_plugins);
			array_splice($active_plugins,$key,1);
			update_option('active_plugins',$active_plugins);
		endif;
	}
		
}
?>