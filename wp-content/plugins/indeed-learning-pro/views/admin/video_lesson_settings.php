<div>
    <div class="ulp-inside-item">
        <h3><?php esc_html_e( 'Turn the Lesson into a Video one', 'ulp' );?></h3>
        <div>
        <?php esc_html_e( 'Instead of standard content a Video from Youtube or Videmo may play for an interactivity approach', 'ulp' );?>
        </div>
        <label class="ulp_label_shiwtch ulp-switch-button-margin">
          <?php $checked = ($data['settings']['ulp_lesson_is_video']) ? 'checked' : '';?>
          <input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#ulp_lesson_is_video');" <?php echo esc_attr($checked);?> />
          <div class="switch ulp-display-inline"></div>
        </label>
        <input type="hidden" name="ulp_lesson_is_video" value="<?php echo esc_attr($data['settings']['ulp_lesson_is_video']);?>" id="ulp_lesson_is_video" />
    </div>
    <div class="ulp-line-break"></div>
    <div class="ulp-inside-item">
        <h4><?php esc_html_e( 'Setup Video', 'ulp' );?></h4>
        <p><?php esc_html_e( 'Only Youtube and Vimeo sources are supported. Provide the full video URL.', 'ulp' );?></p>
        <div class="row">
            <div class="col-xs-6">
        <div class="input-group">
         <span class="input-group-addon" id="basic-addon1"><?php esc_html_e( 'Video URL', 'ulp' );?> </span>
        <input type="text" class="form-control"  name="ulp_lesson_video_target" value="<?php echo esc_attr($data['settings']['ulp_lesson_video_target']);?>" />
    	</div>
    </div>
  </div>
    </div>
    <div class="ulp-line-break"></div>
    <div class="ulp-inside-item">
      <div class="row">
          <div class="col-xs-6">
        <div class="input-group">
         <span class="input-group-addon" id="basic-addon1"><?php esc_html_e( 'Video Width', 'ulp' );?> </span>
        <input type="text" class="form-control"  name="ulp_lesson_video_width" value="<?php echo esc_attr($data['settings']['ulp_lesson_video_width']);?>" />
      </div>
    </div>
  </div>
    </div>
    <div class="ulp-inside-item">
      <div class="row">
          <div class="col-xs-6">
        <div class="input-group">
         <span class="input-group-addon" id="basic-addon1"><?php esc_html_e( 'Video Height', 'ulp' );?> </span>
        <input type="text" class="form-control"  name="ulp_lesson_video_height" value="<?php echo esc_attr($data['settings']['ulp_lesson_video_height']);?>" />
      </div>
    </div>
  </div>
    </div>

    <div class="ulp-line-break"></div>
    <div class="ulp-inside-item">
    	<h4><?php esc_html_e( 'Additional Settings', 'ulp' );?></h4>
        <h5><?php esc_html_e( 'Autoplay', 'ulp' );?></h5>
        <p><?php esc_html_e( 'Video will auto-start once the lessons is loaded.', 'ulp' );?></p>
        <label class="ulp_label_shiwtch ulp-switch-button-margin">
          <?php $checked = ($data['settings']['ulp_lesson_video_autoplay']) ? 'checked' : '';?>
          <input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#ulp_lesson_video_autoplay');" <?php echo esc_attr($checked);?> />
          <div class="switch ulp-display-inline"></div>
        </label>
        <input type="hidden" name="ulp_lesson_video_autoplay" value="<?php echo esc_attr($data['settings']['ulp_lesson_video_autoplay']);?>" id="ulp_lesson_video_autoplay" />
    </div>
    <div>
        <h5><?php esc_html_e( 'Autocomplete lesson when video is ended', 'ulp' );?></h5>
        <p><?php esc_html_e( 'Lesson will be automatically Completed when Video completes', 'ulp' );?></p>
        <label class="ulp_label_shiwtch ulp-switch-button-margin">
          <?php $checked = ($data['settings']['ulp_lesson_video_autocomplete']) ? 'checked' : '';?>
          <input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#ulp_lesson_video_autocomplete');" <?php echo esc_attr($checked);?> />
          <div class="switch ulp-display-inline"></div>
        </label>
        <input type="hidden" name="ulp_lesson_video_autocomplete" value="<?php echo esc_attr($data['settings']['ulp_lesson_video_autocomplete']);?>" id="ulp_lesson_video_autocomplete" />
    </div>
</div>
