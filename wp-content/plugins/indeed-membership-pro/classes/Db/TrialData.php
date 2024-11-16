<?php
namespace Indeed\Ihc\Db;
if (!defined('ABSPATH')){
   exit();
}
class TrialData
{
	/// input
	private $lid 								= 0;
	private $country 						= '';
	private $state 							= '';
	/// output
	private $trialPrice 				= 0;
	private $initalTrialPrice 	= 0;
	private $taxes 							= 0;
	private $durationType 			= '';
	private $durationValue 			= '';
	private $trialActive 				= false;
	private $currency 					= '';
	private $durationTimeType 	= '';

	public function __construct(){}

	public function setVariable($name, $value)
	{
		$this->$name = $value;
		return $this;
	}

	public function run()
	{
		$levelData = ihc_get_level_by_id($this->lid);
		if ($levelData['payment_type']!='payment'){
			$this->trialPrice = 0;
			return $this;
		}

		/// set the price
		if (isset($levelData['access_trial_price']) && $levelData['access_trial_price']!==''){
			$this->trialPrice = $levelData['access_trial_price'];
			$this->initalTrialPrice = $this->trialPrice;
			$this->taxes = ihc_get_taxes_for_amount_by_country($this->country, $this->state, $this->trialPrice);
			$this->trialPrice += empty($this->taxes['total']) ? 0 : $this->taxes['total'];
			$this->trialActive = true;
		}
		/// set the duration
		if ($levelData['access_trial_type']==1){
			/// certain period
			$this->durationValue = $levelData['access_trial_time_value'];
			$this->durationType = 'certain_period';
			$this->durationTimeType = $levelData['access_trial_time_type'];
		} else {
			/// couple cycles
			$this->durationValue = $levelData['access_trial_couple_cycles'];
			$this->durationType = 'couple_of_cycles';
		}
		return $this;
	}

	public function isTrialActive()
	{
		return $this->trialActive;
	}

	public function getTrialPrice($raw=true)
	{
		if ($raw)
				return $this->trialPrice;
		else
				return ihc_format_price_and_currency($this->currency, $this->trialPrice);
	}

	public function getInitialTrialPrice($raw=true)
	{
		if ($raw)
				return $this->initalTrialPrice;
		else
				return ihc_format_price_and_currency($this->currency, $this->initalTrialPrice);
	}
  public function setTaxesAfterDiscount(){
    $this->taxes = ihc_get_taxes_for_amount_by_country($this->country, $this->state, $this->trialPrice);
    $this->trialPrice += empty($this->taxes['total']) ? 0 : $this->taxes['total'];
    if(empty($this->taxes['total'])){
      $this->taxes = 0;
    }
  }
	public function getTaxes()
	{
		return $this->taxes;
	}

	public function getDurationType($raw=true)
	{
		if ($raw){
				return $this->durationType;
		}	else {
			if ($this->durationType=='couple_of_cycles'){
					return esc_html__('Cycle/s', 'ihc');
			}
			switch ($this->durationTimeType){
				case 'D':
					return esc_html__('Days', 'ihc');
					break;
				case 'W':
					return esc_html__('Weeks', 'ihc');
					break;
				case 'M':
					return esc_html__('Months', 'ihc');
					break;
				case 'Y':
					return esc_html__('Years', 'ihc');
					break;
			}
		}
	}

	public function getDurationValue()
	{
			return $this->durationValue;
	}
}
