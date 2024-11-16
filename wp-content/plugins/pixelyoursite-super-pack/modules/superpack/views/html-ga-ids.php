<?php

namespace PixelYourSite\SuperPack;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use PixelYourSite;

?>

<?php
$isWpmlActive = isWPMLActive();
if($isWpmlActive) {
    $languageCodes = array_keys(apply_filters( 'wpml_active_languages',null,null));
}
$pixelsInfo = PixelYourSite\SuperPack()->getGaAdditionalPixel();

foreach ($pixelsInfo as $index => $pixelInfo) : ?>
    <div class="plate mt-3 pt-4 pb-3 pixel_info">

        <?php PixelYourSite\SuperPack()->render_text_input_array_item('ga_ext_pixel_id', "", $index,true); ?>
        <div class="row">
            <div class="col-11">
                <div class='custom-switch '>
                    <input type="checkbox" value="1" <?php checked($pixelInfo->isEnable, true); ?>
                           id="pixel_ga_is_enable_<?=$index?>" class="custom-switch-input is_enable">
                    <label class="custom-switch-btn" for="pixel_ga_is_enable_<?=$index?>"></label>
                </div>
                <h4 class="switcher-label">Enable track ID</h4>
            </div>
            <div class="col-1">
                <button type="button" class="btn btn-sm remove-row">
                    <i class="fa fa-trash-o" aria-hidden="true"></i>
                </button>
            </div>
        </div>
        <div class="row  mb-2">
            <div class="col-12">
                <div class="custom-switch">
                    <input type="checkbox" value="1" <?php checked($pixelInfo->isUseServerApi, true); ?> id="pys_ga_use_server_api_<?=$index?>" class="custom-switch-input pys_ga_use_server_api">
                    <label class="custom-switch-btn" for="pys_ga_use_server_api_<?=$index?>"></label>
                </div>

                <h4 class="switcher-label">Enable Measurement Protocol (add the api_secret)</h4>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12">
                <h4 class="label mb-3 mt-3">Google Analytics tracking ID:</h4>
                <input type="text" value="<?= $pixelInfo->pixel ?>"
                           placeholder="Google Analytics tracking ID" class='form-control pixel_id ga_tracking_id'/>

                <p class="ga_pixel_info small">
                    <?php if (!empty($pixelInfo->pixel)) :
                        if(strpos($pixelInfo->pixel, 'G') === 0) {
                            echo 'We identified this tag as a GA4 property.';
                        } else {
                            echo '<span class="not-support-tag">We identified this tag as a Google Analytics Universal property.</span>';
                        }
                    endif; ?>
                </p>

                <div class="row align-items-center mb-3">
                    <div class="col-12">
                        <h4 class="label">Measurement Protocol API secret:</h4>
                        <input type="text" value="<?= $pixelInfo->server_access_api_token ?>"
                               placeholder="API secret" class='form-control server_access_api_token' <?= !$pixelInfo->isUseServerApi ? 'disabled' : ''; ?>/>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col">
                        Generate the API secret inside your Google Analytics account: navigate to <b>Admin > Data Streams > choose your stream > Measurement Protocol API secrets</b>. The Measurement Protocol is used for WooCommerce and Easy Digital Downloads "Google Analytics Advanced Purchase Tracking" and refund tracking. Required for GA4 properties only.
                    </div>
                </div>
                <p>
                    <label class="custom-control custom-checkbox">
                        <input type="checkbox" data-ext="debug_mode" value="1"
                               class="custom-control-input pixel_ext_chekbox"
                            <?= !empty($pixelInfo->extensions['debug_mode']) ? ($pixelInfo->extensions['debug_mode'] !== false ? "checked" : "") : "" ?>>
                        <span class="custom-control-indicator"></span>
                        <span class="custom-control-description">Enable Analytics Debug mode for this property</span>
                    </label>
                </p>
                <hr>
                <div class="row align-items-center mb-3">
                    <div class="col-12">
                        <div class="mb-1">
                            <div class='custom-switch '>
                                <input type="checkbox" value="1" <?php checked($pixelInfo->enable_server_container, true); ?>
                                       id="enable_server_container_<?=$index?>" class="custom-switch-input enable_server_container">
                                <label class="custom-switch-btn" for="enable_server_container_<?=$index?>"></label>
                            </div>
                            <h4 class="switcher-label">Enable Server container url (Beta)</h4>
                        </div>
                        <p>
                            <?php _e('Learn how to use it: ', 'pys');?>
                            <a href="https://www.youtube.com/watch?v=WZnmSoSJyBc" target="_blank">watch video</a>
                        </p>
                    </div>
                </div>
                <div class="row align-items-center mb-3">
                    <div class="col-12">
                        <h4 class="label">Server container url (optional): </h4>
                        <input type="text" data-ext="server_container_url" class="form-control server_container_url"
                            value="<?= !empty($pixelInfo->server_container_url) ? $pixelInfo->server_container_url : "" ?>"
                            placeholder="https://analytics.example.com"/>
                    </div>
                </div>
                <div class="row align-items-center mb-3">
                    <div class="col-12">
                        <h4 class="label">Transport url (optional): </h4>
                        <input type="text" data-ext="transport_url" class="form-control transport_url"
                               value="<?= !empty($pixelInfo->transport_url) ? $pixelInfo->transport_url : "" ?>"
                               placeholder="https://tagging.mywebsite.com"/>
                    </div>
                </div>
                <div class="row align-items-center mb-3">
                    <div class="col-12">
                        <label class="custom-control custom-checkbox">
                            <input type="checkbox" value="1"
                                   class="custom-control-input first_party_collection" <?php checked($pixelInfo->first_party_collection, true); ?>>
                            <span class="custom-control-indicator"></span>
                            <span class="custom-control-description">First party cookies selector first_party_collection (recommended)</span>
                        </label>
                    </div>
                </div>
                <hr>
                <p>
                    <label class="custom-control custom-checkbox">
                        <input type="checkbox" value="1"
                               class="custom-control-input is_fire_signal" <?php checked($pixelInfo->isFireForSignal, true); ?>>
                        <span class="custom-control-indicator"></span>
                        <span class="custom-control-description">Fire the active automated events for this pixel</span>
                    </label>
                </p>

                <?php if (PixelYourSite\isWooCommerceActive()) : ?>
                    <p>
                        <label class="custom-control custom-checkbox">
                            <input type="checkbox"
                                   class="custom-control-input is_fire_woo" <?php checked($pixelInfo->isFireForWoo, true); ?>>
                            <span class="custom-control-indicator"></span>
                            <span class="custom-control-description">Fire the WooCommerce events for this pixel</span>
                        </label>
                    </p>
                <?php endif; ?>
                <?php if (PixelYourSite\isEddActive()) : ?>
                    <p>
                        <label class="custom-control custom-checkbox">
                            <input type="checkbox"
                                   class="custom-control-input is_fire_edd" <?php checked($pixelInfo->isFireForEdd, true); ?>>
                            <span class="custom-control-indicator"></span>
                            <span class="custom-control-description">Fire the Easy Digital Downloads events for this pixel</span>
                        </label>
                    </p>
                <?php endif; ?>
                <p>
                    <strong>Display conditions:</strong>
                    <?php SpPixelCondition()->renderHtml($pixelInfo->displayConditions) ?>
                </p>
                <hr>

                <?php if(PixelYourSite\SuperPack()->getOption('enable_hide_this_tag_by_url')) : ?>
                    <div class="row align-items-center pb-3">
                        <div class="col-12">
                            <div class='custom-switch '>
                                <input type="checkbox" value="1" <?php checked($pixelInfo->isHideByUrl, true); ?>
                                       id="pixel_ga_is_hide_url_<?=$index?>" class="custom-switch-input is-hide-url">
                                <label class="custom-switch-btn" for="pixel_ga_is_hide_url_<?=$index?>"></label>
                            </div>
                            <h4 class="switcher-label">Hide this tag if the URL includes</h4>
                        </div>
                    </div>
                    <div class="row align-items-center pb-3">
                        <div class="col-12">
                            <h4 class="label">Hide this tag if the page URL any of these values. The tag will not fire on the speciffic page only.</h4>
                            <select class="form-control pys-condition-pysselect2 hide-conditions-url" id="pixel_ga_hide_url_conditions_<?=$index?>" style="width: 100%;"
                                    multiple>

                                <?php foreach ( $pixelInfo->hideConditionByUrl as $tag ) : ?>
                                    <option  value="<?php echo esc_attr( $tag ); ?>" selected locked="locked">
                                        <?php echo esc_attr( $tag ); ?>
                                    </option>
                                <?php endforeach; ?>

                            </select>
                        </div>
                    </div>
                    <hr>
                <?php endif; ?>
                <?php if(PixelYourSite\SuperPack()->getOption('enable_hide_this_tag_by_tags')) : ?>
                    <div class="row align-items-center pb-3">
                        <div class="col-12">
                            <div class='custom-switch '>
                                <input type="checkbox" value="1" <?php checked($pixelInfo->isHide, true); ?>
                                       id="pixel_ga_is_hide_<?=$index?>" class="custom-switch-input is-hide">
                                <label class="custom-switch-btn" for="pixel_ga_is_hide_<?=$index?>"></label>
                            </div>
                            <h4 class="switcher-label">Hide this tag if the landing URL includes these URL tags</h4>
                        </div>
                    </div>
                    <div class="row align-items-center pb-3">
                        <div class="col-12">
                            <h4 class="label"> Hide this tag if the landing page URL includes any of these URL parameters values. The tag will not fire on any pages.</h4>
                            <select class="form-control pys-condition-pysselect2 hide-conditions" id="pixel_ga_hide_conditions_<?=$index?>" style="width: 100%;"
                                    multiple>

                                <?php foreach ( $pixelInfo->hideCondition as $tag ) : ?>
                                    <option  value="<?php echo esc_attr( $tag ); ?>" selected locked="locked">
                                        <?php echo esc_attr( $tag ); ?>
                                    </option>
                                <?php endforeach; ?>

                            </select>
                            <small>
                                Use this format: param_name=value or param_name<br>
                                Example: brand=Apple, brand.
                            </small>
                        </div>
                    </div>
                    <div class="row align-items-center pb-3">
                        <div class="col-12 flex-input-block" >
                            <h4 class="label">Hide for:</h4>
                            <input type="number"
                                   id="pixel_ga_hide_time"
                                   value="<?php echo isset($pixelInfo->hideTime) && !empty($pixelInfo->hideTime) ? $pixelInfo->hideTime : 24 ?>"
                                   min="0" class="form-control hide-time"
                                   max="720"
                                   step="0.01"
                            >
                            <span>Hours</span>
                        </div>
                    </div>
                    <hr>
                <?php endif; ?>
                <?php if ($isWpmlActive && !empty($languageCodes)) {
                    $active = $pixelInfo->wpmlActiveLang;
                    if($active == null && !is_array($active)) {
                        $active = $languageCodes;
                    }
                    printLangList($active, $languageCodes);
                }
                ?>
            </div>

        </div>
    </div>
