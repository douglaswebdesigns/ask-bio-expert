<?php class SubmitWidget extends WP_Widget
{
    function __construct(){
		$widget_ops = array('description' => 'Displays Submit Button');
		$control_ops = array('width' => 400, 'height' => 300);
		parent::__construct(false,$name='ET Submit Button Widget',$widget_ops,$control_ops);
    }

  /* Displays the Widget in the front-end */
    function widget($args, $instance){
		extract($args);
		$aboutText = empty($instance['aboutText']) ? '' : $instance['aboutText'];
?>
<div class="widget-button">
	<a href="<?php echo esc_url( get_permalink( get_pageId( get_option('askit_answer_page') ) ) ); ?>" id="call_to_action"><span><?php echo esc_html( $aboutText ); ?></span></a>
</div>
<?php
	}

  /*Saves the settings. */
    function update($new_instance, $old_instance){
		$instance = $old_instance;
		$instance['aboutText'] = sanitize_text_field( $new_instance['aboutText'] );

		return $instance;
	}

  /*Creates the form for the widget in the back-end. */
    function form($instance){
		//Defaults
		$instance = wp_parse_args( (array) $instance, array('aboutText'=>'Submit a Question') );

		$aboutText = esc_attr( $instance['aboutText'] );

		# Title
		echo '<p><label for="' . $this->get_field_id('aboutText') . '">' . 'Button Text:' . '</label><input class="widefat" id="' . $this->get_field_id('aboutText') . '" name="' . $this->get_field_name('aboutText') . '" type="text" value="' . $aboutText . '" /></p>';
	}

}// end SubmitWidget class

function SubmitWidgetInit() {
	register_widget('SubmitWidget');
}

add_action('widgets_init', 'SubmitWidgetInit'); ?>