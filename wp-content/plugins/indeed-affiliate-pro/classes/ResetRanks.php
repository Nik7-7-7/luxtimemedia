<?php
namespace Indeed\Uap;

class ResetRanks
{
    private $cronName = 'uapDoRanksReset';

    public function __construct(){}

    public function doSchedule()
    {
        $schedule = wp_next_scheduled($this->cronName);
        $targetDay = get_option('uap_ranks_pro_reset_day');

        $currentDay = date('d', time());
        $currentMonth = date("m", time());
        $currentYear = date('Y', time());
        if ($currentDay>$targetDay){
            $currentMonth++;
        }
        if ($currentMonth>12){
            $currentMonth = 1;
            $currentYear++;
        }
        $maximumNumberOfDaysPerMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);

        if ($maximumNumberOfDaysPerMonth<$targetDay){
            $targetDay = $maximumNumberOfDaysPerMonth;
        }

        $timestamp = strtotime($currentYear . '-' . $currentMonth . '-' . $targetDay);
        wp_schedule_event($timestamp, 'daily', $this->cronName, array());
        return $this;

    }

    public function doAction()
    {
        global $indeed_db;

        $ranks = $indeed_db->get_rank_list();
        if (empty($ranks)){
        		return $this;
        }
        reset($ranks);
        $key = key($ranks);
        $rankId = $key; /// the most lower rank

        $affiliates = $indeed_db->getAllAffiliatesIds();
        if (!$affiliates){
            return $this;
        }

        foreach ($affiliates as $affiliateObject){
            $indeed_db->update_affiliate_rank_by_uid($affiliateObject->uid, $rankId);
        }
        return $this;
    }


}
