<?php wp_enqueue_script( 'ulp_admin_questions_answers', ULP_URL . 'assets/js/admin_questions_answers.js', ['jquery'], 3.6, false );?>
<?php do_action('ulp_admin_before_meta_box_question_answer');?>

<?php
$custom_css = '';
$custom_css .=  "#free_choice_type_of_answer{". (($data['answer_type']==1) ? 'display: block;' : 'display:none;')."} ";
$custom_css .=  "#fill_in_type_of_answer{". (($data['answer_type']==7) ? 'display: block;' : 'display:none;')."} ";
$custom_css .=  "#single_choice_type_of_answer{". (($data['answer_type']==2) ? 'display: block;' : 'display:none;')."} ";
$custom_css .=  "#multi_choice_type_of_answer{". (($data['answer_type']==3) ? 'display: block;' : 'display:none;')."} ";
$custom_css .=  "#true_or_false_type_of_answer{". (($data['answer_type']==4) ? 'display: block;' : 'display:none;')."} ";
$custom_css .=  "#essay_type_of_answer{". (($data['answer_type']==5) ? 'display: block;' : 'display:none;')."} ";
$custom_css .=  "#sorting_type_of_answer{". (($data['answer_type']==6) ? 'display: block;' : 'display:none;')."} ";
$custom_css .=  "#image_single_type_of_answer{". (($data['answer_type']==8) ? 'display: block;' : 'display:none;')."} ";
$custom_css .=  "#image_multiple_type_of_answer{". (($data['answer_type']==9) ? 'display: block;' : 'display:none;')."} ";
$custom_css .=  "#matching_type_of_answer{". (($data['answer_type']==10) ? 'display: block;' : 'display:none;')."} ";

wp_register_style( 'dummy-handle', false );
wp_enqueue_style( 'dummy-handle' );
wp_add_inline_style( 'dummy-handle', $custom_css );
?>
<div class="ulp-inside-item ulp-instructor-question-type">
  <div class="ulp-instructor-edit-row">
   <div class="ulp-inst-col-6">
	<h4><?php esc_html_e('Type of Question', 'ulp');?></h4>
    <div>
				<select name='answer_type' id="ulp_answer_type"  class="ulp-form-control m-bot15">
					<?php
						$opt = array(
									1 => esc_html__('Free choice', 'ulp'),
									7 => esc_html__('Fill in blank', 'ulp'),
									2 => esc_html__('One Choice', 'ulp'),
									3 => esc_html__('Multi Choice', 'ulp'),
									4 => esc_html__('True or False', 'ulp'),
									5 => esc_html__('Essay', 'ulp'),
									6 => esc_html__('Sorting answers', 'ulp'),
									8 => esc_html__('Choose image - single choice', 'ulp'),
									9 => esc_html__('Choose image - multiple choice', 'ulp'),
									10 => esc_html__('Matching', 'ulp'),
						);
					?>
					<?php foreach ($opt as $k=>$v):?>
						<?php $selected = ($k==$data['answer_type']) ? 'selected' : '';?>
						<option value='<?php echo esc_attr($k);?>' <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
					<?php endforeach;?>
				</select>
    </div>
  </div>
 </div>
</div>
<div class="ulp-instructor-edit-line-break"></div>
<div id="free_choice_type_of_answer" class="ulp_div_to_show_hide">
  <div class="ulp-inside-item">
  <div class="ulp-instructor-edit-row">
   <div class="ulp-inst-col-6">
    <h4><?php esc_html_e('Correct Answer', 'ulp');?></h4>
     <div class="ulp-form-section">
    <div class="ulp-input-group ulp-input-group-max">
    <span class="ulp-input-group-addon" id="basic-addon1"><?php esc_html_e('answer:', 'ulp');?></span>
	<input type='text'  class="ulp-form-control" name='answer_value' value='<?php echo stripslashes($data['answer_value']);?>' />
    </div>
    </div>
  </div>
 </div>
 </div>
