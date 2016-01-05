<?php

class CMA_QuestionsWidget extends WP_Widget
{

    public function __construct()
    {
        $widget_ops = array('classname' => 'CMA_QuestionsWidget', 'description' => 'Show CM Questions');
        parent::__construct('CMA_QuestionsWidget', 'CM Questions', $widget_ops);
    }

    public static function register()
    {
        register_widget(__CLASS__);
    }

    /**
     * Widget options form
     * @param WP_Widget $instance
     */
    public function form($instance)
    {
        $instance = wp_parse_args((array) $instance, array(
        	'title' => '',
        	'limit' => 5,
        	'sort' => CMA_Settings::getOption(CMA_Settings::OPTION_INDEX_ORDER_BY),
        	'order' => 'desc',
            'cat' => '',
            'author' => '',
        	'contributor' => '',
        	'answered' => null,
        	'resolved' => '',
            'form' => false,
        	'pagination' => false,
        	'ajax' => false,
        	'formOnTop' => false,
            'displaySearch' => false,
            'displayTags' => false,
            'displayCategories' => true,
        	'displayResolvedPrefix' => true,
        	'displayIcons' => true,
            'displayViews' => false,
            'displayVotes' => false,
            'displayAnswers' => false,
            'displayUpdated' => true,
            'displayAuthorInfo' => true,
            'displayStatusInfo' => false,
        	'displayNavBar' => false,
            'displayWrapperClass' => 'cma-sidebar-questions',
        ));

        $title               = $instance['title'];
        $limit               = $instance['limit'];
        $sort                = $instance['sort'];
        $order               = $instance['order'];
        $cat                 = $instance['cat'];
        $author              = $instance['author'];
        $contributor         = $instance['contributor'];
        $answered            = $instance['answered'];
        $resolved            = $instance['resolved'];
        $form                = $instance['form'];
        $pagination          = $instance['pagination'];
        $ajax                = $instance['ajax'];
        $formOnTop           = $instance['formOnTop'];
        $displaySearch       = $instance['displaySearch'];
        $displayTags         = $instance['displayTags'];
        $displayCategories   = $instance['displayCategories'];
        $displayResolvedPrefix = $instance['displayResolvedPrefix'];
        $displayIcons        = $instance['displayIcons'];
        $displayViews        = $instance['displayViews'];
        $displayVotes        = $instance['displayVotes'];
        $displayAnswers      = $instance['displayAnswers'];
        $displayUpdated      = $instance['displayUpdated'];
        $displayAuthorInfo   = $instance['displayAuthorInfo'];
        $displayStatusInfo   = $instance['displayStatusInfo'];
        $displayNavBar       = $instance['displayNavBar'];
        $displayWrapperClass = $instance['displayWrapperClass'];
        ?>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                Title: <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
            </label>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('limit')); ?>">
                Limit: <input class="widefat" id="<?php echo esc_attr($this->get_field_id('limit')); ?>" name="<?php echo esc_attr($this->get_field_name('limit')); ?>" type="text" value="<?php echo esc_attr($limit); ?>" />
            </label>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('sort')); ?>">
                Sort by: <select class="widefat" id="<?php echo esc_attr($this->get_field_id('sort')); ?>" name="<?php echo esc_attr($this->get_field_name('sort')); ?>">
                    <?php
                    $options = array(
						'newest' => 'orderby_newest',
						'hottest' => 'orderby_hottest',
						'views' => 'orderby_most_views',
						'votes' => 'orderby_most_votes'
					);
                    foreach($options as $key => $name)
                    {
                        echo '<option value="' . $key . '"';
                        if($key == $sort) echo ' selected="selected"';
                        echo '>' . CMA_Labels::getLocalized($name) . '</option>';
                    }
                    ?>
                </select>
            </label>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('order')); ?>">Sort order:<select class="widefat" id="<?php echo esc_attr($this->get_field_id('order')); ?>" name="<?php echo esc_attr($this->get_field_name('order')); ?>">
            		<option value=""<?php if(!strlen($order)) echo ' selected="selected"'; ?>>any</option>
                    <option value="desc"<?php if($order == 'desc') echo ' selected="selected"'; ?>>DESC</option>
                    <option value="asc"<?php if($order == 'asc') echo ' selected="selected"'; ?>>ASC</option>
                </select>
            </label>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('cat')); ?>">Category: <select class="widefat" id="<?php echo esc_attr($this->get_field_id('cat')); ?>" name="<?php echo esc_attr($this->get_field_name('cat')); ?>">
                    <option value="">All categories</option>
                    <?php
                    $options = get_terms(CMA_Category::TAXONOMY, array(
                        'orderby' => 'name',
                        'hide_empty' => 0
                    ));
                    foreach($options as $term)
                    {
                        echo '<option value="' . $term->term_id . '"';
                        if($term->term_id == $cat) echo ' selected="selected"';
                        echo '>' . $term->name . '</option>';
                    }
                    ?>
                </select>
            </label>
        </p>
        <div><p>
            <label for="<?php echo esc_attr($this->get_field_id('author')); ?>">Author: <span class="user-name"><?php
            
            $user = null;
            if (!empty($author) AND $user = get_user_by('slug', $author)) {
				echo esc_html($user->display_name);
			} else {
				echo 'any';
			}
            
            ?></span><span class="cma-user-remove"<?php if (empty($user)) echo ' style="display:none"'; ?>><a href="#" class="btn-user-remove">remove</a></span></label><br />
            <label><span style="display:inline-block;width:100px;">Find user:</span><input class="cma-users-search" type="text" /></label>
            <input id="<?php echo esc_attr($this->get_field_id('author')); ?>" name="<?php echo esc_attr($this->get_field_name('author')); ?>" type="hidden" value="<?php echo esc_attr($author); ?>" />
            <ul class="searchUsersResults" style="display:none"></ul>
        </p></div>
        <div><p>
            <label for="<?php echo esc_attr($this->get_field_id('contributor')); ?>">Contributor: <span class="user-name"><?php
            
            $user = null;
            if (!empty($contributor) AND $user = get_user_by('slug', $contributor)) {
				echo esc_html($user->display_name);
			} else {
				echo 'any';
			}
            
            ?></span><span class="cma-user-remove"<?php if (empty($user)) echo ' style="display:none"'; ?>><a href="#" class="btn-user-remove">remove</a></span></label><br />
            <label><span style="display:inline-block;width:100px;">Find user:</span><input class="cma-users-search" type="text" /></label>
            <input id="<?php echo esc_attr($this->get_field_id('contributor')); ?>" name="<?php echo esc_attr($this->get_field_name('contributor')); ?>" type="hidden" value="<?php echo esc_attr($contributor); ?>" style="width:200px" />
            <ul class="searchUsersResults" style="display:none"></ul>
        </p></div>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('answered')); ?>">Answered filter:<select class="widefat" id="<?php echo esc_attr($this->get_field_id('answered')); ?>" name="<?php echo esc_attr($this->get_field_name('answered')); ?>">
            		<option value=""<?php if(!strlen($answered)) echo ' selected="selected"'; ?>>show all</option>
                    <option value="0"<?php if(strlen($answered) AND !$answered) echo ' selected="selected"'; ?>>show only unanswered</option>
                    <option value="1"<?php if(strlen($answered) AND $answered) echo ' selected="selected"'; ?>>show only answered</option>
                </select>
            </label>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('resolved')); ?>">Resolved filter:<select class="widefat" id="<?php echo esc_attr($this->get_field_id('resolved')); ?>" name="<?php echo esc_attr($this->get_field_name('resolved')); ?>">
            		<option value=""<?php if(!strlen($resolved)) echo ' selected="selected"'; ?>>show all</option>
                    <option value="0"<?php if(strlen($resolved) AND !$resolved) echo ' selected="selected"'; ?>>show only unresolved</option>
                    <option value="1"<?php if(strlen($resolved) AND $resolved) echo ' selected="selected"'; ?>>show only resolved</option>
                </select>
            </label>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('displayCategories')); ?>">Display categories:<select class="widefat" id="<?php echo esc_attr($this->get_field_id('displayCategories')); ?>" name="<?php echo esc_attr($this->get_field_name('displayCategories')); ?>">
                    <option value="0"<?php if(!$displayCategories) echo ' selected="selected"'; ?>>No</option>
                    <option value="1"<?php if($displayCategories) echo ' selected="selected"'; ?>>Yes</option>
                </select>
            </label>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('displayResolvedPrefix')); ?>">Display [RESOLVED] prefix:<select class="widefat" id="<?php echo esc_attr($this->get_field_id('displayResolvedPrefix')); ?>" name="<?php echo esc_attr($this->get_field_name('displayResolvedPrefix')); ?>">
                    <option value="0"<?php if(!$displayResolvedPrefix) echo ' selected="selected"'; ?>>No</option>
                    <option value="1"<?php if($displayResolvedPrefix) echo ' selected="selected"'; ?>>Yes</option>
                </select>
            </label>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('displayIcons')); ?>">Display icons:<select class="widefat" id="<?php echo esc_attr($this->get_field_id('displayIcons')); ?>" name="<?php echo esc_attr($this->get_field_name('displayIcons')); ?>">
                    <option value="0"<?php if(!$displayIcons) echo ' selected="selected"'; ?>>No</option>
                    <option value="1"<?php if($displayIcons) echo ' selected="selected"'; ?>>Yes</option>
                </select>
            </label>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('form')); ?>">Show Form: <select class="widefat" id="<?php echo esc_attr($this->get_field_id('form')); ?>" name="<?php echo esc_attr($this->get_field_name('form')); ?>">
                    <option value="0"<?php if(!$form) echo ' selected="selected"'; ?>>No</option>
                    <option value="1"<?php if($form) echo ' selected="selected"'; ?>>Yes</option>
                </select>
            </label>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('pagination')); ?>">Show Pagination: <select class="widefat" id="<?php echo esc_attr($this->get_field_id('pagination')); ?>" name="<?php echo esc_attr($this->get_field_name('pagination')); ?>">
                    <option value="0"<?php if(!$pagination) echo ' selected="selected"'; ?>>No</option>
                    <option value="1"<?php if($pagination) echo ' selected="selected"'; ?>>Yes</option>
                </select>
            </label>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('ajax')); ?>">AJAX support: <select class="widefat" id="<?php echo esc_attr($this->get_field_id('ajax')); ?>" name="<?php echo esc_attr($this->get_field_name('ajax')); ?>">
                    <option value="0"<?php if(!$ajax) echo ' selected="selected"'; ?>>No</option>
                    <option value="1"<?php if($ajax) echo ' selected="selected"'; ?>>Yes</option>
                </select>
            </label>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('formOnTop')); ?>">Show question form on top: <select class="widefat" id="<?php echo esc_attr($this->get_field_id('formOnTop')); ?>" name="<?php echo esc_attr($this->get_field_name('formOnTop')); ?>">
                    <option value="0"<?php if(!$formOnTop) echo ' selected="selected"'; ?>>No</option>
                    <option value="1"<?php if($formOnTop) echo ' selected="selected"'; ?>>Yes</option>
                </select>
            </label>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('displaySearch')); ?>">Show Search: <select class="widefat" id="<?php echo esc_attr($this->get_field_id('displaySearch')); ?>" name="<?php echo esc_attr($this->get_field_name('displaySearch')); ?>">
                    <option value="0"<?php if(!$displaySearch) echo ' selected="selected"'; ?>>No</option>
                    <option value="1"<?php if($displaySearch) echo ' selected="selected"'; ?>>Yes</option>
                </select>
            </label>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('displayTags')); ?>">Show Tags:
                <select class="widefat" id="<?php echo esc_attr($this->get_field_id('displayTags')); ?>" name="<?php echo esc_attr($this->get_field_name('displayTags')); ?>">
                    <option value="0"<?php if(!$displayTags) echo ' selected="selected"'; ?>>No</option>
                    <option value="1"<?php if($displayTags) echo ' selected="selected"'; ?>>Yes</option>
                </select>
            </label>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('displayViews')); ?>">Show Views:
                <select class="widefat" id="<?php echo esc_attr($this->get_field_id('displayViews')); ?>" name="<?php echo esc_attr($this->get_field_name('displayViews')); ?>">
                    <option value="0"<?php if(!$displayViews) echo ' selected="selected"'; ?>>No</option>
                    <option value="1"<?php if($displayViews) echo ' selected="selected"'; ?>>Yes</option>
                </select>
            </label>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('displayVotes')); ?>">Show Votes:
                <select class="widefat" id="<?php echo esc_attr($this->get_field_id('displayVotes')); ?>" name="<?php echo esc_attr($this->get_field_name('displayVotes')); ?>">
                    <option value="0"<?php if(!$displayVotes) echo ' selected="selected"'; ?>>No</option>
                    <option value="1"<?php if($displayVotes) echo ' selected="selected"'; ?>>Yes</option>
                </select>
            </label>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('displayAnswers')); ?>">Show Answers:
                <select class="widefat" id="<?php echo esc_attr($this->get_field_id('displayAnswers')); ?>" name="<?php echo esc_attr($this->get_field_name('displayAnswers')); ?>">
                    <option value="0"<?php if(!$displayAnswers) echo ' selected="selected"'; ?>>No</option>
                    <option value="1"<?php if($displayAnswers) echo ' selected="selected"'; ?>>Yes</option>
                </select>
            </label>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('displayUpdated')); ?>">Show Updated:
                <select class="widefat" id="<?php echo esc_attr($this->get_field_id('displayUpdated')); ?>" name="<?php echo esc_attr($this->get_field_name('displayUpdated')); ?>">
                    <option value="0"<?php if(!$displayUpdated) echo ' selected="selected"'; ?>>No</option>
                    <option value="1"<?php if($displayUpdated) echo ' selected="selected"'; ?>>Yes</option>
                </select>
            </label>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('displayAuthorInfo')); ?>">Show Author:
                <select class="widefat" id="<?php echo esc_attr($this->get_field_id('displayAuthorInfo')); ?>" name="<?php echo esc_attr($this->get_field_name('displayAuthorInfo')); ?>">
                    <option value="0"<?php if(!$displayAuthorInfo) echo ' selected="selected"'; ?>>No</option>
                    <option value="1"<?php if($displayAuthorInfo) echo ' selected="selected"'; ?>>Yes</option>
                </select>
            </label>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('displayStatusInfo')); ?>">Show Status:
                <select class="widefat" id="<?php echo esc_attr($this->get_field_id('displayStatusInfo')); ?>" name="<?php echo esc_attr($this->get_field_name('displayStatusInfo')); ?>">
                    <option value="0"<?php if(!$displayStatusInfo) echo ' selected="selected"'; ?>>No</option>
                    <option value="1"<?php if($displayStatusInfo) echo ' selected="selected"'; ?>>Yes</option>
                </select>
            </label>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('displayNavBar')); ?>">Show Navigation Bar:
                <select class="widefat" id="<?php echo esc_attr($this->get_field_id('displayNavBar')); ?>" name="<?php echo esc_attr($this->get_field_name('displayNavBar')); ?>">
                    <option value="0"<?php if(!$displayNavBar) echo ' selected="selected"'; ?>>No</option>
                    <option value="1"<?php if($displayNavBar) echo ' selected="selected"'; ?>>Yes</option>
                </select>
            </label>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('displayWrapperClass')); ?>">
                Container Class: <input class="widefat" id="<?php echo esc_attr($this->get_field_id('displayWrapperClass')); ?>" name="<?php echo esc_attr($this->get_field_name('displayWrapperClass')); ?>" type="text" value="<?php echo esc_attr($displayWrapperClass); ?>" />
            </label>
        </p>
        
        <style type="text/css">
            .searchUsersResults {margin:0;padding:0;list-style:none;margin-left:101px;margin-top:-15px;border:solid 1px #ccc;padding:5px;width:200px;}
            .searchUsersResults li {margin:0;padding:0; cursor: pointer;}
            .cma-user-remove {float: right;}
            </style>
            <script type="text/javascript">
            jQuery(function($) {
                
				$('.btn-user-remove').click(function() {
					var obj = $(this);
					obj.parents('div').find('input[type=hidden]').val('');
					obj.parents('label').find('.user-name').html('any');
					obj.parents('span').first().hide();
					return false;
				});

				var addHandler = function() {
					var item = $(this);
					var container = item.parents('div').first();
					container.find('input[type=hidden]').val(item.text());
					container.find('input[type=text]').val('');
					container.find('.user-name').text(item.text());
					container.find('.cma-user-remove').show();
					container.find('.searchUsersResults').hide();
				};
	            
				$('.cma-users-search').keyup(function() {
					var searchInput = $(this);
					var container = searchInput.parents('div').first();
					var resultsContainer = container.find('.searchUsersResults');
					var hiddenInput = container.find('input[type=hidden]');
					clearTimeout(this.searchTimer);
					this.searchTimer = setTimeout(function() {
						var search = $.trim(searchInput.val());
						if (search) {
							$.ajax({
								url: 'admin.php',
								data: {page: 'CMA_settings_search_users', q: search},
								success: function(data) {
									data = $.trim(data);
									resultsContainer.show();
									if (data.length == 0) resultsContainer.html('No results');
									else {
										var users = data.split("\n");
										for (var i=0; i<users.length; i++) {
											var item = $('<li/>').text(users[i]);
											item.click(addHandler);
											resultsContainer.append(item);
										}
									}
								}
							});
						}
					}, 500);
				});
            });
            </script>
        
        
        <?php
    }

    /**
     * Update widget options
     * @param WP_Widget $new_instance
     * @param WP_Widget $old_instance
     * @return WP_Widget
     */
    public function update($new_instance, $old_instance)
    {
        $instance                        = $old_instance;
        $instance['title']               = $new_instance['title'];
        $instance['limit']               = $new_instance['limit'];
        $instance['sort']                = $new_instance['sort'];
        $instance['order']               = $new_instance['order'];
        $instance['cat']                 = $new_instance['cat'];
        $instance['author']              = $new_instance['author'];
        $instance['contributor']         = $new_instance['contributor'];
        $instance['answered']            = $new_instance['answered'];
        $instance['resolved']            = $new_instance['resolved'];
        $instance['form']                = $new_instance['form'];
        $instance['ajax']                = $new_instance['ajax'];
        $instance['formOnTop']           = $new_instance['formOnTop'];
        $instance['pagination']          = $new_instance['pagination'];
        $instance['displayCategories']   = $new_instance['displayCategories'];
        $instance['displayResolvedPrefix']   = $new_instance['displayResolvedPrefix'];
        $instance['displayIcons']        = $new_instance['displayIcons'];
        $instance['displaySearch']       = $new_instance['displaySearch'];
        $instance['displayTags']         = $new_instance['displayTags'];
        $instance['displayVotes']        = $new_instance['displayVotes'];
        $instance['displayViews']        = $new_instance['displayViews'];
        $instance['displayAnswers']      = $new_instance['displayAnswers'];
        $instance['displayUpdated']      = $new_instance['displayUpdated'];
        $instance['displayAuthorInfo']   = $new_instance['displayAuthorInfo'];
        $instance['displayStatusInfo']   = $new_instance['displayStatusInfo'];
        $instance['displayNavBar']   = $new_instance['displayNavBar'];
        $instance['displayWrapperClass'] = $new_instance['displayWrapperClass'];
        return $instance;
    }

    /**
     * Render widget
     *
     * @param array $args
     * @param WP_Widget $instance
     */
    public function widget($args, $instance)
    {
        extract($args, EXTR_SKIP);

        echo $before_widget;
        $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);

        if(!empty($title))
        {
            echo $before_title . $title . $after_title;
        }

        foreach($instance as $instanceKey => $intanceValue)
        {
            if(preg_match('/(display)(\w+)/', $instanceKey, $matches) == 1)
            {
                $newKey            = strtolower($matches[2]);
                $instance[$newKey] = $intanceValue;
                $instance['display' . $newKey] = $intanceValue;
                unset($instance[$instanceKey]);
            }
            if (strlen($intanceValue) == 0) {
				unset($instance[$instanceKey]);
			}
        }

        $instance['tiny'] = true;

        // WIDGET CODE GOES HERE
        echo CMA_Shortcodes::shortcode_questions($instance, true);
        echo $after_widget;
    }

}

add_action('widgets_init', array('CMA_QuestionsWidget', 'register'));
