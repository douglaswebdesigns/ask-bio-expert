<?php global $shortname; ?>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/jquery-ui.min.js"></script>
	<script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/superfish.js"></script>
	<script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/cufon-yui.js"></script>
	<script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/League_Gothic_400.font.js"></script>

	<script type="text/javascript">
	//<![CDATA[
		jQuery.noConflict();
		jQuery(document).ready(function(){
			Cufon.replace('ul#primary-menu a strong',{textShadow:'1px 1px 1px #000', hover: { textShadow: '1px 1px 1px #000' }})('ul#primary-menu ul a',{textShadow:'1px 1px 1px #000'});

			jQuery('ul.nav').superfish({
				delay:       300,                            // one second delay on mouseout
				animation:   {opacity:'show',height:'show'},  // fade-in and slide-down animation
				speed:       'fast',                          // faster animation speed
				autoArrows:  true,                           // disable generation of arrow mark-up
				dropShadows: false                            // disable drop shadows
			});

			jQuery('ul.nav > li > a.sf-with-ul').parent('li').addClass('sf-ul');

			et_search_bar();

			var $featured_content = jQuery('#slides');

			if ($featured_content.length) {
				$featured_content.cycle({
					timeout: 0,
					speed: 500,
					cleartypeNoBg: true,
					prev:   '#featured a#left-arrow',
					next:   '#featured a#right-arrow'
				});
			}

			<!---- Search Bar Improvements ---->
			function et_search_bar(){
				var $searchform = jQuery('#header-bottom div#search-bar'),
					$searchinput = $searchform.find("input#searchinput"),
					searchvalue = $searchinput.val();

				$searchinput.focus(function(){
					if (jQuery(this).val() === searchvalue) jQuery(this).val("");
				}).blur(function(){
					if (jQuery(this).val() === "") jQuery(this).val(searchvalue);
				});
			};

			et_footer_improvements('#footer .footer-widget');

			<!---- Footer Improvements ---->
			function et_footer_improvements($selector){
				var $footer_widget = jQuery($selector);

				if (!($footer_widget.length == 0)) {
					$footer_widget.each(function (index, domEle) {
						if ((index+1)%4 == 0) jQuery(domEle).addClass("last").after("<div class='clear'></div>");
					});
				}
			}

			$comment_rating = jQuery('.comment_rating');

			$comment_rating.live('mouseover', function(){
				if ( jQuery(this).find('.et_like_dislike_box').css('visibility') == 'visible' ) return;
				if ( jQuery(this).find('.et_like_dislike_box').length ) {
					jQuery(this).prev('.comment-wrap-inner').animate({ opacity: .5 }, 500);
					jQuery(this).parent().prev('.avatar-box').animate({ opacity: .5 }, 500);

					jQuery(this).find('.et_like_dislike_box').css( { opacity: 0, 'visibility': 'visible' } ).animate( { opacity: 1, left: 51 }, 500);
				}
			});

			$comment_rating.live('mouseout', function(e){
				if ( jQuery(e.relatedTarget).parents('.et_like_dislike_box').length || jQuery(e.relatedTarget).is('.et_like_dislike_box') )  return;

				if ( jQuery(this).find('.et_like_dislike_box').length ) {
					jQuery(this).find('.et_like_dislike_box').animate( { opacity: 0, left: 61 }, 500, function(){
						jQuery(this).css( { opacity: 0, 'visibility': 'hidden' } )
					});

					jQuery(this).prev('.comment-wrap-inner').animate({ opacity: 1 }, 500);
					jQuery(this).parent().prev('.avatar-box').animate({ opacity: 1 }, 500);
				}
			});


			$mainContent = jQuery('#comment-wrap');

			jQuery('a.right_answer, a.et_like_button, a.et_dislike_button').live("click", function(){
				$href = jQuery(this).attr('href');
				$mainContent.fadeTo('fast',0.2).load($href+' #comment-wrap', function() {
					$mainContent.fadeTo('fast',1);
				});
				return false;
			});


			$contentArea = jQuery('#main-area');

			jQuery('body#home #main-tabbed-area a, body#home .wp-pagenavi a, body#home .pagination a').live("click", function(){
				$href = jQuery(this).attr('href');
				$contentArea.fadeTo('fast',0.2).load($href+' #main-area', function() {
					$contentArea.fadeTo('fast',1);
				});
				return false;
			});


			$createNewAnswer = jQuery('#create_new_post');

			$createNewAnswer.find('form').submit(function() {
				$href = jQuery(this).attr('action');

				$createNewAnswer.fadeTo('fast',0.2).load($href+' #create_new_post', jQuery(this).serializeArray(), function() {
					$createNewAnswer.fadeTo('fast',1);
				});
				return false;
			});

			var $tabbed_area = jQuery('div#tabbed');
			if ($tabbed_area.length) {
				$tabbed_area.tabs({ hide : true });
			};

			<?php if (get_option($shortname.'_disable_toptier') == 'on') echo('jQuery("ul.nav > li > ul").prev("a").attr("href","#");'); ?>

			Cufon.now();
		});
	//]]>
	</script>