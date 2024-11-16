<?php
namespace PixelYourSite\SuperPack;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}


class SpSingularCondition extends SpCondition
{

    public function register_sub_conditions()
    {

        $condition = new SpHomeCondition();
        $this->sub_conditions[] = $condition;
        SpPixelCondition()->registerCondition($condition);


        $post_types = get_public_post_types();
        // Product form WooCommerce are handled separately.
        if ( class_exists( 'woocommerce' ) ) {
            unset( $post_types['product'] );
        }
        foreach ($post_types as $post_type => $label) {
            $condition = new SpPostCondition([
                'post_type' => $post_type,
            ]);
            $this->sub_conditions[] = $condition;
            SpPixelCondition()->registerCondition($condition);

        }
    }

    public function get_label()
    {
        return 'Singular';
    }

    public function get_name()
    {
        return 'singular';
    }

    public function check($args)
    {
        $status = ( is_singular() && ! is_embed() ) || is_404();
        return $status;
    }

    public function get_all_label()
    {
        return 'All Singular';
    }

    public function get_controls()
    {
        $options = [];
        foreach ($this->sub_conditions as $condition) {
            $title =  $condition->get_label();
            $items = [$condition->get_name()];
            foreach ($condition->get_sub_conditions() as $sub) {
                $items[] = $sub->get_name();
            }
            $options[] = [
                    'title' => $title,
                    'items'=>$items
            ];
        }
        return [
                'type'=>'select_titled',
                'name' => 'sub_name',
                'options'=>$options
        ];
        ?>
<?php
    }
}