<?php endforeach; ?>


<div class="plate mt-3 pt-4 pb-3 pixel_info" id="pys_superpack_ga_tracking_id" style="display: none;">

    <input type="hidden" name="pys[superpack][ga_ext_pixel_id][]"  value="" placeholder="0" class="form-control">

    <div class="row">
        <div class="col-11">
            <div class='custom-switch '>
                <input type="checkbox" value="1" checked id="pixel_ga_is_enable" class="custom-switch-input is_enable">
                <label class="custom-switch-btn" for="pixel_ga_is_enable"></label>
            </div>
            <h4 class="switcher-label">Enable track ID</h4>
        </div>
        <div class="col-1">
            <button type="button" class="btn btn-sm remove-row">
                <i class="fa fa-trash-o" aria-hidden="true"></i>
            </button>
        </div>
    </div>
    <div class="row  mb-2">
        <div class="col-12">
            <div class="custom-switch">
                <input type="checkbox" value="1" id="pys_ga_use_server_api" class="custom-switch-input pys_ga_use_server_api">
                <label class="custom-switch-btn" for="pys_ga_use_server_api"></label>
            </div>

            <h4 class="switcher-label">Enable Measurement Protocol (add the api_secret)</h4>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-12">

            <input type="text" value="" placeholder="Google Analytics tracking ID" class='form-control pixel_id ga_tracking_id'/>
            <p class="ga_pixel_info small"></p>

            <div class="row align-items-center mb-3">
                <div class="col-12">
                    <h4 class="label">Measurement Protocol API secret:</h4>
                    <input type="text" value=""
                           placeholder="API secret" class='form-control server_access_api_token' disabled/>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col">
                    Generate the API secret inside your Google Analytics account: navigate to <b>Admin > Data Streams > choose your stream > Measurement Protocol API secrets.</b> The Measurement Protocol is used for WooCommerce and Easy Digital Downloads "Google Analytics Advanced Purchase Tracking" option. Requiered for GA4 properties only.
                </div>
            </div>
            <p>
                <label class="custom-control custom-checkbox">
                    <input type="checkbox" data-ext="debug_mode" value="1" class="custom-control-input pixel_ext_chekbox">
                    <span class="custom-control-indicator"></span>
                    <span class="custom-control-description">Enable Analytics Debug mode for this property</span>
                </label>
            </p>
            <hr>
            <div class="row align-items-center mb-3">
                <div class="col-12">
                    <div class="mb-1">
                        <div class='custom-switch '>
                            <input type="checkbox" value="1" id="enable_server_container" class="custom-switch-input enable_server_container">
                            <label class="custom-switch-btn" for="enable_server_container"></label>
                        </div>
                        <h4 class="switcher-label">Enable Server container url (Beta)</h4>
                    </div>
                    <p>
                        <?php _e('Learn how to use it: ', 'pys');?>
                        <a href="https://www.youtube.com/watch?v=WZnmSoSJyBc" target="_blank">watch video</a>
                    </p>
                </div>
            </div>
            <div class="row align-items-center mb-3">
                <div class="col-12">
                    <h4 class="label">Server container url (optional): </h4>
                    <input type="text" data-ext="server_container_url" class="form-control server_container_url"
                           value=""
                           placeholder="https://analytics.example.com"/>
                </div>
            </div>
            <div class="row align-items-center mb-3">
                <div class="col-12">
                    <h4 class="label">Transport url (optional): </h4>
                    <input type="text" data-ext="transport_url" class="form-control transport_url"
                           value=""
                           placeholder="https://tagging.mywebsite.com"/>
                </div>
            </div>
            <div class="row align-items-center mb-3">
                <div class="col-12">
                    <label class="custom-control custom-checkbox">
                        <input type="checkbox" value="1" checked class="custom-control-input first_party_collection">
                        <span class="custom-control-indicator"></span>
                        <span class="custom-control-description">First party cookies selector first_party_collection (recommended)</span>
                    </label>
                </div>
            </div>
            <hr>
            <p>
                <label class="custom-control custom-checkbox">
                    <input type="checkbox" value="1"
                           class="custom-control-input is_fire_signal" checked>
                    <span class="custom-control-indicator"></span>
                    <span class="custom-control-description">Fire the active automated events for this pixel</span>
                </label>
            </p>

            <?php if (PixelYourSite\isWooCommerceActive()) : ?>
                <p>
                    <label class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input is_fire_woo" checked>
                        <span class="custom-control-indicator"></span>
                        <span class="custom-control-description">Fire the WooCommerce events for this pixel</span>
                    </label>
                </p>
            <?php endif; ?>
            <?php if (PixelYourSite\isEddActive()) : ?>
                <p>
                    <label class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input is_fire_edd" checked>
                        <span class="custom-control-indicator"></span>
                        <span class="custom-control-description">Fire the Easy Digital Downloads events for this pixel</span>
                    </label>
                </p>
            <?php endif; ?>
            <p>
                <strong>Display conditions:</strong>
                <?php SpPixelCondition()->renderHtml() ?>
            </p>
            <hr>

            <?php if(PixelYourSite\SuperPack()->getOption('enable_hide_this_tag_by_url')) : ?>
                <div class="row align-items-center pb-3">
                    <div class="col-12">
                        <div class='custom-switch '>
                            <input type="checkbox" value="1"
                                   id="pixel_ga_is_hide_url" class="custom-switch-input is-hide-url">
                            <label class="custom-switch-btn" for="pixel_ga_is_hide_url"></label>
                        </div>
                        <h4 class="switcher-label">Hide this tag if the URL includes</h4>
                    </div>
                </div>
                <div class="row align-items-center pb-3">
                    <div class="col-12">
                        <h4 class="label">Hide this tag if the page URL any of these values. The tag will not fire on the speciffic page only.</h4>
                        <select class="form-control pys-condition-pysselect2 hide-conditions-url" id="pixel_ga_hide_url_conditions" style="width: 100%;" multiple></select>
                    </div>
                </div>
                <hr>
            <?php endif; ?>
            <?php if(PixelYourSite\SuperPack()->getOption('enable_hide_this_tag_by_tags')) : ?>
                <div class="row align-items-center pb-3">
                    <div class="col-12">
                        <div class='custom-switch '>
                            <input type="checkbox" value="1"
                                   id="pixel_ga_is_hide" class="custom-switch-input is-hide">
                            <label class="custom-switch-btn" for="pixel_ga_is_hide"></label>
                        </div>
                        <h4 class="switcher-label">Hide this tag if the landing URL includes these URL tags</h4>
                    </div>
                </div>
                <div class="row align-items-center pb-3">
                    <div class="col-12">
                        <h4 class="label"> Hide this tag if the landing page URL includes any of these URL parameters values. The tag will not fire on any pages.</h4>
                        <select class="form-control pys-condition-pysselect2 hide-conditions" id="pixel_ga_hide_conditions" style="width: 100%;" multiple> </select>                       </select>
                        <small>
                            Use this format: param_name=value or param_name<br>
                            Example: brand=Apple, brand.
                        </small>
                    </div>
                </div>
                <div class="row align-items-center pb-3">
                    <div class="col-12 flex-input-block" >
                        <h4 class="label">Hide for:</h4>
                        <input type="number"
                               id="pixel_ga_hide_time"
                               value="24"
                               min="0" class="form-control hide-time"
                               max="720"
                               step="0.01"
                        >
                        <span>Hours</span>
                    </div>
                </div>
                <hr>
            <?php endif; ?>
            <?php if ($isWpmlActive && !empty($languageCodes)) {
                printLangList($languageCodes, $languageCodes );
            }
            ?>
        </div>
    </div>
</div>


<div class="row my-3">
    <div class="col-12">
        <button class="btn btn-sm btn-primary" type="button"
                id="pys_superpack_add_ga_tracking_id">
            Add Extra Google Analytics Tracking ID
        </button>
    </div>
</div>


<script type="text/javascript">
    jQuery(document).ready(function ($) {
        



    });
</script>