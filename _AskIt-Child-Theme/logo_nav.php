	<a href="<?php echo esc_url( home_url( '/login' ) ); ?>">
		<?php $logo = (get_option('askit_logo') <> '') ? get_option('askit_logo') : get_template_directory_uri().'/images/logo.png'; ?>
		<img src="<?php echo esc_attr( $logo ); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" id="logo"/>
	</a>