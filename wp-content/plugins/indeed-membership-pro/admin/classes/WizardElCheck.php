<?php
/*
Since version 12.1.
Initiate : $iumpWizardElCheck = new \Indeed\Ihc\Admin\WizardElCheck();
Dependency : \Indeed\Ihc\Services\ElCheck
*/
namespace Indeed\Ihc\Admin;

class WizardElCheck extends \Indeed\Ihc\Services\ElCheck
{
      /**
       * @var string
       */
      protected $ajax                   = 'ihc_wizard_el_check_get_url_ajax';// custom ajax call for wizard
      /**
       * @var string
       */
      protected $redirectBackPath        = 'admin.php?page=ihc_manage&tab=wizard&step=1';// back to wizard
}