</div>
<div id="fill_in_type_of_answer" class="ulp_div_to_show_hide">
  <div class="ulp-inside-item">
  <div class="ulp-instructor-edit-row">
   <div class="ulp-inst-col-6">
    <h4><?php esc_html_e('Required Answer', 'ulp');?></h4>
    <div  class="ulp-input-group-extra-max">
		<textarea name='answer_value_required' class="ulp-form-control" rows="10" cols="85"><?php echo stripslashes($data['answer_value_required']);?></textarea>
     <div class="ulp-margin-top">
	 <p><i><?php esc_html_e('You can provide more required answers. Set the right answer between {}. If more possible rights exist split them by |', 'ulp');?></i></p>
     <p><strong><?php esc_html_e('Examples:', 'ulp');?></strong></p>
     <p><?php esc_html_e('In WordPress are you able to install additional <strong>{themes|plugins}</strong>', 'ulp');?></p>
     <p><?php esc_html_e('The <strong>{bear|wolf}</strong> is bigger than a <strong>{rabbit}</strong>', 'ulp');?></p>
    </div>
    </div>
  </div>
 </div>
 </div>
</div>
<div id="multi_choice_type_of_answer" class="ulp_div_to_show_hide">
  <div class="ulp-inside-item">
  <div class="ulp-instructor-edit-row">
   <div class="ulp-inst-col-6">
	<h4><?php esc_html_e('Multiple Choices', 'ulp');?></h4>
	<?php
		$checkbox_correct_values = array();
		$checkbox_correct_values = $data['answers_multiple_answers_correct_answers'];
		if (empty($data['answers_multiple_answers_possible_values'])){
			$data['answers_multiple_answers_possible_values'] = array('exemple');
		}
	?>
	<div id="ulpmultipleanswers_wrapp">
		<?php foreach ($data['answers_multiple_answers_possible_values'] as $k => $v ):?>
			<div class="ulp-input-group ulp-input-group-max">
				<input type="text" class="ulp-form-control" value="<?php echo esc_attr($v);?>" name="answers_multiple_answers_possible_values[]" />
				<span class="ulp-delete-parent ulp-input-group-addon"><i class="fa-ulp fa-remove-ulp ulp-pointer"></i></span>
			</div>
		<?php endforeach;?>
	</div>
	<div id="ulp_add_values_for_multiple_answers" class="ulp_add_value"><?php esc_html_e('Add new Option', 'ulp');?></div>
	<h4><?php esc_html_e('Correct Answers', 'ulp');?></h4>
    <div class="ulp-input-group ulp-input-group-max">
    <span class="ulp-input-group-addon" id="basic-addon1"><?php esc_html_e('answers:', 'ulp');?></span>
    <input type="text" name="answers_multiple_answers_correct_answers" class="ulp-form-control" id="answers_multiple_answers_correct_answers" value="<?php echo esc_attr($data['answers_multiple_answers_correct_answers']);?>" />
    </div>
	<div class="ulp-questions-answers-message"><?php esc_html_e("write correct values between comma ',' .", 'ulp');?></div>
  </div>
 </div>
 </div>
</div>
<div id="single_choice_type_of_answer" class="ulp_div_to_show_hide">
  <div class="ulp-inside-item">
  <div class="ulp-instructor-edit-row">
   <div class="ulp-inst-col-6">
    <h4><?php esc_html_e('Single Choices', 'ulp');?></h4>
	<?php
		if (empty($data['answers_single_answer_possible_values'])){
			$data['answers_single_answer_possible_values'] = array('exemple');
		}
	?>
	<div id="ulpsingleanswers_wrapp" >
		<?php foreach ($data['answers_single_answer_possible_values'] as $value):?>
			 <div class="ulp-input-group ulp-input-group-max">
				<input type="text" onkeyup=""  class="ulp-form-control" value="<?php echo esc_attr($value);?>" name="answers_single_answer_possible_values[]" />
				<span class="ulp-delete-parent ulp-input-group-addon"><i class="fa-ulp fa-remove-ulp ulp-pointer"></i></span>
			</div>
		<?php endforeach;?>
	</div>
	<div id="ulp_add_values_for_single_answers" class="ulp_add_value"><?php esc_html_e('Add new Option', 'ulp');?></div>
	<h4><?php esc_html_e('Correct Answer', 'ulp');?></h4>
    <div class="ulp-input-group ulp-input-group-max">
    <span class="ulp-input-group-addon" id="basic-addon1"><?php esc_html_e('answer:', 'ulp');?></span>
    <input type="text" name="answers_single_answer_correct_value" class="ulp-form-control" id="answers_single_answer_correct_value" value="<?php echo esc_attr($data['answers_single_answer_correct_value']);?>" />
    </div>
   </div>
 </div>
 </div>
