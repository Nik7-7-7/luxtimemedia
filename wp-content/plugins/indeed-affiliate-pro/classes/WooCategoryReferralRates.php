<?php
namespace Indeed\Uap;

class WooCategoryReferralRates
{
    /**
     * @param none
     * @return none
     */
    public function __construct()
    {
        // Admin
        add_action( 'product_cat_add_form_fields', [ $this, 'catReferralRateFormAdd' ], 999, 2 );
        add_action( 'product_cat_edit_form_fields', [ $this, 'catReferralRateFormEdit' ], 999 );
        add_action( 'edited_product_cat', [ $this, 'saveReferralRate' ], 999, 1 );
        add_action( 'create_product_cat', [ $this, 'saveReferralRate' ], 999, 1 );

        // Public
        add_filter( 'uap_filter_referral_amount', [ $this, 'filterAmount'], 2, 4 );
    }

    public function catReferralRateFormAdd( $cat=0 )
    {
        $currency = get_option('uap_currency');
        $catId = isset( $cat->term_id ) ? $cat->term_id : 0;
        $types = [
                          'flat' 					=> esc_html__( 'Flat ', 'uap') . '(' . $currency .')',
                          'percentage'		=> esc_html__( 'Percentage ', 'uap') . '(%)',
                          'default'       => esc_html__( 'Default Affiliate system Settings', 'uap' ),
        ];
        $data = [
                  'uap_referral_type'		    => get_term_meta( $catId, 'uap_referral_type', true ),
                  'uap_referral_value'		  => get_term_meta( $catId, 'uap_referral_value', true ),
                  'types'                   => $types,
                  'cat_id'                  => $catId,
                  'uap_excluded'            => get_term_meta( $catId, 'uap_excluded', true ),
        ];
        if ( $data['uap_excluded'] === '' || $data['uap_excluded'] === false || $data['uap_excluded'] === null ){
            $data['uap_excluded'] = 0;
        }
        
        $view = new \Indeed\Uap\IndeedView();
        $output = $view->setTemplate( UAP_PATH . 'admin/views/woo-cats-referral-rate-add.php' )
                  ->setContentData( $data )
                  ->getOutput();
        echo esc_uap_content( $output );
    }


    public function catReferralRateFormEdit( $cat=0 )
    {
        $currency = get_option('uap_currency');
        $catId = isset( $cat->term_id ) ? $cat->term_id : 0;
        $types = [
                          'flat' 					=> esc_html__( 'Flat ', 'uap') . '(' . $currency .')',
                          'percentage'		=> esc_html__( 'Percentage ', 'uap') . '(%)',
                          'default'       => esc_html__( 'Default Affiliate system Settings', 'uap' ),
        ];
        $data = [
                  'uap_referral_type'		    => get_term_meta( $catId, 'uap_referral_type', true ),
                  'uap_referral_value'		  => get_term_meta( $catId, 'uap_referral_value', true ),
                  'types'                   => $types,
                  'cat_id'                  => $catId,
                  'uap_excluded'            => get_term_meta( $catId, 'uap_excluded', true ),
        ];
        if ( $data['uap_excluded'] === '' || $data['uap_excluded'] === false || $data['uap_excluded'] === null ){
            $data['uap_excluded'] = 0;
        }

        $view = new \Indeed\Uap\IndeedView();
        $output = $view->setTemplate( UAP_PATH . 'admin/views/woo-cats-referral-rate-edit.php' )
                  ->setContentData( $data )
                  ->getOutput();
        echo esc_uap_content( $output );
    }


    public function saveReferralRate( $catId=0 )
    {
        if ( empty( $catId ) || !isset( $_POST['uap_referral_type'] ) || !isset( $_POST['uap_referral_value'] ) ){
            return false;
        }
        update_term_meta( $catId, 'uap_referral_type', uap_sanitize_array( $_POST['uap_referral_type'] ) );
        update_term_meta( $catId, 'uap_referral_value', uap_sanitize_array( $_POST['uap_referral_value'] ) );
        if ( isset( $_POST['uap_excluded'] ) ){
            update_term_meta( $catId, 'uap_excluded', sanitize_text_field( $_POST['uap_excluded'] ) );
        }
    }

    public function filterAmount( $customAmounts=[], $inputAmount=0, $productId=0, $attr=[] )
    {
        $cats = wp_get_post_terms ($productId, 'product_cat', [ 'fields'=>'ids' ] );
        if ( empty( $cats ) ){
            return $customAmounts;
        }
        $array= [];
        foreach ( $cats as $catId ){
          $type = get_term_meta( $catId, 'uap_referral_type', true );
          $value = get_term_meta( $catId, 'uap_referral_value', true );

          if ( $type == false ){
              continue;
          }
          if ( $type == 'default' ){
              continue;
          }
          if ( $value === false  || $value == '' ){
              continue;
          }
          if ( $type == 'flat' ){
              $customAmounts[] = $value;
          } else if ( $type == 'percentage' ){
              $customAmounts[] = $inputAmount * $value / 100;
          }
        }
        return $customAmounts;
    }

}
