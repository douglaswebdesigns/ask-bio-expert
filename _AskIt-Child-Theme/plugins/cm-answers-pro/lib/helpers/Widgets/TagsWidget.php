<?php
class CMA_TagsWidget extends WP_Widget
{
	
	const DEFAULT_TITLE = 'Popular Tags';
	const DEFAULT_LIMIT = 10;

    public function __construct()
    {
        $widget_ops = array('classname' => 'CMA_TagsWidget', 'description' => 'Show CM Tags');
        parent::__construct('CMA_TagsWidget', 'CMA Tags', $widget_ops);
    }

    public static function getInstance()
    {
        return register_widget(get_class());
    }

    /**
     * Widget options form
     * @param WP_Widget $instance
     */
    public function form($instance)
    {
        if(isset($instance['title']))
        {
            $title = $instance['title'];
        }
        else
        {
            $title = __(self::DEFAULT_TITLE, 'cm-answers-pro');
        }
        $limit = isset($instance['limit']) ? $instance['limit'] : self::DEFAULT_LIMIT;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_name('title')); ?>"><?php _e('Title:'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_name('limit')); ?>"><?php _e('Count:'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('limit')); ?>" name="<?php echo esc_attr($this->get_field_name('limit')); ?>" type="text" value="<?php echo esc_attr($limit); ?>" />
        </p>

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
        $instance          = array();
        $instance['title'] = (!empty($new_instance['title']) ) ? strip_tags($new_instance['title']) : '';
        $instance['limit'] = (!empty($new_instance['limit']) ) ? strip_tags($new_instance['limit']) : self::DEFAULT_LIMIT;

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

        if (empty($instance['title'])) $instance['title'] = CMA::__(self::DEFAULT_TITLE);
        if (empty($instance['limit'])) $instance['limit'] = self::DEFAULT_LIMIT;
        
        $title = apply_filters('widget_title', $instance['title']);
        $limit = $instance['limit'];

        echo $before_widget;
        if(!empty($title)) echo $before_title . $title . $after_title;
        ?>
        <div class="cma-tags-container">
            <?php
            $terms = self::get_terms_by_post_type(array('post_tag'), array('cma_thread'));
            if(!empty($terms))
            {
                $qs = '?';
                if(isset($_GET["sort"])) $qs .= 'sort=' . urlencode($_GET["sort"]) . "&";
                if(isset($_GET["s"])) $qs .= 's=' . urlencode($_GET["s"]) . "&";
                foreach($terms as $term)
                {
                	$url = get_post_type_archive_link(CMA_Thread::POST_TYPE) . $qs . 'cmatag=' . urlencode($term->slug);
                    printf('<div><a href="%s">%s</a> <span>(%d)</span></div>',
	        			esc_attr($url),
	        			esc_html($term->name),
	        			esc_html($term->cnt)
        			);
                    if(--$limit <= 0) break;
                }
            }
            else
            {
                echo 'No tags';
            }
            ?>
        </div>
        <?php
        echo $after_widget;
    }

    static public function get_terms_by_post_type($taxonomies, $post_types)
    {
        global $wpdb;
        $query   = $wpdb->prepare("SELECT t.*, COUNT(*) as cnt from $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id INNER JOIN $wpdb->term_relationships AS r ON r.term_taxonomy_id = tt.term_taxonomy_id INNER JOIN $wpdb->posts AS p ON p.ID = r.object_id WHERE p.post_type IN('%s') AND tt.taxonomy IN('%s') AND p.post_status='publish' GROUP BY t.term_id ORDER BY cnt DESC", join("', '", $post_types), join("', '", $taxonomies));
        $results = $wpdb->get_results($query);
        return $results;
    }

}


function cma_register_tags_widget()
{
    register_widget('CMA_TagsWidget');
}
add_action('widgets_init', 'cma_register_tags_widget');
