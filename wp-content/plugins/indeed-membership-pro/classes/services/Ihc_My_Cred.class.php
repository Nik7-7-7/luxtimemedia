<?php
if (!class_exists('Ihc_My_Cred') && class_exists('myCRED_Hook')):
class Ihc_My_Cred extends myCRED_Hook{

	/*
	 * @param array
	 * @return none
	 */
	public function __construct($hook_prefs=array(), $type='mycred_default'){
		$levels = \Indeed\Ihc\Db\Memberships::getAll();
		if ($levels){
			foreach ($levels as $lid=>$temp_array){
				$defaults['acquire_level_' . $lid] = array(
							'creds' => 0,
							'log'   => '%plural% for acquire level ' . $temp_array['label'],
							'run_everytime' => 0,
				);
			}
			if (!empty($defaults)){
				parent::__construct(array(
					'id' => 'ihc_mycred',
					'defaults' => $defaults,
				), $hook_prefs, $type);
			}
		}
	}


	/*
	 * @param none
	 * @return none
	 */
	public function run(){
		add_action('ihc_action_after_subscription_activated', array($this, 'give_points_for_level_acquire'), 1, 3);
	}


	/*
	 * @param int, int
	 * @return none
	 */
	public function give_points_for_level_acquire($uid=0, $lid=-1, $first_time=TRUE){
		if ($uid && $lid>-1){
			if ($this->core->exclude_user($uid)){
				return;
			}
			$key = 'acquire_level_' . $lid;

			/// run just once ??
			if (!$first_time && !$this->prefs[$key]['run_everytime']){
				return;
			}

			if ($this->prefs[$key]['creds']>0){
				$this->core->add_creds(
					$key,
					$uid,
					$this->prefs[$key]['creds'],
					$this->prefs[$key]['log'],
					$lid,
					array('ref_type'=>'post'),
					$this->mycred_type
				);
			}
		}
	}


	/*
	 * @param array
	 * @return array
	 */
	public function add_references($references=array()){
		$levels = \Indeed\Ihc\Db\Memberships::getAll();
		if ($levels){
			foreach ($levels as $lid=>$temp_array){
				$references['acquire_level_' . $lid] = esc_html__('Acquire Membership', 'ihc');
			}
		}
		return $references;
	}


	/*
	 * Print settings form
	 * @param none
	 * @return none
	 */
	public function preferences(){
		$levels = \Indeed\Ihc\Db\Memberships::getAll();
		if ($levels):
			foreach ($levels as $lid=>$temp_array):
				$key = 'acquire_level_' . $lid;
				?>
				<div class="ihc-mycred-box">
					<h2><?php echo esc_html__("Membership '", 'ihc') .  esc_html($temp_array['label']) . esc_html("' ");?></h2>
					<label class="subheader" for="<?php echo esc_attr($this->field_id(array($key=>'creds')));?>"><?php echo esc_html__('Points', 'ihc');?></label>
					<ol>
						<li>
							<div class="h2">
								<input type="number" min="0" name="<?php echo esc_attr($this->field_name(array($key=>'creds')));?>" id="<?php echo esc_attr($this->field_id(array($key=>'creds'))); ?>" value="<?php echo esc_attr($this->core->number($this->prefs[$key]['creds']));?>" />
							</div>
						</li>
					</ol>
					<label class="subheader" for="<?php echo esc_attr($this->field_id(array($key=>'log')));?>"><?php echo esc_html__('Log Template', 'ihc');?></label>
					<ol>
						<li>
							<div class="h2">
								<input type="text" name="<?php echo esc_attr($this->field_name(array($key=>'log')));?>" id="<?php echo esc_attr($this->field_id(array($key=>'log'))); ?>" value="<?php echo esc_attr($this->prefs[$key]['log']);?>" class="long" placeholder="%plural% for <?php echo esc_attr($temp_array['label']); ?> purchased" />
							</div>
						</li>
					</ol>

					<label class="subheader" for="<?php echo esc_attr($this->field_id(array($key=>'custom_option')));?>"><?php echo esc_html__('Reward', 'ihc');?></label>
					<ol>
						<li>
							<div class="h2">
								<select name="<?php echo esc_attr($this->field_name(array($key=>'run_everytime')));?>" id="<?php echo esc_attr($this->field_id(array($key=>'run_everytime'))); ?>" class="long" >
									<?php $selected = ($this->prefs[$key]['run_everytime']==0) ? 'selected' : '';?>
									<option <?php echo esc_attr($selected);?> value="0" ><?php esc_html_e('Just Once', 'ihc');?></option>
									<?php $selected = ($this->prefs[$key]['run_everytime']==1) ? 'selected' : '';?>
									<option <?php echo esc_attr($selected);?> value="1" ><?php esc_html_e('Everytime', 'ihc');?></option>
								</select>
							</div>
						</li>
					</ol>
					<span class="description"><?php echo esc_html__('Available only for Recurring Subscriptions', 'ihc');?></span>
				</div>

				<?php
			endforeach;
		endif;
	}

}
endif;
