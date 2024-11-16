<?php

if ( ! defined( 'ABSPATH' ) ){
   exit;
}

class Elementor_Ihc_Locker_Widget extends \Elementor\Widget_Base
{

  public function get_name()
  {
      return 'ump-locker-shortcode';
  }

  public function get_title()
  {
      return esc_html__( 'UMP - Inside Locker', 'ihc' );
  }

  public function get_icon()
  {
      return 'fa fa-code';
  }

  protected function render()
  {
      $settings = $this->get_settings_for_display();
      if ( is_array($settings['target']) ){
          $target = implode(',', $settings['target']);
      } else {
          $target = '';
      }

      echo esc_ump_content('[ihc-hide-content ihc_mb_type="' . $settings['type'] . '" ihc_mb_who="' . $target . '" ihc_mb_template="' . $settings['template'] . '" ]' . $settings['the_content'] . '[/ihc-hide-content]');
  }

  protected function _register_controls()
  {

      $this->start_controls_section(
        'section_editor',
        [
          'label' => esc_html__( 'UMP - Register Popup', 'ihc' ),
        ]
      );

      $this->add_control(
        'the_content',
        [
          'label' => esc_html__('Content', 'ihc'),
          'type' => \Elementor\Controls_Manager::WYSIWYG,
          'dynamic' => [
            'active' => true,
          ],
          'default' => '',
        ]
      );
      $this->add_control(
        'type',
        [
          'label' => esc_html__('Show', 'ihc'),
          'type' => \Elementor\Controls_Manager::SELECT,
          'dynamic' => [
            'active' => true,
          ],
          'options' => array( 'show' => esc_html__('Show Content Only For', 'ihc'), 'block' => esc_html__('Hide Content Only For', 'ihc') ),
          'default' => '',
        ]
      );

      $options = array( 'all'=>esc_html__('All', 'ihc'), 'reg'=>esc_html__('Registered Users', 'ihc'), 'unreg'=>esc_html__('Unregistered Users', 'ihc') );
      $levels = \Indeed\Ihc\Db\Memberships::getAll();

      if ($levels){
        foreach($levels as $id=>$level){
          $options[$id] = $level['name'];
        }
      }
      $this->add_control(
        'target',
        [
          'label' => esc_html__('Target', 'ihc'),
          'type' => \Elementor\Controls_Manager::SELECT2,
          'dynamic' => [
            'active' => true,
          ],
          'multiple'  => true,
          'options' => $options,
          'default' => '',
        ]
      );

      $lockers = ihc_return_meta('ihc_lockers');
      $options = array();
      if ( !empty($lockers) ){
          foreach ($lockers as $k=>$v){
              $options[ $k ] = $v['ihc_locker_name'];
          }
      }
      $this->add_control(
        'template',
        [
          'label' => esc_html__('Template', 'ihc'),
          'type' => \Elementor\Controls_Manager::SELECT,
          'dynamic' => [
            'active' => true,
          ],
          'options' => $options,
          'default' => '',
        ]
      );

      $this->end_controls_section();

  }

}
