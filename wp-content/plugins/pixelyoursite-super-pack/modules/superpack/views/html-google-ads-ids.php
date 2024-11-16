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

$pixelsInfo = PixelYourSite\SuperPack()->getAdsAdditionalPixel();

foreach ($pixelsInfo as $index => $pixelInfo) : ?>

    <div class="plate mt-3 pt-4 pb-3 pixel_info">
        <?php PixelYourSite\SuperPack()->render_text_input_array_item('ads_ext_pixel_id', "", $index,true); ?>
        <div class="row">
            <div class="col-11">
                <div class='custom-switch '>
                    <input type="checkbox" value="1" <?php checked($pixelInfo->isEnable, true); ?>
                           id="pixel_ads_is_enable_<?=$index?>" class="custom-switch-input is_enable">
                    <label class="custom-switch-btn" for="pixel_ads_is_enable_<?=$index?>"></label>
                </div>
                <h4 class="switcher-label">Enable Pixel</h4>
            </div>
            <div class="col-1">
                <button type="button" class="btn btn-sm remove-row">
                    <i class="fa fa-trash-o" aria-hidden="true"></i>
                </button>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <p>
                    <input type="text" value="<?= $pixelInfo->pixel ?>"
                           placeholder="AW-123456789" class='form-control pixel_id'/>
                </p>
                <p>
                    <?php PixelYourSite\Ads()->render_switcher_input_array("enhanced_conversions_manual_enabled",($index+1));?>
                    <div class="switcher-label">Enable enhanced conversions</div>
                </p>
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
                                       id="pixel_ads_is_hide_url_<?=$index?>" class="custom-switch-input is-hide-url">
                                <label class="custom-switch-btn" for="pixel_ads_is_hide_url_<?=$index?>"></label>
                            </div>
                            <h4 class="switcher-label">Hide this tag if the URL includes</h4>
                        </div>
                    </div>
                    <div class="row align-items-center pb-3">
                        <div class="col-12">
                            <h4 class="label">Hide this tag if the page URL any of these values. The tag will not fire on the speciffic page only.</h4>
                            <select class="form-control pys-condition-pysselect2 hide-conditions-url" id="pixel_ads_hide_url_conditions_<?=$index?>" style="width: 100%;"
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
                                       id="pixel_ads_is_hide_<?=$index?>" class="custom-switch-input is-hide">
                                <label class="custom-switch-btn" for="pixel_ads_is_hide_<?=$index?>"></label>
                            </div>
                            <h4 class="switcher-label">Hide this tag if the landing URL includes these URL tags</h4>
                        </div>
                    </div>
                    <div class="row align-items-center pb-3">
                        <div class="col-12">
                            <h4 class="label"> Hide this tag if the landing page URL includes any of these URL parameters values. The tag will not fire on any pages.</h4>
                            <select class="form-control pys-condition-pysselect2 hide-conditions" id="pixel_ads_hide_conditions_<?=$index?>" style="width: 100%;"
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
                                   id="pixel_ads_hide_time"
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
                <?php
                if ($isWpmlActive && !empty($languageCodes)) {
                    $active = $pixelInfo->wpmlActiveLang;
                    if ($active == null && !is_array($active)) {
                        $active = $languageCodes;
                    }

                    printLangList($active, $languageCodes);
                }
                ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<div class="plate mt-3 pt-4 pb-3 pixel_info" id="pys_superpack_google_ads_id" style="display: none;">

    <input type="hidden" name="pys[superpack][ads_ext_pixel_id][]" value="" placeholder="0" class="form-control">
    <div class="row">
        <div class="col-11">
            <div class='custom-switch '>
                <input type="checkbox" value="1" checked
                       id="pixel_ads_is_enable" class="custom-switch-input is_enable">
                <label class="custom-switch-btn" for="pixel_ads_is_enable"></label>
            </div>
            <h4 class="switcher-label">Enable Pixel</h4>
        </div>
        <div class="col-1">
            <button type="button" class="btn btn-sm remove-row">
                <i class="fa fa-trash-o" aria-hidden="true"></i>
            </button>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-12">
            <p>
                <input type="text" value="" placeholder="AW-123456789" class='form-control pixel_id'/>
            </p>
            <p>
            <div class="custom-switch">
                <input type="checkbox"
                       name="enhanced_conversions_manual_enabled"
                       value="1" checked
                       id="enhanced_conversions_manual_enabled"
                       class="custom-switch-input enhanced_conversions_manual_enabled">

                <label class="custom-switch-btn" for="enhanced_conversions_manual_enabled"></label>
            </div>
            <div class="switcher-label">Enable enhanced conversions</div>

            </p>
            <p>
                <label class="custom-control custom-checkbox">
                    <input type="checkbox" value="1" class="custom-control-input is_fire_signal" checked>
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
                <?php  SpPixelCondition()->renderHtml() ?>
            </p>
            <hr>

            <?php if(PixelYourSite\SuperPack()->getOption('enable_hide_this_tag_by_url')) : ?>
                <div class="row align-items-center pb-3">
                    <div class="col-12">
                        <div class='custom-switch '>
                            <input type="checkbox" value="1"
                                   id="pixel_ads_is_hide_url" class="custom-switch-input is-hide-url">
                            <label class="custom-switch-btn" for="pixel_ads_is_hide_url"></label>
                        </div>
                        <h4 class="switcher-label">Hide this tag if the URL includes</h4>
                    </div>
                </div>
                <div class="row align-items-center pb-3">
                    <div class="col-12">
                        <h4 class="label">Hide this tag if the page URL any of these values. The tag will not fire on the speciffic page only.</h4>
                        <select class="form-control pys-condition-pysselect2 hide-conditions-url" id="pixel_ads_hide_url_conditions" style="width: 100%;" multiple></select>
                    </div>
                </div>
                <hr>
            <?php endif; ?>
            <?php if(PixelYourSite\SuperPack()->getOption('enable_hide_this_tag_by_tags')) : ?>
                <div class="row align-items-center pb-3">
                    <div class="col-12">
                        <div class='custom-switch '>
                            <input type="checkbox" value="1"
                                   id="pixel_ads_is_hide" class="custom-switch-input is-hide">
                            <label class="custom-switch-btn" for="pixel_ads_is_hide"></label>
                        </div>
                        <h4 class="switcher-label">Hide this tag if the landing URL includes these URL tags</h4>
                    </div>
                </div>
                <div class="row align-items-center pb-3">
                    <div class="col-12">
                        <h4 class="label"> Hide this tag if the landing page URL includes any of these URL parameters values. The tag will not fire on any pages.</h4>
                        <select class="form-control pys-condition-pysselect2 hide-conditions" id="pixel_ads_hide_conditions" style="width: 100%;" multiple> </select>                       </select>
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
                               id="pixel_ads_hide_time"
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
                id="pys_superpack_add_google_ads_id">
            Add Extra Google Ads Tag
        </button>
    </div>
</div>
