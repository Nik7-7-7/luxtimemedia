<?php
/*
Since version 8.5.
Initiate : $uapWizardElCheck = new \Indeed\Uap\Admin\WizardElCheck();
Dependency : \Indeed\Uap\ElCheck
*/
namespace Indeed\Uap\Admin;

class WizardElCheck extends \Indeed\Uap\ElCheck
{
      /**
       * @var string
       */
      protected $ajax                   = 'uap_wizard_el_check_get_url_ajax';// custom ajax call for wizard
      /**
       * @var string
       */
      protected $redirectBackPath        = 'admin.php?page=ultimate_affiliates_pro&tab=wizard&step=1';// back to wizard
}
