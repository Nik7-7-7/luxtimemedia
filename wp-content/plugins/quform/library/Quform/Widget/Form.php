<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Widget_Form extends WP_Widget
{
    /**
     * @var Quform_Repository
     */
    protected $repository;

    /**
     * @var Quform_Form_Controller
     */
    protected $controller;

    /**
     * @var array
     */
    protected $defaults;

    public function __construct()
    {
        $this->repository = Quform::getService('repository');
        $this->controller = Quform::getService('formController');

        $this->defaults = array(
            'title' => '',
            'id' => '',
            'show_title' => true,
            'show_description' => true
        );

        $options = array(
            'description' => __('Display one of your created forms.', 'quform'),
            'classname'   => 'quform-widget'
        );

        parent::__construct('quform-widget', Quform::getPluginName(), $options);
    }

    /**
     * Display the widget
     *
     * @param  array  $args      Display arguments
     * @param  array  $instance  The settings for this widget instance
     */
    public function widget($args, $instance)
    {
        $instance = wp_parse_args((array) $instance, $this->defaults);

        echo $args['before_widget'];

        $title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);

        if (Quform::isNonEmptyString($title)) {
            echo $args['before_title'] . $title . $args['after_title'];
        }

        $options = array(
            'show_title' => $instance['show_title'],
            'show_description' => $instance['show_description'],
            'id' => $instance['id']
        );

        echo $this->controller->form($options);

        echo $args['after_widget'];
    }

    /**
     * Update the widget settings
     *
     * @param   array  $new_instance  New settings for this widget instance
     * @param   array  $old_instance  Old settings for this widget instance
     * @return  array                 New settings for this widget instance
     */
    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = sanitize_text_field($new_instance['title']);
        $instance['id'] = is_numeric($new_instance['id']) ? (int) $new_instance['id'] : '';
        $instance['show_title'] = ! empty($new_instance['show_title']);
        $instance['show_description'] = ! empty($new_instance['show_description']);

        return $instance;
    }

    /**
     * Display the widget settings form
     *
     * @param   array  $instance  The current settings for this widget instance
     * @return  null
     */
    public function form($instance)
    {
        $instance = wp_parse_args((array) $instance, $this->defaults);
        $orderBy = get_user_meta(get_current_user_id(), 'quform_forms_order_by', true);
        $order = get_user_meta(get_current_user_id(), 'quform_forms_order', true);
        $forms = $this->repository->formsToSelectArray(null, $orderBy, $order);

        if ( ! count($forms)) {
            if (current_user_can('quform_add_forms')) {
                /* translators: %1$s: open link tag, %2$s: close link tag */
                printf('<p>' . esc_html__('No forms yet, %1$sclick here to create one%2$s.', 'quform') . '</p>', '<a href="' . esc_url(admin_url('admin.php?page=quform.forms&sp=add')) . '">', '</a>');
            } else {
                echo '<p>' . esc_html__('No forms yet.', 'quform') . '</p>';
            }
            return;
        }
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Title:', 'quform'); ?></label>
            <input
                type="text"
                class="widefat"
                id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                name="<?php echo esc_attr($this->get_field_name('title')); ?>"
                value="<?php echo Quform::escape($instance['title']); ?>">
            <br>
            <small><?php esc_html_e('The title of the widget.', 'quform'); ?></small>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('id')); ?>"><?php esc_html_e('Form:', 'quform'); ?></label>
            <select
                id="<?php echo esc_attr($this->get_field_id('id')); ?>"
                name="<?php echo esc_attr($this->get_field_name('id')); ?>"
                class="widefat">
                <option value=""><?php esc_html_e('Select a form', 'quform'); ?></option>
                <?php foreach ($forms as $id => $name) : ?>
                    <option value="<?php echo Quform::escape($id); ?>" <?php selected($instance['id'], $id); ?>><?php echo Quform::escape($name); ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <input
                type="checkbox"
                id="<?php echo esc_attr($this->get_field_id('show_title')); ?>"
                name="<?php echo esc_attr($this->get_field_name('show_title')); ?>"
                value="1"
                <?php checked($instance['show_title']); ?>>
            <label for="<?php echo esc_attr($this->get_field_id('show_title')); ?>"><?php esc_html_e('Show form title', 'quform'); ?></label>
            <br>
            <input
                type="checkbox"
                id="<?php echo esc_attr($this->get_field_id('show_description')); ?>"
                name="<?php echo esc_attr($this->get_field_name('show_description')); ?>"
                value="1"
                <?php checked($instance['show_description']); ?>>
            <label for="<?php echo esc_attr($this->get_field_id('show_description')); ?>"><?php esc_html_e('Show form description', 'quform'); ?></label>
        </p>
        <?php
    }

    /**
     * Register the widget with WordPress
     */
    public static function register()
    {
        register_widget(__CLASS__);
    }
}