</div>
<div id="true_or_false_type_of_answer" class="ulp_div_to_show_hide">
 <div class="ulp-inside-item">
  <div class="ulp-instructor-edit-row">
   <div class="ulp-inst-col-6">
	<h4><?php esc_html_e('Correct Answer', 'ulp');?></h4>
	<div>
		<input type="radio" name="answer_value_for_bool" value="0" <?php echo ($data['answer_value_for_bool']==0) ? 'checked' : '';?> /> <?php esc_html_e('False', 'ulp');?>
	</div>
	<div>
		<input type="radio" name="answer_value_for_bool" value="1" <?php echo ($data['answer_value_for_bool']==1) ? 'checked' : '';?> /> <?php esc_html_e('True', 'ulp');?>
	</div>
  </div>
  </div>
 </div>
</div>
<div id="essay_type_of_answer" class="ulp_div_to_show_hide">
		<div class="ulp-inside-item">
          <div class="ulp-instructor-edit-row">
              <div class="ulp-inst-col-6">
				<h4><?php esc_html_e("Keywords", 'ulp');?></h4>
                <div class="form-group">
				<textarea name="answer_value_for_essay" class="ulp-form-control text-area" rows="10" ><?php echo stripslashes($data['answer_value_for_essay']);?></textarea>
              	</div>
              </div>
          </div>
      </div>
</div>
<div id="sorting_type_of_answer" class="ulp_div_to_show_hide">
		<div class="ulp-inside-item">
			  <div class="ulp-instructor-edit-row">
					   <div class="ulp-inst-col-6">
						    <h4><?php esc_html_e('Sorting answers', 'ulp');?></h4>
								<?php
									if (empty($data['answers_sorting_type'])){
										$data['answers_sorting_type'] = array('exemple', 'exemple 2');
									}
								?>
								<div id="ulpsrotingtype_wrapp" >
									<?php foreach ($data['answers_sorting_type'] as $value):?>
										 <div class="ulp-input-group ulp-the-sorting-answers ulp-input-group-max">
											 <span class="ulp-input-group-addon"><i class="fa-ulp fa-sort-ulp"></i></span>
											<input type="text" onkeyup=""  class="ulp-form-control" value="<?php echo esc_attr($value);?>" name="answers_sorting_type[]" />
											<span class="ulp-delete-parent ulp-input-group-addon"><i class="fa-ulp fa-remove-ulp ulp-pointer"></i></span>
										</div>
									<?php endforeach;?>
								</div>
								<div id="ulp_add_values_for_sorting_type" class="ulp_add_value"><?php esc_html_e('Add new Value', 'ulp');?></div>
				   </div>
			 </div>
	 </div>
</div>
<div id="image_single_type_of_answer" class="ulp_div_to_show_hide">
  <div class="ulp-inside-item">
		  <div class="ulp-instructor-edit-row">
		   <div class="ulp-inst-col-6">
			    <h4><?php esc_html_e('Choose Image - single choice', 'ulp');?></h4>
				<?php
					if (empty($data['image_answers_single_answer_possible_values'])){
						$data['image_answers_single_answer_possible_values'] = ['...upload an image from Media Library'];
					}
				?>
				<div id="ulp_image_single_answers_wrapp" >
					<?php $i = 1;?>
					<?php foreach ($data['image_answers_single_answer_possible_values'] as $value):?>
						<div class="ulp-input-group ulp-input-group-max ulp-margin-bottom">
							<span class="ulp-input-group-addon" id="basic-addon1"><?php esc_html_e('ID:', 'ulp');?><span class="ulp_answer_num"><?php echo esc_html($i);$i++;?></span></span>
							<input type="text" onkeyup=""  class="ulp-form-control" value="<?php echo esc_attr($value);?>" name="image_answers_single_answer_possible_values[]" onClick="openMediaUp(this);" placeholder="...upload an image from Media Library" />
							<span class="ulp-delete-parent ulp-input-group-addon"><i class="fa-ulp fa-remove-ulp ulp-pointer"></i></span>
						</div>
					<?php endforeach;?>
				</div>
				<div id="ulp_add_values_for_image_single_answers" class="ulp_add_value"><?php esc_html_e('Add new Option', 'ulp');?></div>
				<h4><?php esc_html_e('Correct Answer', 'ulp');?></h4>
			    <div class="ulp-input-group ulp-input-group-max">
			    		<span class="ulp-input-group-addon" id="basic-addon1"><?php esc_html_e('Right Answer:', 'ulp');?></span>
			    		<input type="text" name="image_answers_single_answer_correct_value" class="ulp-form-control" id="image_answers_single_answer_correct_value" value="<?php echo esc_attr($data['image_answers_single_answer_correct_value']);?>" />
			    </div>
                <div class="ulp-margin-top"><strong><i><?php esc_html_e('Write the ID of the right image', 'ulp');?></i></strong></div>
		   </div>
		 </div>
 </div>
