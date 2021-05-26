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
    $defaults['custom_'.$config->getCustomFieldPreTravelCheck('id').'_-1'] = $defaultStatus;
    $defaults['custom_'.$config->getCustomFieldPickup('id').'_-1'] = $defaultStatus;
    $defaults['custom_'.$config->getCustomFieldTicket('id').'_-1'] = $defaultStatus;
    $defaults['custom_'.$config->getCustomFieldVisa('id').'_-1'] = $defaultStatus;
    $defaults['custom_'.$config->getCustomFieldAccomodation('id').'_-1'] = $defaultStatus;
    $defaults['custom_'.$config->getCustomFieldDsa('id').'_-1'] = $defaultStatus;
    $defaults['custom_'.$config->getCustomFieldInvitation('id').'_-1'] = $defaultStatus;
    $form->setDefaults($defaults);
  }

  protected static function setDefaultParentCase(&$form) {
    $parent_case_id = CRM_Utils_Request::retrieve('parent_case_id', 'Positive', $form);
    if (!empty($parent_case_id)) {
      $config = CRM_Travelcase_Config::singleton();
      $dsa = CRM_Travelcase_InfoForDsaConfig::singleton();
      $defaults['case_type_id'] = $config->getCaseType('value');
      try {
        $case = civicrm_api3('Case', 'getsingle', array('id' => $parent_case_id));
        $defaults['custom_'.$config->getCustomFieldCaseId('id').'_-1_id'] = $parent_case_id;
        $defaults['custom_'.$config->getCustomFieldCaseId('id').'_-1'] = $case['subject'];
        $defaults['custom_'.$autofill_field = $dsa->getCustomFieldFillFromLinkedEntity('id').'_-1'] = '1';
      } catch (Exception $ex) {
          //do nothing
      }

      $form->setDefaults($defaults);
    }
  }

  /**
   * Method to create default travel case status
   *
   * @param $caseId
   * @param $caseTypeId
   */
  public static function businessTravelStatus($caseId, $caseTypeId) {
    $config = CRM_Travelcase_Config::singleton();
    $travelCaseTypeId = $config->getCaseType('value');
    if ($caseTypeId == $travelCaseTypeId) {
      $travelStatusConfig = CRM_Travelcase_TravelCaseStatusConfig::singleton();
      if (self::travelCaseStatusExists($caseId) == FALSE) {
        $defaultStatus = "-1";
        $columns = array(
          $travelStatusConfig->getCustomFieldAccomodation('column_name'),
          $travelStatusConfig->getCustomFieldDsa('column_name'),
          $travelStatusConfig->getCustomFieldInvitation('column_name'),
          $travelStatusConfig->getCustomFieldPickup('column_name'),
          $travelStatusConfig->getCustomFieldTicket('column_name'),
          $travelStatusConfig->getCustomFieldVisa('column_name')
        );
        $clauses = array();
        foreach ($columns as $column) {
          $clauses[] = $column.' = %2';
        }
        $insert = "INSERT INTO ".$travelStatusConfig->getCustomGroupTravelCaseStatus('table_name')
          ." SET entity_id = %1, ".implode(', ', $clauses);
        $params = array(
          1 => array($caseId, 'Integer'),
          2 => array($defaultStatus, 'String')
        );
        CRM_Core_DAO::executeQuery($insert, $params);
      }
    }
  }

  /**
   * Method to check if there is already travel status for travel case
   *
   * @param $caseId
   * @return bool
   */
  private static function travelCaseStatusExists($caseId) {
    $config = CRM_Travelcase_TravelCaseStatusConfig::singleton();
    $query = 'SELECT COUNT(*) FROM '.$config->getCustomGroupTravelCaseStatus('table_name').' WHERE entity_id = %1';
    $count = CRM_Core_DAO::singleValueQuery($query, array(1 => array($caseId, 'Integer')));
    if ($count > 0) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

}
