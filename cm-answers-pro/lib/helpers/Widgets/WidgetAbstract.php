<?php

class CMA_WidgetAbstract extends WP_Widget {
	
	const WIDGET_TITLE = '';
	const WIDGET_DESCRIPTION = '';
	
	static protected $widgetFields = array();
	
	
	public static function init() {
		add_action('widgets_init', array(get_called_class(), 'registerWidget'));
	}
	
	
	static function registerWidget() {
		register_widget(get_called_class());
	}
	
	public function __construct( $id_base = null, $name = null, $widget_options = array(), $control_options = array() ) {
		parent::__construct(get_called_class(), $this->getWidgetTitle(), $this->getWidgetOptions(), $this->getWidgetControlOptions());
	}
	
	
	function getWidgetTitle() {
		return static::WIDGET_TITLE;
	}
	
	function getWidgetDescription() {
		return static::WIDGET_DESCRIPTION;
	}
	
	function getWidgetOptions() {
		return array('classname' => get_called_class(), 'description' => $this->getWidgetDescription());
	}
	
	function getWidgetControlOptions() {
		return array();
	}
	
	

	/**
	 * Widget options form
	 * @param WP_Widget $instance
	 */
	public function form($instance) {
	
		$templates = array(
			CMA_Settings::TYPE_STRING => '<p><label for="%(nameAttr)s">%(label)s</label><input type="text" name="%(nameAttr)s" value="%(valueAttr)s" class="widefat" id="%(idAttr)s" /></p>',
			CMA_Settings::TYPE_BOOL => '<p><label><input type="checkbox" name="%(nameAttr)s" value="%(valueAttr)s" id="%(idAttr)s"%(checked)s /> %(label)s</label></p>',
		);
		
		foreach (static::$widgetFields as $name => $config) {
			if (!isset($instance[$name])) {
				if (is_array($config['default']) AND is_callable($config['default'])) {
					$value = call_user_func($config['default']);
				} else {
					$value = $config['default'];
				}
			} else {
				$value = $instance[$name];
			}
			$valueAttr = esc_attr($value);
			$templateValues = array(
				'label' => $config['label'],
				'nameAttr' => esc_attr($this->get_field_name($name)),
				'idAttr' => esc_attr($this->get_field_id($name)),
				'valueAttr' => esc_attr($value),
			);
			if ($config['type'] == CMA_Settings::TYPE_BOOL) {
				$templateValues['checked'] = checked($config['default'], intval($value), false);
				$templateValues['valueAttr'] = 1;
			}
			if (isset($templates[$config['type']])) {
				echo self::vsprintf_named($templates[$config['type']], $templateValues);
			}
		}
		
	}
	

	/**
	 * Update widget options
	 *
	 * @param WP_Widget $new_instance
	 * @param WP_Widget $old_instance
	 * @return WP_Widget
	 */
	public function update($new_instance, $old_instance)
	{
		$instance = array();
		foreach (static::$widgetFields as $name => $config) {
			$instance[$name] = (isset($new_instance[$name]) ) ? $new_instance[$name] : '';
		}
		return $instance;
	}
	
	
	/**
	 * Render widget
	 * 
	 * @see WP_Widget::widget()
	 */
	public function widget($args, $instance) {
		extract($args, EXTR_SKIP);
		$title = apply_filters('widget_title', $this->getInstanceTitle($instance));
		echo $before_widget;
		if(!empty($title)) echo $before_title . $title . $after_title;
		echo $this->getWidgetContent($instance);
		echo $after_widget;
	}
	
	
	function getInstanceTitle($instance) {
		if (isset($instance['title'])) return $instance['title'];
	}
	
	
	function getWidgetContent($instance) {
		
	}
	
	
	static function vsprintf_named($format, $args) {
	    $names = preg_match_all('/%\((.*?)\)/', $format, $matches, PREG_SET_ORDER);
	
	    $values = array();
	    foreach($matches as $match) {
	        $values[] = $args[$match[1]];
	    }
	
	    $format = preg_replace('/%\((.*?)\)/', '%', $format);
	    return vsprintf($format, $values);
	}
	
	
	
}
