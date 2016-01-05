<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
<?php elegant_description(); ?>
<?php elegant_keywords(); ?>
<?php elegant_canonical(); ?>

<link href='http://fonts.googleapis.com/css?family=Yanone+Kaffeesatz:light,regular,bold&amp;subset=latin' rel='stylesheet' type='text/css' />

<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

<!--[if lt IE 7]>
	<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/css/ie6style.css" />
	<script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/DD_belatedPNG_0.0.8a-min.js"></script>
	<script type="text/javascript">DD_belatedPNG.fix('img#logo, p#slogan, #menu, #top-overlay, #featured-overlay, span.overlay, .entry-content p.meta-info, #controllers span#right-arrow, #controllers span#left-arrow, #footer-content, #footer');</script>
<![endif]-->
<!--[if IE 7]>
	<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/css/ie7style.css" />
<![endif]-->
<!--[if IE 8]>
	<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/css/ie8style.css" />
<![endif]-->

<script type="text/javascript">
	document.documentElement.className = 'js';
</script>

<?php if ( is_singular() ) wp_enqueue_script( 'comment-reply' ); ?>
<?php wp_head(); ?>

</head>
<body<?php if ( is_front_page() || is_home() ) echo ' id="home"'; ?> <?php body_class(); ?>>
	<div id="header">
		<div id="header-inner">
			<div class="container">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php $logo = (get_option('askit_logo') <> '') ? get_option('askit_logo') : get_template_directory_uri().'/images/logo.png'; ?>
					<img src="<?php echo esc_attr( $logo ); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" id="logo"/>
				</a>

				<?php $menuClass = 'nav';
				$menuID = 'primary-menu';
				$primaryNav = '';
				if (function_exists('wp_nav_menu')) {
					$primaryNav = wp_nav_menu( array( 'theme_location' => 'primary-menu', 'container' => '', 'fallback_cb' => '', 'menu_class' => $menuClass, 'menu_id' => $menuID, 'echo' => false, 'walker' => new description_walker() ) );
				};
				if ($primaryNav == '') { ?>
					<ul id="<?php echo esc_attr( $menuID ); ?>" class="<?php echo esc_attr( $menuClass ); ?>">
						<?php if (get_option('askit_home_link') == 'on') { ?>
							<li <?php if (is_home()) echo('class="current_page_item"') ?>><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e('Home','AskIt') ?></a></li>
						<?php }; ?>

						<?php show_page_menu($menuClass,false,false); ?>

						<?php show_categories_menu($menuClass,false); ?>
					</ul> <!-- end ul#nav -->
				<?php }
				else echo($primaryNav); ?>

			</div> <!-- end .container -->
		</div> <!-- end #header-inner -->
	</div> <!-- end #header -->

	<div id="header-bottom">
		<div class="container">
			<?php $menuClass = 'nav clearfix';
			$menuID = 'secondary-menu';
			$secondaryNav = '';
			if (function_exists('wp_nav_menu')) {
				$secondaryNav = wp_nav_menu( array( 'theme_location' => 'secondary-menu', 'container' => '', 'fallback_cb' => '', 'menu_class' => $menuClass, 'menu_id' => $menuID, 'echo' => false ) );
			};
			if ($secondaryNav == '') { ?>
				<ul id="<?php echo esc_attr( $menuID ); ?>" class="<?php echo esc_attr( $menuClass ); ?>">
					<?php if (get_option('askit_home_link') == 'on') { ?>
						<li <?php if (is_home()) echo('class="current_page_item"') ?>><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e('Home','AskIt') ?></a></li>
					<?php }; ?>

					<?php show_page_menu($menuClass,false,false); ?>

					<?php show_categories_menu($menuClass,false); ?>
				</ul> <!-- end ul#nav -->
			<?php }
			else echo($secondaryNav); ?>

			<div id="search-bar">
				<form method="get" id="searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
					<input type="text" value="<?php esc_attr_e('search this site...','AskIt'); ?>" name="s" id="searchinput" />

					<input type="image" src="<?php echo get_template_directory_uri(); ?>/images/search-icon.png" id="searchsubmit" />
				</form>
			</div> <!-- end #search-bar -->

		</div> <!-- end .container -->
	</div> <!-- end #header-bottom -->

	<div id="content" >
		<div id="right-shadow">
			<div id="top-shadow"></div>
			<div class="container clearfix">