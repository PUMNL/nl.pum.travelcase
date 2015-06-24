<?php
/**
 * Class for build of travel case from - remove activities if there is customer contribution condition
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_Travelcase_Utils_CustomerContributionValidation {
  /**
   * Method to set the form for travelcase
   *
   * @param $form
   * @access public
   * @static
   */
  public static function buildForm(&$form) {
    $travelcaseConfig = CRM_Travelcase_Config::singleton();
    $travelCaseTypeName = $travelcaseConfig->getCaseType('name');
    if (isset($form->_caseType) && $form->_caseType == $travelCaseTypeName) {
      $typeIndex = $form->_elementIndex['activity_type_id'];
      self::removeInvalidActivityTypeIdsFromList($form->_elements[$typeIndex]->_options, $form->_caseID);
    }
  }

  /**
   * Method to remove unwanted list options for activity types
   *
   * @param array $typeOptions
   * @param int $caseId
   * @access protected
   * @static
   */
  protected static function removeInvalidActivityTypeIdsFromList(&$typeOptions, $caseId) {
    $typeIdsToBeRemoved = self::getActivityTypeIdsToBeRemoved($caseId);
    foreach ($typeOptions as $typeOptionId => $typeOption) {
      if (in_array($typeOption['attr']['value'], $typeIdsToBeRemoved)) {
        unset($typeOptions[$typeOptionId]);
      }
    }
  }

  /**
   * Method to get the activity type ids to be removed from form list
   * (only if customer contribution on case with status other than
   * completed or cancelled)
   *
   * @param int $caseId
   * @return array $typeIdsToBeRemoved
   * @access protected
   * @static
   */
  protected static function getActivityTypeIdsToBeRemoved($caseId) {
    $typeIdsToBeRemoved = array();
    if (self::caseDsaLoIAllowed($caseId) == FALSE) {
      $loiActivityType = CRM_Threepeas_Utils::getActivityTypeWithName('Letter of Invitation');
      $dsaActivityType = CRM_Threepeas_Utils::getActivityTypeWithName('DSA');
      $typeIdsToBeRemoved[] = $loiActivityType['value'];
      $typeIdsToBeRemoved[] = $dsaActivityType['value'];
    }
    return $typeIdsToBeRemoved;
  }

  /**
   * Method to check if it is allowed to add DSA/Letter of Invitation actions
   *
   * @param int $caseId
   * @return bool
   * @access protected
   * @static
   */
  protected static function caseDsaLoIAllowed($caseId) {
    $isAllowed = TRUE;
    $customerContribution = CRM_Threepeas_Utils::getActivityTypeWithName('Condition: Customer Contribution.');
    $parentCaseId = self::getParentCaseId($caseId);
    if (empty($parentCaseId)) {
      return $isAllowed;
    }
    $completedStatus = CRM_Threepeas_Utils::getActivityStatusWithName('Completed');
    $cancelledStatus = CRM_Threepeas_Utils::getActivityStatusWithName('Cancelled');
    $allowedStatus = array($completedStatus['value'], $cancelledStatus['value']);
    $caseActivities = civicrm_api3('CaseActivity', 'Get', array('case_id' => $parentCaseId));
    foreach ($caseActivities['values'] as $activityId => $caseActivity) {
      if ($caseActivity['activity_type_id'] == $customerContribution['value']) {
        if (!in_array($caseActivity['status_id'], $allowedStatus)) {
          $isAllowed = FALSE;
        }
      }
    }
    return $isAllowed;
  }

  /**
   * Method to get the parent case for a travel case
   *
   * @param int $travelCaseId
   * @return int
   * @access protected
   * @static
   */
  protected static function getParentCaseId($travelCaseId) {
    $travelCaseConfig = CRM_Travelcase_Config::singleton();
    $customGroupParams = array(
      'id' => $travelCaseConfig->getCustomFieldCaseId('custom_group_id'),
      'return' => 'table_name');
    $customGroupTable = civicrm_api3('CustomGroup', 'Getvalue', $customGroupParams);
    $parentCaseIdColumn = $travelCaseConfig->getCustomFieldCaseId('column_name');
    $query = 'SELECT '.$parentCaseIdColumn.' AS parentCaseId FROM '.$customGroupTable.' WHERE entity_id = %1';
    $queryParams = array(1 => array($travelCaseId, 'Integer'));
    $dao = CRM_Core_DAO::executeQuery($query, $queryParams);
    if ($dao->fetch()) {
      return $dao->parentCaseId;
    } else {
      return 0;
    }
  }
}