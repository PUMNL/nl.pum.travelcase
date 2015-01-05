<?php

class CRM_Travelcase_Utils_PermissionValidation {
  
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
    $caseId = _travelcase_retrieve_case_id_from_url($form->_submitValues['entryURL']);
    
    if (!self::hasParentCaseApplicantPays($caseId) && self::hasPermission($caseId)) {
      return;
    }
    
    if (isset($fields['timeline_id']) == 'Visa') {
      if (!self::hasPermission($caseId)) {
        $errors['timeline_id'] = ts('You do not have the permission for this timeline');
      } else {
        $errors['timeline_id'] = ts('Applicant Pays restriction in place on parent case');
      }
    }
  }
  
  protected static function validateCaseActivityForm( $formName, &$fields, &$files, &$form, &$errors ) {
    $config = CRM_Travelcase_ApplicantPaysConfig::singleton();
    $caseId = _travelcase_retrieve_case_id_from_url($form->_submitValues['entryURL']);
    $activityTypeId = $form->getVar('_activityTypeId');
    
    if (!in_array($activityTypeId, $config->getRestrictiedActivites())) {
      return;
    }
    
    if (!self::hasParentCaseApplicantPays($caseId) && self::hasPermission($caseId)) {
      return;
    }
    
    foreach($fields as $fieldName => $value) {
      if (!self::hasPermission($caseId)) {
        $errors[$fieldName] = ts('You do not have the permission to edit this activity');
      } else {
        $errors[$fieldName] = ts('Applicant Pays restriction in place on parent case');
      }
    }
    
  }
  
  protected static function validateCustomDataForm( $formName, &$fields, &$files, &$form, &$errors ) {
    $config = CRM_Travelcase_ApplicantPaysConfig::singleton();

    $groupId = $form->getVar('_groupID');
    if (!in_array($groupId, $config->getCustomGroupIds())) {
      return;
    }
    
    $caseId = $form->getVar('_entityID');
    if (!self::hasPermission($caseId)) {
      foreach($fields as $fieldName => $value) {
        $errors[$fieldName] = ts('You do not have the permission to edit this information on the travelcase');
      }
    } elseif (self::hasParentCaseApplicantPays($caseId)) {
      foreach($fields as $fieldName => $value) {
        $errors[$fieldName] = ts('Applicant Pays restriction in place on parent case');
      }
    }
  }
  
  protected static function hasParentCaseApplicantPays($child_case_id) {
    $parent_case_id = self::getParentCaseId($child_case_id);
    if (!$parent_case_id) {
      return false;
    }
    
    return self::hasCaseApplicantPays($parent_case_id);
  }
  
  protected static function getParentCaseId($child_case_id) {
    $config = CRM_Travelcase_Config::singleton();    
    
    $case_id_field = $config->getCustomFieldCaseId('id');
    $custom_values = CRM_Core_BAO_CustomValueTable::getEntityValues($child_case_id, null, array($case_id_field));
    
    if (empty($custom_values[$case_id_field])) {
      return false;
    }
    
    return $custom_values[$case_id_field];
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
  
  protected static function hasPermission($case_id) {
    if (CRM_Core_Permission::check('manage all travel cases')) {
      return true;
    }
    
    $config = CRM_Travelcase_Config::singleton();
    $case_type_id = civicrm_api3('Case', 'getvalue', array(
      'id' => $case_id,
      'return' => 'case_type_id',
    ));
    
    if ($case_type_id != $config->getCaseType('value')) {
      return true; //this is not a travel case so user has permission
    }
    
    //check wether the user has CC relationship on the parent case
    $parent_case_id = self::getParentCaseId($case_id);
    if (!$parent_case_id) {
      return false;
    }
    
    $session = CRM_Core_Session::singleton();
    
        
    try {
      $parent_case_type_id = civicrm_api3('Case', 'getvalue', array(
        'id' => $parent_case_id,
        'return' => 'case_type_id',
      ));
      $relationships_to_check = $config->getTravelCaseManagerByParentCaseRole($parent_case_type_id);
      foreach($relationships_to_check as $relationship_type_id) {
        try {
          $contact_id = civicrm_api3('Relationship', 'getvalue', array(
            'relationship_type_id' => $relationship_type_id,
            'case_id' => $parent_case_id,
            'return' => 'contact_id_b',
          ));
          
          if ($contact_id == $session->get('userID')) {
            return true;
          }
        } catch (Exception $ex) {
          //do nothing
        }
      }
    } catch (Exception $ex) {
      //do nothing
    }
    
    return false;
  }
  
}