</div>
<div id="image_multiple_type_of_answer" class="ulp_div_to_show_hide">
	  <div class="ulp-inside-item">
				<div class="ulp-instructor-edit-row">
					   <div class="ulp-inst-col-6">
								<h4><?php esc_html_e('Choose Image - multiple choice', 'ulp');?></h4>
								<?php
									$checkbox_correct_values = [];
									$checkbox_correct_values = $data['image_answers_multiple_answers_possible_values'];
									if (empty($data['image_answers_multiple_answers_possible_values'])){
										$data['image_answers_multiple_answers_possible_values'] = ['...upload an image from Media Library'];
									}
								?>
								<div id="ulp_image_multiple_answers_wrapp">
									<?php $i = 1;?>
									<?php foreach ($data['image_answers_multiple_answers_possible_values'] as $k => $v ):?>
										<div class="ulp-input-group ulp-input-group-max ulp-margin-bottom">
											<span class="ulp-input-group-addon" id="basic-addon1"><?php esc_html_e('ID:', 'ulp');?><span class="ulp_answer_num"><?php echo esc_html($i);$i++;?></span></span>
											<input type="text" class="ulp-form-control" value="<?php echo esc_attr($v);?>" name="image_answers_multiple_answers_possible_values[]" onClick="openMediaUp(this);" placeholder="...upload an image from Media Library"/>
											<span class="ulp-delete-parent ulp-input-group-addon"><i class="fa-ulp fa-remove-ulp ulp-pointer"></i></span>
										</div>
									<?php endforeach;?>
								</div>
								<div id="ulp_add_values_for_image_multiple_answers" class="ulp_add_value"><?php esc_html_e('Add new Option', 'ulp');?></div>
								<h4><?php esc_html_e('Correct Answers', 'ulp');?></h4>
							    <div class="ulp-input-group ulp-input-group-max">
							    <span class="ulp-input-group-addon" id="basic-addon1"><?php esc_html_e('Right Answers:', 'ulp');?></span>
							    <input type="text" name="image_answers_multiple_answers_correct_answers" class="ulp-form-control" id="image_answers_multiple_answers_correct_answers" value="<?php echo esc_attr($data['image_answers_multiple_answers_correct_answers']);?>" />
								</div>
								<div class="ulp-margin-top ulp-questions-answers-message"><strong><i><?php esc_html_e("Write the IDs of the right images separated by comma ',' .", 'ulp');?></i></strong></div>
					  </div>
			 	</div>
 	  </div>
</div>
<div id="matching_type_of_answer" class="ulp_div_to_show_hide">
	<div class="ulp-inside-item">
			<div class="ulp-instructor-edit-row">
					 <div class="ulp-inst-col-6">
							<h4><?php esc_html_e('Add Matched data', 'ulp');?></h4>
							<?php
								if (empty($data['matching_micro_questions'])){
									$data['matching_micro_questions'] = ['examples'];
								}
							?>
							<div id="ulp_matching_qanda_wrapp">
								<?php foreach ($data['matching_micro_questions'] as $k => $v ):?>
									<div class="ulp-input-group ulp-input-group-extra-max ulp-margin-bottom">
										<input type="text" class="ulp-form-control" value="<?php echo esc_attr($v);?>" name="matching_micro_questions[]" placeholder="<?php esc_html_e('Question', 'ulp');?>" />
										<input type="text" class="ulp-form-control ulp-questions-answers-matching" value="<?php echo (isset($data['matching_micro_questions_answers'][$k])) ? $data['matching_micro_questions_answers'][$k] : '';?>"
										 name="matching_micro_questions_answers[]" placeholder="<?php esc_html_e('Answer', 'ulp');?>" />
										<span class="ulp-delete-parent ulp-input-group-addon"><i class="fa-ulp fa-remove-ulp ulp-pointer"></i></span>
									</div>
								<?php endforeach;?>
							</div>
							<div id="ulp_add_values_for_matching" class="ulp_add_value"><?php esc_html_e('Add new Option', 'ulp');?></div>
					</div>
			</div>
	</div>
</div>
<span class="ulp-js-instructor-questions-answers" ></span>

<?php do_action('ulp_admin_after_meta_box_question_answer');?>
