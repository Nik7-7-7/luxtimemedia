<?php

namespace PixelYourSite\SuperPack;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use PixelYourSite;

global $post;

?>

<?php if ( PixelYourSite\Facebook()->enabled() ) : ?>
    <?php
        $value = (array) get_post_meta( $post->ID, '_pys_super_pack_remove_pixel', true );
        $pixels = PixelYourSite\Facebook()->getAllPixels(false);
        $disabledPixel = '';

        if($value == "1" && is_array($pixels) && count($pixels) > 0) {
            $disabledPixel = 'all';
        } else if(!empty($value)) {
            $disabledPixel = $value;
        }

    ?>
    <p>
        <label>
            <strong>Remove Meta Pixel (formerly Facebook Pixel) on this <?php echo get_post_type(); ?></strong>
    <p>
            <select name="pys_super_pack_remove_pixel[]" class="form-control pys-pysselect2"
                    data-placeholder="Select Pixel" multiple>
                <option>Select Pixel</option>
                <option value="all" <?=selected(in_array( 'all', $disabledPixel))?>>Remove All</option>
                <?php foreach ($pixels as $pixel) : ?>
                    <?php if (is_array($disabledPixel)){ ?>
                        <option value="<?=$pixel?>" <?= selected(in_array( $pixel, $disabledPixel))?> ><?=$pixel?></option>
                    <?php } else { ?>
                        <option value="<?=$pixel?>" <?=selected($pixel,$disabledPixel)?> ><?=$pixel?></option>
                    <?php } ?>
                <?php endforeach; ?>
            </select>
    </p>

        </label>
    </p>
<?php endif; ?>

<?php if ( PixelYourSite\GA()->enabled() ) : ?>
	<?php
        $value = get_post_meta( $post->ID, '_pys_super_pack_remove_ga_pixel', true );
        $pixels = PixelYourSite\GA()->getAllPixels(false);
        $disabledPixel = '';

        if($value == "1" && is_array($pixels) && count($pixels) > 0) {
            $disabledPixel = 'all';
        } else if(!empty($value)) {
            $disabledPixel = $value;
        }
        ?>
	<p>
		<label>
			<strong>Remove Google Analytics on this <?php echo get_post_type(); ?></strong>
            <p>
                <select name="pys_super_pack_remove_ga_pixel[]" class="form-control pys-pysselect2" data-placeholder="Select Pixel" multiple>
                    <option>Select Pixel</option>
                    <?php if (is_array($disabledPixel)){ ?>
                        <option value="all" <?= selected(in_array("all", $disabledPixel))?>>Remove All</option>
                    <?php } else { ?>
                        <option value="all" <?= selected("all", $disabledPixel)?>>Remove All</option>
                    <?php } ?>

                    <?php foreach ($pixels as $pixel) : ?>
                        <?php if (is_array($disabledPixel)){ ?>
                            <option value="<?=$pixel?>" <?= selected(in_array( $pixel, $disabledPixel))?> ><?=$pixel?></option>
                        <?php } else { ?>
                            <option value="<?=$pixel?>" <?=selected($pixel,$disabledPixel)?> ><?=$pixel?></option>
                        <?php } ?>
                    <?php endforeach; ?>
                </select>
            </p>
		</label>
	</p>
<?php endif; ?>


<?php if ( PixelYourSite\Ads()->enabled() ) : ?>
    <?php
    $value = get_post_meta( $post->ID, '_pys_super_pack_remove_ads_pixel', true );
    $pixels = PixelYourSite\Ads()->getAllPixels(false);
    $disabledPixel = $value;

    ?>
    <p>
        <label>
            <strong>Remove Google Ads Tag on this <?php echo get_post_type(); ?></strong>
    <p>
        <select name="pys_super_pack_remove_ads_pixel[]" class="form-control pys-pysselect2" data-placeholder="Select Pixel" multiple>
            <option>Select Pixel</option>
            <?php if (is_array($disabledPixel)){ ?>
                <option value="all" <?= selected(in_array("all", $disabledPixel))?>>Remove All</option>
            <?php } else { ?>
                <option value="all" <?= selected("all", $disabledPixel)?>>Remove All</option>
            <?php } ?>
            <?php foreach ($pixels as $pixel) : ?>
                <?php if (is_array($disabledPixel)){ ?>
                    <option value="<?=$pixel?>" <?= selected(in_array( $pixel, $disabledPixel))?> ><?=$pixel?></option>
                <?php } else { ?>
                    <option value="<?=$pixel?>" <?=selected($pixel,$disabledPixel)?> ><?=$pixel?></option>
                <?php } ?>
            <?php endforeach; ?>
        </select>
    </p>

    </label>
    </p>
