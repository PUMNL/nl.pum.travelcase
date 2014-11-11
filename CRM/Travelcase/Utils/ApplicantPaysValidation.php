<?php

class CRM_Travelcase_Utils_ApplicantPaysValidation {
  
  public static function validateForm( $formName, &$fields, &$files, &$form, &$errors ) {
    if ($formName == 'CRM_Case_Form_Activity') {
      self::validateCaseActivityForm($formName, $fields, $files, $form, $errors);
    } elseif ($formName == 'CRM_Case_Form_CustomData') {
      self::validateCustomDataForm($formName, $fields, $files, $form, $errors);
    } elseif ($formName == 'CRM_Case_Form_CaseView') {
      self::validateCaseView($formName, $fields, $files, $form, $errors);
    }
  }
  
  protected static function validateCaseView( $formName, &$fields, &$files, &$form, &$errors ) {
    $caseId = $form->getVar('_caseID');
    
    if (!self::hasParentCaseApplicantPays($caseId)) {
      return;
    }
    
    if (isset($fields['timeline_id']) == 'Visa') {
      $errors['timeline_id'] = ts('Applicant Pays restriction in place on parent case');
    }
  }
  
  protected static function validateCaseActivityForm( $formName, &$fields, &$files, &$form, &$errors ) {
    $config = CRM_Travelcase_ApplicantPaysConfig::singleton();
    $caseId = $form->getVar('_caseId');
    $activityTypeId = $form->getVar('_activityTypeId');
    
    if (!in_array($activityTypeId, $config->getRestrictiedActivites())) {
      return;
    }
    
    if (!self::hasParentCaseApplicantPays($caseId)) {
      return;
    }
    
    foreach($fields as $fieldName => $value) {
      $errors[$fieldName] = ts('Applicant Pays restriction in place on parent case');
    }
    
  }
  
  protected static function validateCustomDataForm( $formName, &$fields, &$files, &$form, &$errors ) {
    $config = CRM_Travelcase_ApplicantPaysConfig::singleton();

    $groupId = $form->getVar('_groupID');
    if (!in_array($groupId, $config->getCustomGroupIds())) {
      return;
    }
    
    $caseId = $form->getVar('_entityID');
    if (self::hasParentCaseApplicantPays($caseId)) {
      foreach($fields as $fieldName => $value) {
        $errors[$fieldName] = ts('Applicant Pays restriction in place on parent case');
      }
    }
  }
  
  protected static function hasParentCaseApplicantPays($child_case_id) {
    $config = CRM_Travelcase_Config::singleton();    
    
    $case_id_field = $config->getCustomFieldCaseId('id');
    $custom_values = CRM_Core_BAO_CustomValueTable::getEntityValues($child_case_id, null, array($case_id_field));
    
    if (empty($custom_values[$case_id_field])) {
      return false;
    }
    
    return self::hasCaseApplicantPays($custom_values[$case_id_field]);
  }
  
  protected static function hasCaseApplicantPays($case_id) {
    $config = CRM_Travelcase_ApplicantPaysConfig::singleton();
    $sql = "SELECT * FROM `civicrm_activity` `a` 
        INNER JOIN `civicrm_case_activity` `ca` ON `a`.`id` = `ca`.`activity_id` 
        WHERE `is_deleted` != '1' 
        AND `is_current_revision` = '1' 
        AND `activity_type_id` = %1 
        AND `status_id` = %2 
        AND `case_id` = %3";
    
    $dao = CRM_Core_DAO::executeQuery($sql, array(
      1 => array($config->getActivityTypeId(), 'Integer'),
      2 => array(1, 'Integer'), //scheduled
      3 => array($case_id, 'Integer'),
    ));
    if ($dao->fetch()) {
      return true;
    }
    return false;
  }
  
}

