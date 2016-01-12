<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head profile="http://gmpg.org/xfn/11">
<meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' /> 
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
<?php elegant_description(); ?>
<?php elegant_keywords(); ?>
<?php elegant_canonical(); ?>

<link href='http://fonts.googleapis.com/css?family=Yanone+Kaffeesatz:light,regular,bold&amp;subset=latin' rel='stylesheet' type='text/css' />

<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
<link rel="stylesheet" type="text/css" href="<?php echo get_stylesheet_directory_uri(); ?>/myMarquee/css/marquee.css" />
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
<!--[if lt IE 9]>
	<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/myMarquee/css/marquee_ie8.css" />
<![endif]-->

<script type="text/javascript">
	document.documentElement.className = 'js';
</script>

<?php if ( is_singular() ) wp_enqueue_script( 'comment-reply' ); ?>
<?php wp_head(); ?>

</head>
<body<?php if ( is_front_page() || is_home() ) echo ' id="home"'; ?> <?php body_class(); ?>>

	<?php include (STYLESHEETPATH . '/myMarquee/php/marquee_include.php'); ?>
	
			<div id="header-bottom">

				<?php $menuClass = 'nav';
					$menuID = 'primary-menu';
					$primaryNav = '';
					if (function_exists('wp_nav_menu')) {
						$primaryNav = wp_nav_menu( array( 
							'theme_location' => 'primary-menu', 
							'row' => '', 
							'fallback_cb' => '', 
							'menu_class' => $menuClass, 
							'menu_id' => $menuID, 
							'echo' => false, 
							'walker' => new description_walker() ) );
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
				
			</div>
		</div>
	</div>
	<div class="row">		
			<div id="content" >
				<div id="right-shadow">
					<div id="top-shadow"></div>
						<div class="full-container">					
							<?php if( !is_user_logged_in()  && is_page_template('page-login.php') ) { ?>									
								<div id="header-top">	
									<div id="header-spacer"></div>													
									<div class="col-lg-3 col-md-3 col-xs-12">	
										<h2 class="title text-center">The Expert Center</h2>
										<div class="et-protected-bgd">						
											<div class="et-protected-form">
												<?php $scheme = apply_filters( 'et_forms_scheme', null ); ?>
												<?php $scheme = apply_filters( 'et_forms_scheme', null ); ?>
												<form action='<?php echo esc_url( home_url( '', $scheme ) ); ?>/wp-login.php' method='post'>
													<p><label><span><?php esc_html_e('Username','AskIt'); ?>: </span><input type='text' name='log' id='log' value='<?php echo esc_attr($user_login); ?>' size='20' /><span class='et_protected_icon'></span></label></p>
													<p><label><span><?php esc_html_e('Password','AskIt'); ?>: </span><input type='password' name='pwd' id='pwd' size='20' /><span class='et_protected_icon et_protected_password'></span></label></p>
													<input type='submit' name='submit' value='Login' class='etlogin-button' />
												</form>
											</div> <!-- .et-protected-form -->
										</div> <!-- .et-protected -->
										<div class="sign-up-button">
										<a href="http://www.your-new-wp-site.com/askabioexpert/register/"><img src="http://www.your-new-wp-site.com/askabioexpert/wp-content/uploads/2015/12/sign-up-buttons.png" style="float: left"></a>
										</div>
										<br>
								<div class="microSys-ad-logged-out">
										<?php // MicroSys Ad Campaign --Change Ad in the Backend-- --> ?>

											<?php if (get_option('askit_integration_single_bottom') <> '' && get_option('askit_integrate_singlebottom_enable') == 'on') echo(get_option('askit_integration_single_bottom')); ?>

												<?php if (get_option('askit_468_enable') == 'on') { ?>
												<?php if(get_option('askit_468_adsense') <> '') echo ( get_option('askit_468_adsense') );
												else { ?>

													<a href="<?php echo esc_url(get_option('askit_468_url')); ?>"><img src="<?php echo esc_url(get_option('askit_468_image')); ?>" alt="Microsys Ad" class="foursixeight" /></a>
												
												<?php } ?>
										<?php } ?>

								</div>
								</div>	
								<div class="col-lg-5 col-lg-push-1 col-md-5 col-xs-12">			
										<div class="text-left">	
													<?php if (get_option('askit_greeting_enable') == 'on') { ?>
															<?php if(get_option('askit_greeting_text') <> '') echo ( get_option('askit_greeting_text') );
															else { ?>
																<a href="<?php echo esc_url(get_option('askit_greeting_url')); ?>"><img src="<?php echo esc_url(get_option('askit_greeting_image')); ?>" alt="homepage image askabioexpert" class="foursixeight" /></a>
															<?php } ?>
													<?php } ?>																
										</div>

										<div class="instruction-container">
											<p><strong><em><?php the_field('visitor_instructions'); ?></em></strong></p>
										</div>												
								</div>	
								<div class="expert-guy text-center col-lg-3 col-lg-push-1 col-md-3 img-responsive hidden-xs">
									<img src="http://www.your-new-wp-site.com/askabioexpert/wp-content/themes/AskIt-child/images/answer-guy-250.png">
									<div class="img-responsive">
										<?php 

											$image = get_field('sponsor_image');

											if( !empty($image) ): ?>

												<img src="<?php echo $image['url']; ?>" alt="<?php echo $image['alt']; ?>" />

										<?php endif; ?>																
									</div>
								</div>										
							</div>	
						</div>	
					</div>
				</div>
			</div>
		</div>

				<?php } else { ?>

				<div class="row">									
					<div class="col-xs-12" id="header-top">	
						<div id="header-spacer"></div>
						<div class="col-lg-3 col-lg-push-1 col-xs-12">

								<p class="leader">You are currently logged into</p>
								<h2 class="title leader times">The Expert Center</h2>	
							<br>
							<p class="small"><a href="<?php echo wp_logout_url(); ?>">Logout</a></p>
							<div class="microSys-ad">
								<?php if (get_option('askit_integration_single_bottom') <> '' && get_option('askit_integrate_singlebottom_enable') == 'on') echo(get_option('askit_integration_single_bottom')); ?>

									<?php if (get_option('askit_468_enable') == 'on') { ?>
									<?php if(get_option('askit_468_adsense') <> '') echo ( get_option('askit_468_adsense') );
											else { ?>
										<a href="<?php echo esc_url(get_option('askit_468_url')); ?>"><img src="<?php echo esc_url(get_option('askit_468_image')); ?>" alt="Microsys Ad" class="foursixeight" /></a>
									<?php } ?>
								<?php } ?>
							</div>
						</div>
						<div class="">	
							<div class="col-lg-4 col-lg-push-1 col-md-4 col-xs-12">			
								<div class="img-responsive text-left">
										<?php 

											$image = get_field('sponsor_image');

											if( !empty($image) ): ?>

												<img src="<?php echo $image['url']; ?>" alt="<?php echo $image['alt']; ?>" />

										<?php endif; ?>
										
								</div>

								<div class="instruction-container">

									<div class="negative-space"></div>

								</div>												
							</div>	
							<div class="expert-guy text-center col-lg-3 col-lg-push-1 img-responsive hidden-xs">
							<img src="http://www.your-new-wp-site.com/askabioexpert/wp-content/themes/AskIt-child/images/answer-guy-250.png">
							</div>										
						</div>	
					</div>	
				</div>				

				<?php } ?>
				<?php if( !is_page_template('page-login.php') ) { ?>

				<div class="row">
					<div class="col-xs-12">	
						<div class="row">
							<div class="title-container text-right col-xs-3">
									
							</div>	
						</div>
					</div>
				</div>



				<?php } ?>

		<div class="container clearfix">

