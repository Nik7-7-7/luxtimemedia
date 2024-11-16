<div class="ulp-quiz-questions-select">

<?php if ($data['all_questions']):?>

		<select id="ulp_select_questions" multiple="multiple" name="questions_list[]" class="ulp-select-questions">

				<?php if ($data['all_questions']):?>

						<?php foreach ($data['all_questions'] as $subarray):?>

								<?php

										if ( isset($data['quiz_questions']) && is_array($data['quiz_questions']) && in_array($subarray['ID'], $data['quiz_questions'])){


												$labels [$subarray['ID']] = mb_substr( strip_tags($subarray['post_content']), 0 , 110);

												$post_types [$subarray['ID']] = $subarray['post_type'];

												continue;

										}

								 ?>

								<option value="<?php echo esc_attr($subarray['ID']);?>" data-post_type="<?php echo esc_attr($subarray['post_type']);?>"><?php echo mb_substr( strip_tags($subarray['post_content']), 0 , 110);?></option>

						<?php endforeach;?>

						<?php if (isset($data['quiz_questions'])):?>

									<?php foreach ($data['quiz_questions'] as $item_id):?>

											<?php if ( !isset( $labels[$item_id] ) || !isset( $post_types[$item_id] ) ) { continue; } ?>

											<option value="<?php echo esc_attr($item_id);?>" data-post_type="<?php echo esc_attr($post_types[$item_id]);?>" selected><?php echo esc_html($labels[$item_id]);?></option>

									<?php endforeach;?>

						<?php endif;?>

				<?php endif;?>

		</select>

<?php endif;?>
<span class="ulp-js-quiz-questions-select-meta-box" ></span>


</div>

<?php wp_enqueue_script('ulp_ui_multiselect_js');?>
