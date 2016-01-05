<?php
add_action( 'admin_enqueue_scripts', 'import_epanel_javascript' );
function import_epanel_javascript( $hook_suffix ) {
	if ( 'admin.php' == $hook_suffix && isset( $_GET['import'] ) && isset( $_GET['step'] ) && 'wordpress' == $_GET['import'] && '1' == $_GET['step'] )
		add_action( 'admin_head', 'admin_headhook' );
}

function admin_headhook(){ ?>
	<script type="text/javascript">
		jQuery(document).ready(function($){
			$("p.submit").before("<p><input type='checkbox' id='importepanel' name='importepanel' value='1' style='margin-right: 5px;'><label for='importepanel'>Import epanel settings</label></p>");
		});
	</script>
<?php }

add_action('import_end','importend');
function importend(){
	global $wpdb, $shortname;

	#make custom fields image paths point to sampledata/sample_images folder
	$sample_images_postmeta = $wpdb->get_results(
		$wpdb->prepare( "SELECT meta_id, meta_value FROM $wpdb->postmeta WHERE meta_value REGEXP %s", 'http://et_sample_images.com' )
	);
	if ( $sample_images_postmeta ) {
		foreach ( $sample_images_postmeta as $postmeta ){
			$template_dir = get_template_directory_uri();
			if ( is_multisite() ){
				switch_to_blog(1);
				$main_siteurl = site_url();
				restore_current_blog();

				$template_dir = $main_siteurl . '/wp-content/themes/' . get_template();
			}
			preg_match( '/http:\/\/et_sample_images.com\/([^.]+).jpg/', $postmeta->meta_value, $matches );
			$image_path = $matches[1];

			$local_image = preg_replace( '/http:\/\/et_sample_images.com\/([^.]+).jpg/', $template_dir . '/sampledata/sample_images/$1.jpg', $postmeta->meta_value );

			$local_image = preg_replace( '/s:55:/', 's:' . strlen( $template_dir . '/sampledata/sample_images/' . $image_path . '.jpg' ) . ':', $local_image );

			$wpdb->update( $wpdb->postmeta, array( 'meta_value' => esc_url_raw( $local_image ) ), array( 'meta_id' => $postmeta->meta_id ), array( '%s' ) );
		}
	}

	if ( !isset($_POST['importepanel']) )
		return;

	$importOptions = 'YTo4Njp7czowOiIiO047czoxNjoibWFnbmlmaWNlbnRfbG9nbyI7czowOiIiO3M6MTk6Im1hZ25pZmljZW50X2Zhdmljb24iO3M6MDoiIjtzOjI0OiJtYWduaWZpY2VudF9jb2xvcl9zY2hlbWUiO3M6NzoiRGVmYXVsdCI7czoyMjoibWFnbmlmaWNlbnRfYmxvZ19zdHlsZSI7TjtzOjIyOiJtYWduaWZpY2VudF9ncmFiX2ltYWdlIjtOO3M6MTc6Im1hZ25pZmljZW50X3F1b3RlIjtzOjU0OiJIZXJlIElzIFlvdXIgU2xvZ2FuIDxzcGFuPiY8L3NwYW4+IFNvbWV0aGluZyBFbHNlIEhlcmUiO3M6MjQ6Im1hZ25pZmljZW50X2NhdG51bV9wb3N0cyI7czoxOiI2IjtzOjI4OiJtYWduaWZpY2VudF9hcmNoaXZlbnVtX3Bvc3RzIjtzOjE6IjUiO3M6Mjc6Im1hZ25pZmljZW50X3NlYXJjaG51bV9wb3N0cyI7czoxOiI1IjtzOjI0OiJtYWduaWZpY2VudF90YWdudW1fcG9zdHMiO3M6MToiNSI7czoyMzoibWFnbmlmaWNlbnRfZGF0ZV9mb3JtYXQiO3M6NjoiTSBqLCBZIjtzOjIzOiJtYWduaWZpY2VudF91c2VfZXhjZXJwdCI7TjtzOjI2OiJtYWduaWZpY2VudF9ob21lcGFnZV9wb3N0cyI7czoxOiI3IjtzOjI2OiJtYWduaWZpY2VudF9leGxjYXRzX3JlY2VudCI7TjtzOjIwOiJtYWduaWZpY2VudF9mZWF0dXJlZCI7czoyOiJvbiI7czoyMToibWFnbmlmaWNlbnRfZHVwbGljYXRlIjtOO3M6MjA6Im1hZ25pZmljZW50X2ZlYXRfY2F0IjtzOjQ6IkJsb2ciO3M6MjQ6Im1hZ25pZmljZW50X2ZlYXR1cmVkX251bSI7czoxOiI1IjtzOjIxOiJtYWduaWZpY2VudF91c2VfcGFnZXMiO047czoyMjoibWFnbmlmaWNlbnRfZmVhdF9wYWdlcyI7TjtzOjIzOiJtYWduaWZpY2VudF9zbGlkZXJfYXV0byI7TjtzOjIzOiJtYWduaWZpY2VudF9wYXVzZV9ob3ZlciI7TjtzOjI4OiJtYWduaWZpY2VudF9zbGlkZXJfYXV0b3NwZWVkIjtzOjQ6IjQwMDAiO3M6MjE6Im1hZ25pZmljZW50X21lbnVwYWdlcyI7TjtzOjI4OiJtYWduaWZpY2VudF9lbmFibGVfZHJvcGRvd25zIjtzOjI6Im9uIjtzOjIxOiJtYWduaWZpY2VudF9ob21lX2xpbmsiO3M6Mjoib24iO3M6MjI6Im1hZ25pZmljZW50X3NvcnRfcGFnZXMiO3M6MTA6InBvc3RfdGl0bGUiO3M6MjI6Im1hZ25pZmljZW50X29yZGVyX3BhZ2UiO3M6MzoiYXNjIjtzOjI5OiJtYWduaWZpY2VudF90aWVyc19zaG93bl9wYWdlcyI7czoxOiIzIjtzOjIwOiJtYWduaWZpY2VudF9tZW51Y2F0cyI7TjtzOjM5OiJtYWduaWZpY2VudF9lbmFibGVfZHJvcGRvd25zX2NhdGVnb3JpZXMiO3M6Mjoib24iO3M6Mjg6Im1hZ25pZmljZW50X2NhdGVnb3JpZXNfZW1wdHkiO3M6Mjoib24iO3M6MzQ6Im1hZ25pZmljZW50X3RpZXJzX3Nob3duX2NhdGVnb3JpZXMiO3M6MToiMyI7czoyMDoibWFnbmlmaWNlbnRfc29ydF9jYXQiO3M6NDoibmFtZSI7czoyMToibWFnbmlmaWNlbnRfb3JkZXJfY2F0IjtzOjM6ImFzYyI7czoyNzoibWFnbmlmaWNlbnRfZGlzYWJsZV90b3B0aWVyIjtOO3M6MjI6Im1hZ25pZmljZW50X3RodW1ibmFpbHMiO3M6Mjoib24iO3M6Mjk6Im1hZ25pZmljZW50X3Nob3dfcG9zdGNvbW1lbnRzIjtzOjI6Im9uIjtzOjI3OiJtYWduaWZpY2VudF9wYWdlX3RodW1ibmFpbHMiO047czozMDoibWFnbmlmaWNlbnRfc2hvd19wYWdlc2NvbW1lbnRzIjtOO3M6Mjc6Im1hZ25pZmljZW50X2Jsb2dfdGh1bWJuYWlscyI7czoyOiJvbiI7czoyNToibWFnbmlmaWNlbnRfY3VzdG9tX2NvbG9ycyI7TjtzOjIxOiJtYWduaWZpY2VudF9jaGlsZF9jc3MiO047czoyNDoibWFnbmlmaWNlbnRfY2hpbGRfY3NzdXJsIjtzOjA6IiI7czoyNToibWFnbmlmaWNlbnRfY29sb3JfYmdjb2xvciI7czowOiIiO3M6MjY6Im1hZ25pZmljZW50X2NvbG9yX21haW5mb250IjtzOjA6IiI7czoyNjoibWFnbmlmaWNlbnRfY29sb3JfbWFpbmxpbmsiO3M6MDoiIjtzOjI2OiJtYWduaWZpY2VudF9jb2xvcl9wYWdlbGluayI7czowOiIiO3M6MzI6Im1hZ25pZmljZW50X2NvbG9yX3NpZGViYXJfdGl0bGVzIjtzOjA6IiI7czozMToibWFnbmlmaWNlbnRfY29sb3JfZm9vdGVyX3RpdGxlcyI7czowOiIiO3M6MzA6Im1hZ25pZmljZW50X2NvbG9yX2Zvb3Rlcl9saW5rcyI7czowOiIiO3M6MjY6Im1hZ25pZmljZW50X3Nlb19ob21lX3RpdGxlIjtOO3M6MzI6Im1hZ25pZmljZW50X3Nlb19ob21lX2Rlc2NyaXB0aW9uIjtOO3M6Mjk6Im1hZ25pZmljZW50X3Nlb19ob21lX2tleXdvcmRzIjtOO3M6MzA6Im1hZ25pZmljZW50X3Nlb19ob21lX2Nhbm9uaWNhbCI7TjtzOjMwOiJtYWduaWZpY2VudF9zZW9faG9tZV90aXRsZXRleHQiO3M6MDoiIjtzOjM2OiJtYWduaWZpY2VudF9zZW9faG9tZV9kZXNjcmlwdGlvbnRleHQiO3M6MDoiIjtzOjMzOiJtYWduaWZpY2VudF9zZW9faG9tZV9rZXl3b3Jkc3RleHQiO3M6MDoiIjtzOjI1OiJtYWduaWZpY2VudF9zZW9faG9tZV90eXBlIjtzOjI3OiJCbG9nTmFtZSB8IEJsb2cgZGVzY3JpcHRpb24iO3M6Mjk6Im1hZ25pZmljZW50X3Nlb19ob21lX3NlcGFyYXRlIjtzOjM6IiB8ICI7czoyODoibWFnbmlmaWNlbnRfc2VvX3NpbmdsZV90aXRsZSI7TjtzOjM0OiJtYWduaWZpY2VudF9zZW9fc2luZ2xlX2Rlc2NyaXB0aW9uIjtOO3M6MzE6Im1hZ25pZmljZW50X3Nlb19zaW5nbGVfa2V5d29yZHMiO047czozMjoibWFnbmlmaWNlbnRfc2VvX3NpbmdsZV9jYW5vbmljYWwiO047czozNDoibWFnbmlmaWNlbnRfc2VvX3NpbmdsZV9maWVsZF90aXRsZSI7czo5OiJzZW9fdGl0bGUiO3M6NDA6Im1hZ25pZmljZW50X3Nlb19zaW5nbGVfZmllbGRfZGVzY3JpcHRpb24iO3M6MTU6InNlb19kZXNjcmlwdGlvbiI7czozNzoibWFnbmlmaWNlbnRfc2VvX3NpbmdsZV9maWVsZF9rZXl3b3JkcyI7czoxMjoic2VvX2tleXdvcmRzIjtzOjI3OiJtYWduaWZpY2VudF9zZW9fc2luZ2xlX3R5cGUiO3M6MjE6IlBvc3QgdGl0bGUgfCBCbG9nTmFtZSI7czozMToibWFnbmlmaWNlbnRfc2VvX3NpbmdsZV9zZXBhcmF0ZSI7czozOiIgfCAiO3M6MzE6Im1hZ25pZmljZW50X3Nlb19pbmRleF9jYW5vbmljYWwiO047czozMzoibWFnbmlmaWNlbnRfc2VvX2luZGV4X2Rlc2NyaXB0aW9uIjtOO3M6MjY6Im1hZ25pZmljZW50X3Nlb19pbmRleF90eXBlIjtzOjI0OiJDYXRlZ29yeSBuYW1lIHwgQmxvZ05hbWUiO3M6MzA6Im1hZ25pZmljZW50X3Nlb19pbmRleF9zZXBhcmF0ZSI7czozOiIgfCAiO3M6MzU6Im1hZ25pZmljZW50X2ludGVncmF0ZV9oZWFkZXJfZW5hYmxlIjtzOjI6Im9uIjtzOjMzOiJtYWduaWZpY2VudF9pbnRlZ3JhdGVfYm9keV9lbmFibGUiO3M6Mjoib24iO3M6Mzg6Im1hZ25pZmljZW50X2ludGVncmF0ZV9zaW5nbGV0b3BfZW5hYmxlIjtzOjI6Im9uIjtzOjQxOiJtYWduaWZpY2VudF9pbnRlZ3JhdGVfc2luZ2xlYm90dG9tX2VuYWJsZSI7czoyOiJvbiI7czoyODoibWFnbmlmaWNlbnRfaW50ZWdyYXRpb25faGVhZCI7czowOiIiO3M6Mjg6Im1hZ25pZmljZW50X2ludGVncmF0aW9uX2JvZHkiO3M6MDoiIjtzOjM0OiJtYWduaWZpY2VudF9pbnRlZ3JhdGlvbl9zaW5nbGVfdG9wIjtzOjA6IiI7czozNzoibWFnbmlmaWNlbnRfaW50ZWdyYXRpb25fc2luZ2xlX2JvdHRvbSI7czowOiIiO3M6MjI6Im1hZ25pZmljZW50XzQ2OF9lbmFibGUiO047czoyMToibWFnbmlmaWNlbnRfNDY4X2ltYWdlIjtzOjA6IiI7czoxOToibWFnbmlmaWNlbnRfNDY4X3VybCI7czowOiIiO3M6MjM6Im1hZ25pZmljZW50XzQ2OF9hZHNlbnNlIjtzOjA6IiI7fQ==';

	/*global $options;

	foreach ($options as $value) {
		if( isset( $value['id'] ) ) {
			update_option( $value['id'], $value['std'] );
		}
	}*/

	$importedOptions = unserialize(base64_decode($importOptions));

	foreach ($importedOptions as $key => $value) {
		if ( $value != '' ) update_option( $key, $value );
	}
} ?>