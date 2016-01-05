			</div> <!-- end .container -->
		</div> 	<!-- end #right-shadow -->

	</div> <!-- end #content -->

	<div id="footer">
		<div id="footer-content">
			<div class="container clearfix">
				<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Footer') ) : ?>
				<?php endif; ?>
			</div> <!-- end .container -->

			<div id="footer-bottom">
				<div class="container clearfix">
					<?php $menuClass = 'bottom-nav';
					$menuID = 'footer-menu';
					$footerNav = '';
					if (function_exists('wp_nav_menu')) {
						$footerNav = wp_nav_menu( array( 'theme_location' => 'footer-menu', 'container' => '', 'fallback_cb' => '', 'menu_class' => $menuClass, 'menu_id' => $menuID, 'echo' => false, 'depth' => '1' ) );
					};
					if ($footerNav == '') { ?>
						<ul id="<?php echo esc_attr( $menuID ); ?>" class="<?php echo esc_attr( $menuClass ); ?>">
							<?php if (get_option('askit_home_link') == 'on') { ?>
								<li <?php if (is_home()) echo('class="current_page_item"') ?>><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e('Home','AskIt') ?></a></li>
							<?php }; ?>

							<?php show_page_menu($menuClass,false,false); ?>

							<?php show_categories_menu($menuClass,false); ?>
						</ul> <!-- end ul#nav -->
					<?php }
					else echo($footerNav); ?>

					<p id="copyright"><?php esc_html_e('Security ','AskIt'); ?> <a href="#" title="Douglas Web Designs">Policy</a> | <?php esc_html_e('Privacy ','AskIt'); ?> <a href="#">Policy</a></p>
				</div> <!-- end .container -->
			</div> <!-- end #footer-bottom -->
		</div> <!-- end #footer-content -->
	</div> <!-- end #footer -->

	<?php get_template_part('includes/scripts'); ?>

	<?php wp_footer(); ?>
</body>
</html>