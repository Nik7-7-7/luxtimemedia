<?php
function ihc_update_reg_fields($post_data){
	/*
	 * this function will update the order of register fields
	 * @param $_POST
	 * @return none
	 */
	$data = get_option('ihc_user_fields');
	$new_data = array();
	foreach ($data as $k=>$v){
		if ( !isset( $post_data['ihc-order-' . $k] ) ){
				continue;
		}
		$num = $post_data['ihc-order-' . $k];
		$new_data[$num] = $v;
		if (isset($post_data['ihc-field-display-admin' . $k])){
			$new_data[$num]['display_admin'] = $post_data['ihc-field-display-admin' . $k];
		}
		if (isset($post_data['ihc-field-display-public-reg' . $k])){
			$new_data[$num]['display_public_reg'] = $post_data['ihc-field-display-public-reg' . $k];
		}
		if (isset($post_data['ihc-field-display-public-ap' . $k])){
			$new_data[$num]['display_public_ap'] = $post_data['ihc-field-display-public-ap' . $k];
		}
		if (isset($post_data['ihc-field-display-on-modal' . $k])){
				$new_data[$num]['display_on_modal'] = $post_data['ihc-field-display-on-modal' . $k];
		}
		if (isset($post_data['ihc-require-' . $k])){
			$new_data[$num]['req'] = $post_data['ihc-require-' . $k];
		}
	}
	update_option('ihc_user_fields', $new_data);
}

function ihc_update_register_fields($post_data){
	/*
	 * this function will update user custom fields
	 * @param $_POST
	 * @return none
	 */
	if (isset($post_data['name'])){
		$post_data['name'] = ihc_make_string_simple( $post_data['name'] ); // NO whitespaces in slug
                if ( isset( $post_data['id'] ) ){
                    $post_data['id'] = $post_data['id'];
                }
		$meta = get_option('ihc_user_fields');

			$temporary = $meta;
			if ( isset( $temporary[$post_data['id']] ) ){
					unset( $temporary[$post_data['id']] );
					if ( ihc_is_array_value_multi_exists( $temporary, $post_data['name'], 'name')>0 ){
						return;
					}
			}
	}



	if (isset($meta[$post_data['id']])){
		$possible_fields = array(
									'name',
									'label',
									'type',
									'values',
									'sublabel',
									'display_admin',
									'display_public_ap',
									'display_public_reg',
									'target_levels',
									'class',
									'theme',
									'ihc_optin_accept_checked',
									'ihc_memberlist_accept_checked',
									'plain_text_value',
									'conditional_text',
									'error_message',
									'conditional_logic_show',
									'conditional_logic_corresp_field',
									'conditional_logic_corresp_field_value',
									'conditional_logic_cond_type',
		);

		foreach ($possible_fields as $key){
			if (isset($post_data[$key])){
				$meta[$post_data['id']][$key] = $post_data[$key];
			}
		}
		update_option('ihc_user_fields', $meta);
	}
}

/**
 * @param array ( values how already been serialized )
 * @return none
*/
function ihc_save_user_field($post_data)
{
        $post_data['name'] = isset( $post_data['name'] ) ? $post_data['name'] : false;
        $post_data['label'] = isset( $post_data['label'] ) ? $post_data['label'] : false;
        $post_data['type'] = isset( $post_data['type'] ) ? $post_data['type']  : false;
	if ( $post_data['name'] && $post_data['type'] ){ // remove  && $post_data['label']  since version 12.0
		$new = array(
				//'display' => 0,// deprecated
				'display_admin' => 0,
				'display_public_reg' => 0,
				'display_public_ap' => 0,
				'name' => ihc_make_string_simple($post_data['name']),
				'label' => $post_data['label'],
				'type' => $post_data['type'],
				'native_wp' => 0,
				'sublabel' => (isset($post_data['sublabel'])) ? $post_data['sublabel'] : '',
				'target_levels' => (isset($post_data['target_levels'])) ? $post_data['target_levels'] : '',
				'class' => (isset($post_data['class'])) ? $post_data['class'] : '',
		);
		$new['req'] = (isset($post_data['req'])) ? $post_data['req'] : 0;
		$new['display_admin'] = (isset($post_data['display_admin'])) ? $post_data['display_admin'] : 0;
		$new['display_public_reg'] = (isset($post_data['display_public_reg'])) ? $post_data['display_public_reg'] : 0;
		$new['display_public_ap'] = (isset($post_data['display_public_ap'])) ? $post_data['display_public_ap'] : 0;

		$optional_metas = array(
									'values',
									'theme',
									'ihc_optin_accept_checked',
									'ihc_memberlist_accept_checked',
									'plain_text_value',
									'conditional_text',
									'error_message',
									'conditional_logic_show',
									'conditional_logic_corresp_field',
									'conditional_logic_corresp_field_value',
									'conditional_logic_cond_type',
		);

		$data = get_option('ihc_user_fields');
		if (ihc_array_value_exists($data, $new['name'], 'name')!==FALSE){
			return;
		}

		foreach ($optional_metas as $optional_meta){
			if (isset($post_data[$optional_meta])){
				$new[$optional_meta] = $post_data[$optional_meta];
			}
		}

		if ($data!==FALSE){
			$data[]= $new;
		} else {
			$data = ihc_native_user_field();
			$data[] = $new;
		}
		update_option( 'ihc_user_fields', $data );
	}
}

function ihc_delete_user_field($id){
	/*
	 * delete user field
	 * @param field id to delete
	 * @return none
	 */
	$data = get_option('ihc_user_fields');
	if (isset($data[$id]) ){
		unset( $data[$id] );
	}
	update_option('ihc_user_fields', $data);
}