<?php endif; ?>

<?php if ( PixelYourSite\Bing()->enabled() ) : ?>
    <?php
    $value = get_post_meta( $post->ID, '_pys_super_pack_remove_bing_pixel', true );
    if(method_exists(PixelYourSite\Bing(),'getAllPixels')) {
        $pixels = PixelYourSite\Bing()->getAllPixels();
    } else {
        $pixels = PixelYourSite\Bing()->getPixelIDs();// remove in next version
    }
    $disabledPixel = $value;

    ?>
    <p>
        <label>
            <strong>Remove Bing Tag on this <?php echo get_post_type(); ?></strong>
    <p>
        <select name="pys_super_pack_remove_bing_pixel[]" class="form-control pys-pysselect2" data-placeholder="Select Pixel" multiple>
            <option>Select Pixel</option>
            <?php if (is_array($disabledPixel)){ ?>
                <option value="all" <?= selected(in_array("all", $disabledPixel))?>>Remove All</option>
            <?php } else { ?>
                <option value="all" <?= selected("all", $disabledPixel)?>>Remove All</option>
            <?php } ?>
            <?php foreach ($pixels as $pixel) : ?>
                <?php if (is_array($disabledPixel)){ ?>
                    <option value="<?=$pixel?>" <?= selected(in_array( $pixel, $disabledPixel))?> ><?=$pixel?></option>
                <?php } else { ?>
                    <option value="<?=$pixel?>" <?=selected($pixel,$disabledPixel)?> ><?=$pixel?></option>
                <?php } ?>
            <?php endforeach; ?>
        </select>
    </p>

    </label>
    </p>
<?php endif; ?>

<?php if ( PixelYourSite\Pinterest()->enabled() ) : ?>
    <?php
    $value = get_post_meta( $post->ID, '_pys_super_pack_remove_pinterest_pixel', true );
    if(method_exists(PixelYourSite\Pinterest(),'getAllPixels')) {
        $pixels = PixelYourSite\Pinterest()->getAllPixels();
    } else {
        $pixels = PixelYourSite\Pinterest()->getPixelIDs(); // remove in next version
    }

    $disabledPixel = '';

    if($value == "1" && is_array($pixels) && count($pixels) > 0) {
        $disabledPixel = 'all';
    } else if(!empty($value)) {
        $disabledPixel = $value;
    }

    ?>
    <p>
        <label>
            <strong>Remove Pinterest Tag on this <?php echo get_post_type(); ?></strong>
    <p>
        <select name="pys_super_pack_remove_pinterest_pixel[]" class="form-control pys-pysselect2" data-placeholder="Select Pixel" multiple>
            <option>Select Pixel</option>
            <?php if (is_array($disabledPixel)){ ?>
                <option value="all" <?= selected(in_array("all", $disabledPixel))?>>Remove All</option>
            <?php } else { ?>
                <option value="all" <?= selected("all", $disabledPixel)?>>Remove All</option>
            <?php } ?>
            <?php foreach ($pixels as $pixel) : ?>
                <?php if (is_array($disabledPixel)){ ?>
                    <option value="<?=$pixel?>" <?= selected(in_array( $pixel, $disabledPixel))?> ><?=$pixel?></option>
                <?php } else { ?>
                    <option value="<?=$pixel?>" <?=selected($pixel,$disabledPixel)?> ><?=$pixel?></option>
                <?php } ?>
            <?php endforeach; ?>
        </select>
    </p>

    </label>
    </p>
<?php endif; ?>
