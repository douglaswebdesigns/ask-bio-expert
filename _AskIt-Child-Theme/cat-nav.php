
	<div id='cssmenu'>
		<?php $menuClass = 'nav clearfix';
			$menuID = 'category-menu';
			$secondaryNav = '';
			if (function_exists('wp_nav_menu')) {
				$secondaryNav = wp_nav_menu( array( 'theme_location' => 'category-menu', 'container' => '', 'fallback_cb' => '', 'menu_class' => $menuClass, 'menu_id' => $menuID, 'echo' => false ) );
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
	</div>

