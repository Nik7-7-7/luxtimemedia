<?php
function ihc_print_subscription_layout($template, $levels, $register_url, $custom_css='', $payment_select=FALSE){
	$str = '';

	if (!$custom_css){
		$custom_css = get_option('ihc_select_level_custom_css');
	}
	if (!empty($custom_css)){
		wp_register_style( 'dummy-handle', false );
		wp_enqueue_style( 'dummy-handle' );
		wp_add_inline_style( 'dummy-handle', stripslashes($custom_css) );
	}

	$str .= '<div class="ich_level_wrap '.$template.'">';
	switch ($template){
		case 'ihc_level_template_1':
			foreach ($levels as $id => $level){
				$button_label = '';
				if(isset($level['button_label']) && $level['button_label'] != ''){
					$button_label = $level['button_label']; 
				}
				$str .= '<div class="ihc-level-item">'
					. '<div class="ihc-level-item-top">'
						. '<div class="ihc-level-item-title">' . ihc_correct_text($level['label']) . '</div>'
					. '</div>'
					. '<div class="ihc-level-item-content">' . ihc_correct_text($level['description']) . '</div>'
					. '<div class="ihc-level-item-bottom">'
						. '<div class="ihc-level-item-price">' . ihc_correct_text($level['price_text']) . '</div>'
						.ihc_print_level_link( array('id'=>$id, 'register_page' => $register_url ), $button_label, $payment_select, true )
						. '<div class="iump-clear"></div>'
					. '</div>'
				. '</div>';
			}
		break;
		case 'ihc_level_template_2':
			foreach ($levels as $id => $level){
				$button_label = '';
				if(isset($level['button_label']) && $level['button_label'] != ''){
					$button_label = $level['button_label'];
				}
				$str .= '<div class="ihc-level-item">'
					. '<div class="ihc-level-item-top">'
						. '<div class="ihc-level-item-title">' . ihc_correct_text($level['label']) . '</div>'
					. '</div>'
					. '<div class="ihc-level-item-price">' . ihc_correct_text($level['price_text']) . '</div>'
					. '<div class="ihc-level-item-content">' . ihc_correct_text($level['description']) . '</div>'
					. '<div class="ihc-level-item-bottom">'
						.ihc_print_level_link( array('id'=>$id, 'register_page' => $register_url ), $button_label, $payment_select, TRUE )
						. '<div class="iump-clear"></div>'
					. '</div>'
				. '</div>';
			}
		break;
		case 'ihc_level_template_3':
			foreach ($levels as $id => $level){
				$button_label = '';
				if(isset($level['button_label']) && $level['button_label'] != ''){
					$button_label = $level['button_label'];
				}
				$str .= '<div class="ihc-level-item">'
					. '<div class="ihc-level-item-wrap">'
					. '<div class="ihc-level-item-top">'
						. '<div class="ihc-level-item-title">' . ihc_correct_text($level['label']) . '</div>'
					. '</div>'
					. '<div class="ihc-level-item-price">' . ihc_correct_text($level['price_text']) . '</div>'
					. '<div class="ihc-level-item-content">' . ihc_correct_text($level['description']) . '</div>'
					. '<div class="ihc-level-item-bottom">'
						.ihc_print_level_link( array('id'=>$id, 'register_page' => $register_url ), $button_label, $payment_select, TRUE )
						. '<div class="iump-clear"></div>'
					. '</div>'
					. '</div>'
				. '</div>';
			}
		break;
		case 'ihc_level_template_4':
			foreach ($levels as $id => $level){
				$button_label = '';
				if(isset($level['button_label']) && $level['button_label'] != ''){
					$button_label = $level['button_label'];
				}
				$str .= '<div class="ihc-level-item">'
					. '<div class="ihc-level-item-wrap">'
					. '<div class="ihc-level-item-top">'
						. '<div class="ihc-level-item-title">' . ihc_correct_text($level['label']) . '</div>'
					. '</div>'
					. '<div class="ihc-level-item-content">' . ihc_correct_text($level['description']) . '</div>'
					. '<div class="ihc-level-item-bottom">'
						. '<div class="ihc-level-item-price">' . ihc_correct_text($level['price_text']) . '</div>'
						. '<div class="ihc-level-item-link-wrap">'
							.ihc_print_level_link( array('id'=>$id, 'register_page' => $register_url ), $button_label, $payment_select, TRUE )
						. '</div>'
						. '<div class="iump-clear"></div>'
					. '</div>'
					. '</div>'
				. '</div>';
			}
		break;
		case 'ihc_level_template_5':
			foreach ($levels as $id => $level){
				$button_label = '';
				if(isset($level['button_label']) && $level['button_label'] != ''){
					$button_label = $level['button_label'];
				}
				$str .= '<div class="ihc-level-item">'
					. '<div class="ihc-level-item-wrap">'
					. '<div class="ihc-level-item-top">'
						. '<div class="ihc-level-item-title">' . ihc_correct_text($level['label']) . '</div>'
					. '</div>'
					. '<div class="ihc-level-item-price">' . ihc_correct_text($level['price_text']) . '</div>'
					. '<div class="ihc-level-item-content">' . ihc_correct_text($level['description']) . '</div>'
					. '<div class="ihc-level-item-bottom">'
						.ihc_print_level_link( array('id'=>$id, 'register_page' => $register_url ), $button_label, $payment_select, TRUE )
						. '<div class="iump-clear"></div>'
					. '</div>'
					. '</div>'
				. '</div>';
			}
		break;
		case 'ihc_level_template_6':
			foreach ($levels as $id => $level){
				$button_label = '';
				if(isset($level['button_label']) && $level['button_label'] != ''){
					$button_label = $level['button_label'];
				}
				$str .= '<div class="ihc-level-item">'
					. '<div class="ihc-level-item-wrap">'
					. '<div class="ihc-level-item-price">' . ihc_correct_text($level['price_text']) . '</div>'
					. '<div class="ihc-level-item-top">'
						. '<div class="ihc-level-item-title">' . ihc_correct_text($level['label']) . '</div>'
					. '</div>'
					. '<div class="ihc-level-item-content">' . ihc_correct_text($level['description']) . '</div>'
					. '<div class="ihc-level-item-bottom">'
						.ihc_print_level_link( array('id'=>$id, 'register_page' => $register_url ), $button_label, $payment_select, TRUE )
						. '<div class="iump-clear"></div>'
					. '</div>'
					. '</div>'
				. '</div>';
			}
		break;
		case 'ihc_level_template_7':
			foreach ($levels as $id => $level){
				$button_label = '';
				if(isset($level['button_label']) && $level['button_label'] != ''){
					$button_label = $level['button_label'];
				}
				$str .= '<div class="ihc-level-item">'
					. '<div class="ihc-level-item-wrap">'
					. '<div class="ihc-level-item-top">'
						. '<div class="ihc-level-item-title">' . ihc_correct_text($level['label']) . '</div>'
					. '</div>'
					. '<div class="ihc-level-item-price">' . ihc_correct_text($level['price_text']) . '</div>'
					. '<div class="ihc-level-item-content">' . ihc_correct_text($level['description']) . '</div>'
					. '<div class="ihc-level-item-bottom">'
						.ihc_print_level_link( array('id'=>$id, 'register_page' => $register_url ), $button_label, $payment_select, TRUE )
						. '<div class="iump-clear"></div>'
					. '</div>'
					. '</div>'
				. '</div>';
			}
		break;
		case 'ihc_level_template_8':
			foreach ($levels as $id => $level){
				$button_label = '';
				if(isset($level['button_label']) && $level['button_label'] != ''){
					$button_label = $level['button_label'];
				}
				$str .= '<div class="ihc-level-item">'
					. '<div class="ihc-level-item-wrap">'
					. '<div class="ihc-level-item-top">'
						. '<div class="ihc-level-item-title">' . ihc_correct_text($level['label']) . '</div>'
					. '</div>'
					. '<div class="ihc-level-item-price">' . ihc_correct_text($level['price_text']) . '</div>'
					. '<div class="ihc-level-item-content">' . ihc_correct_text($level['description']) . '</div>'
					. '<div class="ihc-level-item-bottom">'
						.ihc_print_level_link( array('id'=>$id, 'register_page' => $register_url ), $button_label, $payment_select, TRUE )
						. '<div class="iump-clear"></div>'
					. '</div>'
					. '</div>'
				. '</div>';
			}
		break;
		case 'ihc_level_template_9':
			foreach ($levels as $id => $level){
				$button_label = '';
				if(isset($level['button_label']) && $level['button_label'] != ''){
					$button_label = $level['button_label'];
				}
				$str .= '<div class="ihc-level-item">'
					. '<div class="ihc-level-item-wrap">'
					. '<div class="ihc-level-item-top">'
						. '<div class="ihc-level-item-title">' . ihc_correct_text($level['label']) . '</div>'
					. '</div>'
					. '<div class="ihc-level-item-price">' . ihc_correct_text($level['price_text']) . '</div>'
					. '<div class="ihc-level-item-content">' . ihc_correct_text($level['description']) . '</div>'
					. '<div class="ihc-level-item-bottom">'
						.ihc_print_level_link( array('id'=>$id, 'register_page' => $register_url ), $button_label, $payment_select, TRUE )
						. '<div class="iump-clear"></div>'
					. '</div>'
					. '</div>'
				. '</div>';
			}
		break;
	}
	$str .= '</div>';
	return $str;
}
