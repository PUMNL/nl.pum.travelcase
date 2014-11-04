<?php

class CRM_Travelcase_Utils_SetDefaultValues {
  
  public static function buildForm($formName, &$form) {
    if ($formName != 'CRM_Case_Form_Case') {
      return;
    }
    
    self::setDefaultCaseStatus($form);
    self::setDefaultParentCase($form);
  }
  
  protected static function setDefaultCaseStatus(&$form) {
    $config = CRM_Travelcase_TravelCaseStatusConfig::singleton();
    $defaultStatus = -1; //to be arranged
    $defaults['custom_'.$config->getCustomFieldPickup('id').'_-1'] = $defaultStatus;
    $defaults['custom_'.$config->getCustomFieldTicket('id').'_-1'] = $defaultStatus;
    $defaults['custom_'.$config->getCustomFieldVisa('id').'_-1'] = $defaultStatus;
    $defaults['custom_'.$config->getCustomFieldAccomodation('id').'_-1'] = $defaultStatus;
    $form->setDefaults($defaults);
  }
  
  protected static function setDefaultParentCase(&$form) {
    $parent_case_id = CRM_Utils_Request::retrieve('parent_case_id', 'Positive', $form);
    if (!empty($parent_case_id)) {
      $config = CRM_Travelcase_Config::singleton();
      $defaults['case_type_id'] = $config->getCaseType('value');
      try {
        $case = civicrm_api3('Case', 'getsingle', array('id' => $parent_case_id));
        $defaults['custom_'.$config->getCustomFieldCaseId('id').'_-1_id'] = $parent_case_id;
        $defaults['custom_'.$config->getCustomFieldCaseId('id').'_-1'] = $case['subject'];
      } catch (Exception $ex) {
          //do nothing
      }
      
      $form->setDefaults($defaults);
    }
  }
  
}
