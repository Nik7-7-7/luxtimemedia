<div class="ulp-popup-wrapp" id="ulp_popup">
    <div class="ulp-the-popup">

        <div class="ulp-popup-top">
            <div class="title"><?php echo esc_ulp_content($data ['title']);?></div>
            <div class="close-bttn" onclick="ulpClosePopup();"></div>
            <div class="ulp-clear"></div>
        </div>

        <div class="ulp-popup-content">
            <?php echo esc_ulp_content($data ['content']);?>
        </div>

    </div>
</div>
