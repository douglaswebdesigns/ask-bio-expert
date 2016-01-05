<?php

class CMA_LoginWidget extends WP_Widget
{

    public function __construct()
    {
        $widget_ops = array('classname' => 'CMA_LoginWidget', 'description' => 'Show CM login widget');
        parent::__construct('CMA_LoginWidget', 'CM Answers Login Widget', $widget_ops);
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
            $title = CMA::__('Login');
        }
        $limit = isset($instance['limit']) ? $instance['limit'] : 10;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_name('title')); ?>"><?php _e('Title:'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
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
    	
    	if (is_user_logged_in()) return;
    	
        extract($args, EXTR_SKIP);

        $title = apply_filters('widget_title', $instance['title']);
		
        echo $before_widget;
        if(!empty($title)) echo $before_title . $title . $after_title;
        ?>
        <div class="cma-login-container">
            <?php do_action('CMA_login_form', array('widget' => true)); ?>
        </div>
        <?php
        echo $after_widget;
    }
    
    
    static function register() {
		register_widget(__CLASS__);
	}

}


add_action('widgets_init', array('CMA_LoginWidget', 'register'));
