
                                  <div class="iump-form-line-register iump-form-file">
                                  <label class="iump-labels-register"><?php echo esc_html($field['label']);?></label>
                                    <?php
                            				wp_enqueue_script( 'ihc-jquery_form_module' );
                            				wp_enqueue_script( 'ihc-jquery_upload_file' );
                            				$upload_settings = ihc_return_meta_arr('extra_settings');
                            				$max_size = $upload_settings['ihc_upload_max_size'] * 1000000;
                            				$rand = rand(1,10000);
                                    $attachment_name = '';
                                    $url = '';

                                    // $ajaxURL = IHC_URL . 'public/ajax-upload.php?ihcpublicn='. wp_create_nonce( 'ihcpublicn' );
                                    $ajaxURL = get_site_url() . '/wp-admin/admin-ajax.php?action=ihc_ajax_public_upload_file&ihcpublicn=' . wp_create_nonce( 'ihcpublicn' );
                                    ?>
                            				<div id="<?php echo esc_attr('ihc_fileuploader_wrapp_' . $rand);?>" class="ihc-wrapp-file-upload ihc-wrapp-file-field ihc-vertical-align-top">
                            				<div class="ihc-file-upload ihc-file-upload-button"><?php esc_html_e("Upload", 'ihc');?></div>
                                    <span class="ihc-js-upload-file-data"
                                          data-alert_text="<?php echo esc_html__('"To add a new file please remove the previous one!"', 'ihc');?>"
                                          data-rand="<?php echo esc_attr($rand);?>"
                                          data-url="<?php echo esc_url($ajaxURL);?>"
                                          data-max_size="<?php echo esc_attr($max_size);?>"
                                          data-allowed_types="<?php echo esc_attr($upload_settings['ihc_upload_extensions']);?>"
                                          data-field_name="<?php echo esc_attr($fieldName);?>"
                                        ></span>

                                <?php
                                if ( $fieldValue ){
                                  if ( strpos ( $fieldValue, 'http' ) !== false ){
                                    $fileExtension = explode( '.', $fieldValue );
                                    end( $fileExtension );
                                    $attachment_type = current( $fileExtension );
                                    $url = $fieldValue;
                                  } else {
                                    $attachment_type = ihc_get_attachment_details($fieldValue, 'extension');
                                    $url = wp_get_attachment_url($fieldValue);
                                  }
                                  $imgClass = isset( $field['img_class'] ) ? $field['img_class'] : 'ihc-member-photo';
                                  switch ($attachment_type){
                                    case 'jpg':
                                    case 'jpeg':
                                    case 'png':
                                    case 'gif':
                                      //print the picture
                                      ?>
                                      <img src="<?php echo esc_url($url);?>" class="<?php echo esc_attr($imgClass);?>" /><div class="ihc-clear"></div>
                                      <?php
                                      break;
                                    default:
                                      //default file type
                                      ?>
                                      <div class="ihc-icon-file-type"></div>

                                      <?php
                                      break;
                                  }
                                  ?>
                                  <?php
                                  $attachment_name = ihc_get_attachment_details($fieldValue);
                                }
                                ?>
  <?php if ( $fieldValue != '' ):?>
      <div class="ihc-file-name-uploaded"><a href="<?php echo esc_url($url);?>" target="_blank"><?php echo esc_html($attachment_name);?></a></div>
      <div onClick='ihcDeleteFileViaAjax( "<?php echo esc_attr($fieldValue);?>", <?php echo esc_attr($data['uid']);?>, "#ihc_fileuploader_wrapp_<?php echo esc_attr($rand);?>", "<?php echo esc_attr($fieldName);?>", "#ihc_upload_hidden_<?php echo esc_attr($rand);?>");' class="ihc-delete-attachment-bttn"><?php esc_html_e( 'Remove', 'ihc' );?></div>
  <?php endif;?>
  <input type="hidden" value="<?php echo esc_attr($fieldValue);?>" name="<?php echo esc_attr($fieldName);?>" id="ihc_upload_hidden_<?php echo esc_attr($rand);?>" />

  </div>
</div>
