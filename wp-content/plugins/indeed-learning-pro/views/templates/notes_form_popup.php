<div class="ulp-popup-wrapp" id="ulp_popup">
    <div class="ulp-the-popup">

        <div class="ulp-popup-top">
            <div class="title"><?php esc_html_e('Take a Note', 'ulp');?></div>
            <div class="close-bttn" onclick="ulpClosePopup();"></div>
            <div class="ulp-clear"></div>
        </div>

        <div class="ulp-popup-content">
            <div class="ulp-popup-create-note-row">
                <label class="ulp-popup-label"><?php esc_html_e('Note Title', 'ulp');?></label>
                <input type="text" id="note_title" />
            </div>
            <div class="ulp-popup-create-note-row">
                <label class="ulp-popup-label"><?php esc_html_e('Content', 'ulp');?></label>
                <textarea id="note_content"></textarea>
            </div>
            <div class="ulp-popup-create-note-row ulp-text-aling-center">
                <button id="note_save" onClick="ulpNoteSave();"><?php esc_html_e('Save your Note', 'ulp');?></button>
            </div>
        </div>

    </div>
</div>
