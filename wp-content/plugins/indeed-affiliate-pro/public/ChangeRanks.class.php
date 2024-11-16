<?php
if (!class_exists('ChangeRanks')) :

class ChangeRanks{
	private $ranks_data;
	private $do_break;
	private $bonus_enabled = FALSE;
	private $ranksPro = false;

	public function __construct($affiliates_ids_in=array()){
		/*
		 * @param [array - selected affiliates]
		 * @return none
		 */
		global $indeed_db;
		$this->ranks_data = $indeed_db->get_ranks(TRUE);
		$this->bonus_enabled = $indeed_db->is_magic_feat_enable('bonus_on_rank');
		if ($this->ranks_data){
			$this->ranks_data = uap_reorder_ranks($this->ranks_data);/// sort array by order attr

			/// RanksPro
			$minimumDate = '';
			$this->ranksPro = get_option('uap_ranks_pro_enabled');
			if ($this->ranksPro){
					$this->ranksPro = (get_option('uap_default_achieve_calculation')=='limited') ? true : false;
			}
			if ($this->ranksPro){
					$minimumDate = get_option('uap_achieve_period');
					$minimumDate = strtotime("-$minimumDate day", time());
			}

			$affiliates = [];
			if ( $this->ranksPro ){
				  $allAffiliates = $indeed_db->getAffiliatesForCheckRanks($affiliates_ids_in);
			}
			$affiliatesWithValues = $indeed_db->get_affiliates_from_referrals($affiliates_ids_in, $minimumDate);/// get affiliates with at least one referral verified

			if ( !empty( $allAffiliates ) ){
					foreach ( $allAffiliates as $key => $array ){
							if ( isset( $affiliatesWithValues[$key]['total_amount'] ) ){
									$allAffiliates[$key]['total_amount'] = $affiliatesWithValues[$key]['total_amount'];
							}
							if ( isset( $affiliatesWithValues[$key]['total_referrals'] ) ){
									$allAffiliates[$key]['total_referrals'] = $affiliatesWithValues[$key]['total_referrals'];
							}
					}
			} else {
					$allAffiliates = $affiliatesWithValues;
			}

			if ($allAffiliates){
				foreach ($allAffiliates as $id=>$array){
					$current_rank = $indeed_db->get_affiliate_rank($id);
					$this->check($id, $array, $current_rank);
				}
			}
		}
	}

	/**
	 * @param int
	 * @param array
	 * @param int
	 * @return none
	 */
	private function check($affiliate_id = 0, $affiliate_data=array(), $current_rank=0)
	{
			global $indeed_db;
			if (empty($affiliate_data['total_amount'])){
					$affiliate_data['total_amount'] = 0;
			}
			if (empty($affiliate_data['total_referrals'])){
					$affiliate_data['total_referrals'] = 0;
			}

			$this->do_break = FALSE;
			$current_rank_detalis = $indeed_db->get_rank($current_rank);
			$ranks = $this->ranks_data;
			$ranks_num = count($ranks);
			$affiliateRankBeforeUpdate = $current_rank;

			if ( empty( $this->ranksPro ) ){
					foreach ($ranks as $key=>$object){
						if ( !empty($object->rank_order) && $object->rank_order<=$current_rank_detalis['rank_order'] ){
								unset($ranks[$key]);
						}
					}
			}

			/// LOOP THROUGH RANKS
			foreach ($ranks as $key=>$object){
					if ( empty( $object->achieve ) && !$this->ranksPro ){
							continue;
					}
					$rank_id = $object->id;
					if ( isset( $object->achieve ) && $object->achieve !== false ){
							$achieve = json_decode($object->achieve, TRUE);
					}
					if (empty($achieve)){
							$achieve = [
								"i" 				=> 1,
								"type_1" 		=> "referrals_number",
								"value_1" 	=> 0
							];
					}

					/// LOOP RANK THROUGH CONDITIONS
					/*
							achieve conditions exemple :
							[
									'type_1' 			=> 'referral_number',
									'value_1'			=> 100,
									'type_2'			=> 'total_amount',
									'value_2'			=> 2000,
									'relation_2'	=> 'and'
							]
					*/
					$doIt = false;
					for ( $i=1; $i<=$achieve['i']; $i++ ){
							$condition = $this->compare($affiliate_data, $achieve['type_' . $i], $achieve['value_' . $i]);
							if ( !empty( $achieve['relation_' . $i] ) ){
								if ( $achieve['relation_' . $i] === 'and' ){
									$doIt = ( $doIt && $condition );
								} else if ( $achieve['relation_' . $i] === 'or' ){
									$doIt = ( $doIt || $condition );
								}
							} else {
								$doIt = $condition;
							}
					}
					if ( $doIt ){
							$this->set_new_rank( $affiliate_id, $rank_id, $affiliateRankBeforeUpdate );
					} else if ( !$this->ranksPro ){
							$this->set_break();
					}

					if ( $this->do_break ){
							break;
					}
			}

			// send notification if its case
			$currentRank = $indeed_db->get_affiliate_rank( $affiliate_id );
			$currentRank = (int)$currentRank;
			$affiliateRankBeforeUpdate = (int)$affiliateRankBeforeUpdate;

			if ( $affiliateRankBeforeUpdate !== $currentRank ){
					$uid = $indeed_db->get_uid_by_affiliate_id( $affiliate_id );
					uap_send_user_notifications( $uid, 'rank_change', $currentRank );//send notification to user
					uap_send_user_notifications( $uid, 'admin_on_aff_change_rank', $currentRank );//send notification to admin
					// pay the bonus
					$indeed_db->pay_bonus_for_rank( $uid, $currentRank );
			}
	}

	private function compare($u_data, $r_type, $r_value){
		/*
		 * @param user data, achieve type, achieve value ... array, string, string
		 * @return int 0 or 1
		 */
		if ($r_type=='referrals_number'){
			if ($r_value<=$u_data['total_referrals']){
				return 1;
			}
		} else if ($r_type=='total_amount'){
			if ($r_value<=$u_data['total_amount']){
				return 1;
			}
		}
		return 0;
	}

	private function set_new_rank( $affiliate_id, $rank_id, $affiliateRankBeforeUpdate ){
		/*
		 * @param int, int
		 * @return none
		 */
		global $indeed_db;
		$currentRank = $indeed_db->get_affiliate_rank($affiliate_id);

		$currentRank = (int)$currentRank;
		$rank_id = (int)$rank_id;
		$affiliateRankBeforeUpdate = (int)$affiliateRankBeforeUpdate;

		if ($currentRank==$rank_id){
				/// affilaite already has this rank
				return;
		}

		/// CHANGE RANK
		$indeed_db->update_affiliate_rank($affiliate_id, $rank_id);

		if ( $affiliateRankBeforeUpdate === $rank_id ){
				return;
		}
		/// PAY THE BONUS
		//$uid = $indeed_db->get_uid_by_affiliate_id($affiliate_id);
		//$indeed_db->pay_bonus_for_rank($uid, $rank_id);
		/// SEND NOTIFICATIONS
		//uap_send_user_notifications($uid, 'rank_change', $rank_id);//send notification to user
		//uap_send_user_notifications($uid, 'admin_on_aff_change_rank', $rank_id);//send notification to admin

	}

	private function set_break(){
		/*
		 * @param none
		 * @return none
		 */
		 $this->do_break = TRUE;
	}

}

endif;
