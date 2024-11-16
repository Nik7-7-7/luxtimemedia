<?php

abstract class AIKIT_Page {
    protected function _drop_down($id, $label, $options, $selected = null, $description = null)
    {
        ?>
        <div class="form-floating">
            <select class="form-select" id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($id); ?>">
                <?php foreach ($options as $key => $option) { ?>
                    <option value="<?php echo esc_attr($key); ?>" <?php echo $selected == $key ? 'selected' : ''; ?>><?php echo esc_html( $option ); ?></option>
                <?php } ?>
            </select>
            <label for="<?php echo esc_attr($id); ?>"><?php echo esc_html( $label ); ?></label>
            <?php if ($description) { ?>
                <small><?php echo esc_html( $description ); ?></small>
            <?php } ?>
        </div>
        <?php
    }

    protected function _radio_button_set($id, $span_label, $value_to_label_map, $checked_value, $description = null, $information_link = null, $information_link_text = null, $is_disabled = false) {

        ?>
        <span class="me-3"><?php echo esc_html( $span_label ); ?></span>
        <?php
        foreach ($value_to_label_map as $value => $label) {
            ?>
            <div class="form-check form-check-inline">
                <input class="form-check-input <?php echo esc_attr( $id ); ?>" type="radio" name="<?php echo esc_attr( $id ); ?>" id="<?php echo esc_attr( $id ); ?>-<?php echo esc_attr( $value ); ?>" value="<?php echo esc_attr( $value ); ?>" <?php echo $checked_value == $value ? 'checked' : ''; ?> <?php echo $is_disabled ? 'disabled' : ''; ?>>
                <label class="form-check-label" for="<?php echo esc_attr( $id ); ?>-<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></label>
            </div>
            <?php
        }

        if ($information_link) {
            ?>
            <a href="<?php echo esc_attr( $information_link ); ?>" target="_blank" class="aikit-info-link"><i class="bi bi-info-circle-fill"></i> <?php echo esc_html( $information_link_text ); ?></a>
            <?php
        }

        if ($description) { ?>
            <p><small><?php echo esc_html( $description ); ?></small></p>
        <?php }
    }

    protected function _text_area($id, $label, $value = '', $description = null, $escape_description = true)
    {
        ?>
        <div class="form-floating">
            <textarea class="form-control" placeholder="<?php echo esc_attr( $label ); ?>" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>" ><?php echo esc_html( $value ); ?></textarea>
            <label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
            <?php if ($description) { ?>
                <small><?php echo $escape_description ? esc_html( $description ) : $description; ?></small>
            <?php } ?>
        </div>
        <?php
    }

    protected function _check_box($id, $label, $checked = false, $description = null) {
        ?>
        <input type="checkbox" class="form-check-input" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>" <?php echo $checked ? 'checked' : ''; ?>/>
        <label class="form-check-label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
        <br>
        <?php if ($description) { ?>
            <small><?php echo esc_html( $description ); ?></small>
        <?php } ?>
        <?php
    }

    protected function _text_box($id, $label, $data_setting = null, $type='text', $value = '', $min = null, $max = null, $step = null, $description = null, $validation_message = null, $disabled = false)
    {
        ?>
        <div class="form-floating">
            <input type="<?php echo esc_attr( $type ); ?>" class="form-control" placeholder="<?php echo esc_html( $label ); ?>" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $value ); ?>" <?php echo $min !== null ? 'min="' . esc_attr( $min ) . '"' : ''; ?> <?php echo $max !== null ? 'max="' . esc_attr( $max ) . '"' : ''; ?> <?php echo $step !== null ? 'step="' . esc_attr( $step ) . '"' : ''; ?>  <?php echo $data_setting !== null ? 'data-setting="' . esc_attr( $data_setting ) . '"' : ''; ?> <?php echo $validation_message !== null ? 'data-validation-message="' . esc_attr( $validation_message ) . '"' : ''; ?> <?php echo $disabled ? 'disabled' : ''; ?>/>
            <label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
        </div>
        <?php if ($description) { ?>
            <small><?php echo esc_html( $description ); ?></small>
        <?php } ?>
        <?php
    }

    protected function _slider($id, $label, $data_setting, $value = '', $min = null, $max = null, $step = null)
    {
        ?>
        <label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
        <input type="range" class="form-range" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $value ); ?>" <?php echo $min !== null ? 'min="' . esc_attr( $min ) . '"' : ''; ?> <?php echo $max !== null ? 'max="' . esc_attr( $max ) . '"' : ''; ?> <?php echo $step !== null ? 'step="' . esc_attr( $step ) . '"' : ''; ?>  data-setting="<?php echo $data_setting ?>"/>

        <?php
    }

    protected function _color_picker($id, $label, $data_setting, $value = '', $reset_value = null)
    {
        ?>
        <label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
        <input type="color" class="form-control" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $value ); ?>"  data-setting="<?php echo $data_setting ?>" <?php echo $reset_value !== null ? 'data-reset-value="' . esc_attr( $reset_value ) . '"' : ''; ?>/>

        <?php
    }
}
