<?php
$accessType = isset( $subscriptionMetas['access_type']  ) ? $subscriptionMetas['access_type']  : $membershipData['access_type'];
$price = isset( $subscriptionMetas['price'] ) ? $subscriptionMetas['price'] : $membershipData['price'];
switch ( $accessType ){
    case 'regular_period':
      $trialPrice = isset( $subscriptionMetas['access_trial_price']  ) ? $subscriptionMetas['access_trial_price']  : $membershipData['access_trial_price'];
      $trialType = isset( $subscriptionMetas['access_trial_type']  ) ? $subscriptionMetas['access_trial_type']  : $membershipData['access_trial_type'];
      $timeType = isset( $subscriptionMetas['access_regular_time_type']  ) ? $subscriptionMetas['access_regular_time_type']  : $membershipData['access_regular_time_type'];
      $timeValue = isset( $subscriptionMetas['access_regular_time_value']  ) ? $subscriptionMetas['access_regular_time_value']  : $membershipData['access_regular_time_value'];
      if ( $trialPrice != '' ){
          //  trial
          if ( $trialType == 1 ){
              // certain period
              $trialTimeValue = isset( $subscriptionMetas['access_trial_time_value']  ) ? $subscriptionMetas['access_trial_time_value']  : $membershipData['access_trial_time_value'];
              $trialTimeType = isset( $subscriptionMetas['access_trial_time_type']  ) ? $subscriptionMetas['access_trial_time_type']  : $membershipData['access_trial_time_type'];
              echo ihc_format_price_and_currency( $currency, $trialPrice ) . esc_html__( ' for ', 'ihc' )  . $trialTimeValue . ihcGetTimeTypeByCode( $trialTimeType, $trialTimeValue ) .
               esc_html__( ' then ', 'ihc' );
          } else {
              // couple of cycles
              $trialCycles = isset( $subscriptionMetas['access_trial_couple_cycles']  ) ? $subscriptionMetas['access_trial_couple_cycles']  : $membershipData['access_trial_couple_cycles'];
              echo ihc_format_price_and_currency( $currency, $trialPrice ) . esc_html__( ' for ', 'ihc' ) . $trialCycles . esc_html__( ' cycles then ', 'ihc' );
          }
      }
          if ( $timeValue == 1 ){
              echo ihc_format_price_and_currency( $currency, $price ) . esc_html__( ' every ', 'ihc' ) . ihcGetTimeTypeByCode( $timeType, $timeValue );
          } else {
              echo ihc_format_price_and_currency( $currency, $price ) . esc_html__( ' for ', 'ihc' ) . $timeValue . ihcGetTimeTypeByCode( $timeType, $timeValue );
          }

      break;
    case 'unlimited':
      echo ihc_format_price_and_currency( $currency, $price );
      esc_html_e( ' for LifeTime.', 'ihc' );
      break;
    case 'limited':
      $timeValue = isset( $subscriptionMetas['access_limited_time_value']  ) ? $subscriptionMetas['access_limited_time_value']  : $membershipData['access_limited_time_value'];
      $timeType = isset( $subscriptionMetas['access_limited_time_type']  ) ? $subscriptionMetas['access_limited_time_type']  : $membershipData['access_limited_time_type'];
      echo ihc_format_price_and_currency( $currency, $price );
      echo esc_html__( ' for ', 'ihc' ) . $timeValue . ' ' . ihcGetTimeTypeByCode( $timeType, $timeValue );
      break;
    case 'date_interval':
      $start = isset( $subscriptionMetas['access_interval_start']  ) ? $subscriptionMetas['access_interval_start']  : $membershipData['access_interval_start'];
      $end = isset( $subscriptionMetas['access_interval_end']  ) ? $subscriptionMetas['access_interval_end']  : $membershipData['access_interval_end'];
      echo ihc_format_price_and_currency( $currency, $price );
      esc_html_e( ' for period: ', 'ihc' );
      echo ihc_convert_date_time_to_us_format( $start );
      echo esc_html(' - ');
      echo ihc_convert_date_time_to_us_format( $end );
      break;
}
