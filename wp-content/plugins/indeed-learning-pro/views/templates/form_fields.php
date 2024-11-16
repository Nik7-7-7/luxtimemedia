<?php switch ($data['type']):?>
<?php case 'images-multiple-choice':?>
  <?php if (!empty($data['options'])):?>
    <div class="ulp-images-multiple-choice-wrapp">
        <?php foreach ($data['options'] as $key=>$value):?>
            <?php $key++;?>
            <div class="ulp-images-multiple-choice-one-item-wrapper">
              <div class="ulp-images-multiple-choice-one-item">

                  <div class="ulp-images-multiple-choice-one-item-img" style= " background-image: url('<?php echo esc_url($value);?>')"></div>
                  <!--img src="<?php echo esc_url($value);?>" /-->
                  <div class="ulp-images-multiple-choice-one-item-checkbox">
                  	<input type="checkbox" name="<?php echo esc_attr($data['name']) . '[]';?>" value="<?php echo esc_attr($key);?>" />
              	</div>
              </div>
            </div>
        <?php endforeach;?>
    </div>
  <?php endif;?>
<?php break;?>
<?php case 'images-single-choice':?>
    <input type="hidden" name="<?php echo esc_attr($data['name']);?>" value="" />
    <?php if (!empty($data['options'])):?>
      <div class="ulp-images-single-choice-wrapp">
        <?php foreach ($data['options'] as $key=>$value):?>
            <?php $key++;?>

        	  <div class="ulp-images-single-choice-one-item-wrapper">
            	<div class="ulp-images-single-choice-one-item">
              	  <div class="ulp-images-single-choice-one-item-img" style= " background-image: url('<?php echo esc_url($value);?>')" data-value="<?php echo esc_attr($key);?>" ></div>
              	  <!--img src="<?php echo esc_url($value);?>" /-->
            	</div>
            </div>
        <?php endforeach;?>
      </div>
    <?php endif;?>

    <?php
    wp_enqueue_script( 'ulp-image_single_choice-type', ULP_URL . 'assets/js/form_field-image_single_choice.js', array('jquery'), 3.6, false );
    ?>
    <span class="ulp-js-form-field-image-single-choice"
          data-name="<?php echo esc_attr($data['name']);?>"
    ></span>

<?php break;?>
<?php case 'matching':?>
<div class="ulp-micro-questions">
  <?php if ($data['questions']):?>
    <?php foreach ($data['questions'] as $question):?>
    <div class="row ulp-matching-question">
        <div class="ulp-micro-question js-ulp-micro-question"><?php echo esc_html($question);?></div>
        <div class="ulp-micro-answer">
            <input type="hidden" name="<?php echo esc_attr($data['name']);?>[]" value="" data-field_type="matching" />
        </div>
    </div>
    <?php endforeach;?>
  <?php endif;?>
</div>

<div class="ulp-micro-answers-possible">
  <?php if ($data['answers']):?>
    <?php foreach ($data['answers'] as $answer):?>
        <div class="ulp-item" title="<?php esc_html_e('Move the box on the right position','ulp');?>" data-answer_key="<?php echo esc_attr($answer);?>"><?php echo esc_html($answer);?></div>
    <?php endforeach;?>
  <?php endif;?>
</div>
<div class="ulp-clear"></div>
<span class="ulp-js-form-field-micro-answer"
      data-title="<?php esc_html_e('Move the box on the right position','ulp');?>"
      data-message="<?php esc_html_e('Double click to remove', 'ulp');?>" ></span>

<?php
wp_enqueue_script('jquery-ui-droppable');
wp_enqueue_script( 'ulp-matching-type', ULP_URL . 'assets/js/form_fields-matching_type.js', array('jquery'), 3.6, false );
?>

<?php break;?>
<?php endswitch;?>
