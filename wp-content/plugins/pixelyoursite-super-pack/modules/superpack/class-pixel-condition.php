<?php

namespace PixelYourSite\SuperPack;

use function Aws\map;
use function PixelYourSite\isEddActive;
use function PixelYourSite\isWooCommerceActive;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

add_action( 'wp_ajax_pys_filter_condition_autocomplete', '\PixelYourSite\SuperPack\SpPixelCondition::ajax_posts_filter_autocomplete' );

class SpPixelCondition {
    private static $_instance = null;

    public static function instance() {

        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    /**
     * @var SpCondition[]
     */
    private $conditions = [];
    /**
     * @var SpCondition[]
     */
    private $allConditions = [];
    private $allOptions = [];

    private function __construct() {
       add_action( 'init', array( $this, 'init' ), 9 );
    }

    function init() {
        $all = new SpAllSiteCondition();
        $single = new SpSingularCondition();
        $archive = new SpArchiveCondition();
        $post_types = new SpPostTypesCondition();


        $this->conditions[] = $all;
        $this->conditions[] = $single;
        $this->conditions[] = $archive;
        $this->conditions[] = $post_types;


        $this->registerCondition($all);
        $this->registerCondition($single);
        $this->registerCondition($archive);
        $this->registerCondition($post_types);


        if(isWooCommerceActive()) {
            $woo = new SpWooCondition();
            $this->conditions[] = $woo;
            $this->registerCondition($woo);
        }

        if(isEddActive()) {
            $edd = new SpEddCondition();
            $this->conditions[] = $edd;
            $this->registerCondition($edd);
        }
    }



    /**
     * @param SpCondition $condition
     */
    public function registerCondition($condition) {
        $this->allConditions[$condition->get_name()] = $condition;
    }

	public function registerOption($option) {
		$this->allOptions[$option->get_name()] = $option;
	}

	/**
	 * @param $name
	 * @return false|SpCondition
	 */
	public function getCondition($name) {
		if(isset($this->allConditions[$name])) {
			return $this->allConditions[$name];
		}
		return false;
	}

	public function getOption($name) {
		if(isset($this->allOptions[$name])) {
			return $this->allOptions[$name];
		}
		return false;
	}

    public function renderHtml($definedCondition = []) {

		if ( empty( $definedCondition ) ) {
			$conditions = SPPixelId::fromArray( array() );
			$definedCondition = $conditions->displayConditions;
		}

        $conditionData = [];
        foreach ( $this->allConditions as $condition) {
            $sub_conditions = [];
            foreach ($condition->get_sub_conditions() as $sub) {
                $sub_conditions[] = $sub->get_name();
            }
            $conditionData[$condition->get_name()] = [
                "controls" => $condition->get_controls() ,
                "label" => $condition->get_label(),
                "sub_conditions"=> $sub_conditions,
                "all_label" => $condition->get_all_label()
            ];
        }

        ?>
        <script>
            var conditions = <?=json_encode($conditionData)?>;
        </script>
        <?php foreach ($definedCondition as $data) { ?>
            <div class="pixel_conditions" data-params='[<?php echo json_encode($data); ?>]'>
                <div class="row">
                    <div class="col-11 pys_flex_block">
                        <select  class="condition_select condition"  data-name="name">
                            <?php foreach ($this->conditions as $condition) :?>
                                <option value="<?php echo $condition->get_name(); ?>"><?php echo $condition->get_label(); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-1">
                        <button type="button" class="btn btn-sm remove-conditions-row <?= count($definedCondition) == 1 ? 'hidden' : '' ?>">
                            <i class="fa fa-trash-o" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </div>
        <?php } ?>
        <div class="pixel_conditions hidden_pixel_conditions" data-params='[]' style="display: none;">
            <div class="row">
                <div class="col-11 pys_flex_block">
                    <select  class="condition_select condition"  data-name="name">
                        <?php foreach ($this->conditions as $condition) :?>
                            <option value="<?=$condition->get_name()?>"><?=$condition->get_label()?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-1">
                    <button type="button" class="btn btn-sm remove-conditions-row">
                        <i class="fa fa-trash-o" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

        </div>
        <div class="row my-3">

            <div class="col-12">
                <button class="btn btn-sm btn-primary pys_superpack_add_conditions_pixel_id" type="button">
                    Add Extra Condition
                </button>
            </div>
        </div>
    <?php
    }

    /**
     * @param array $data
     *
     * @return array
     * @throws \Exception
     */
    public static function ajax_posts_filter_autocomplete( ) {

        $results = [];
        $queryData = $_POST['query'];
        switch ( $queryData['object'] ) {
            case 'tax':
                $by_field = ! empty( $_POST['by_field'] ) ? $_POST['by_field'] : 'term_taxonomy_id';

                $query_args = [
                    'taxonomy' => $queryData['query']['taxonomy'],
                    'hide_empty' => false,
                    'search' => $_POST['q']
                ];

                $terms = get_terms( $query_args );
                if ( is_wp_error( $terms ) ) {
                    break;
                }
                foreach ( $terms as $term ) {
                    $results[] = [
                        'id' => $term->{$by_field},
                        'text' => $term->name,
                    ];
                }
                break;
            case 'post':
                $query_args = [
                    'post_type' => $queryData['query']['post_type'],
                    'posts_per_page' => -1,
                    's' => $_POST['q']
                ];
                $query = new \WP_Query( $query_args );

                foreach ( $query->posts as $post ) {
                    $text = $post->post_title;
                    $results[] = [
                        'id' => $post->ID,
                        'text' => $text,
                    ];
                }
                break;

        }

        wp_send_json_success([
            'results' => $results,
        ],200);

}


}

/**
 * @return SpPixelCondition
 */
function SpPixelCondition() {
    return SpPixelCondition::instance();
}

SpPixelCondition();