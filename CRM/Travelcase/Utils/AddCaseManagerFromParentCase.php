<?php
/**
 * Class to handle CaseManager from Parent Case for Travel Case (issue 2567)
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 7 Dec 2015
 * @license AGPL-3.0
 */

class CRM_Travelcase_Utils_AddCaseManagerFromParentCase {

  /**
   * Method to process caseSummary hook
   * issue 2567 check if case is TravelCase and only has open case as activity (so is new)
   * If so, retrieve project officer of parent case and make that the Case Manager
   *
   * @param $caseId
   */
  public static function caseSummary($caseId) {
    $parentCaseId = self::getParentCaseId($caseId);
    if ($parentCaseId) {
      if (self::onlyOpenCase($caseId) == TRUE) {
        self::updateCaseManager($caseId, $parentCaseId);
      }
    }
  }

  /**
   * Method to update the case coordinator of the travel case to the project officer of the parent case
   *
   * @param $caseId
   * @param $parentCaseId
   */
  private static function updateCaseManager($caseId, $parentCaseId) {
    if (method_exists('CRM_Threepeas_BAO_PumCaseRelation', 'getRelationContactIdByCaseId')) {
      $projectOfficerId = CRM_Threepeas_BAO_PumCaseRelation::getRelationContactIdByCaseId($parentCaseId, 'project_officer');
      $caseManagerRelationshipTypeId = civicrm_api3('RelationshipType', 'Getvalue', array('name_a_b' => 'Case Coordinator is', 'return' => 'id'));
      $caseClientId = CRM_Threepeas_Utils::getCaseClientId($caseId);
      $query = "UPDATE civicrm_relationship SET contact_id_b = %1 WHERE contact_id_a = %2 AND case_id = %3 AND relationship_type_id = %4";
      $params = array(
        1 => array($projectOfficerId, 'Integer'),
        2 => array($caseClientId, 'Integer'),
        3 => array($caseId, 'Integer'),
        4 => array($caseManagerRelationshipTypeId, 'Integer'));
      CRM_Core_DAO::executeQuery($query, $params);
    }
  }

  /**
   * Method to find parent case for Travel Case. Will return FALSE if not found.
   *
   * @param $caseId
   * @return bool|string
   */
  private static function getParentCaseId($caseId) {
    $parentCaseId = FALSE;
    $travelConfig = CRM_Travelcase_Config::singleton();
    $parentTableName = $travelConfig->getCustomGroupLinkCaseTo('table_name');
    $parentCaseIdColumn = $travelConfig->getCustomFieldCaseId('column_name');
    if (!empty($caseId)) {
      $query = "SELECT ".$parentCaseIdColumn." FROM ".$parentTableName." WHERE entity_id = %1";
      $parentCaseId = CRM_Core_DAO::singleValueQuery($query, array(1 => array($caseId, 'Integer')));
    }
    return $parentCaseId;
  }

  /**
   * Method to determine if case only has open case activity
   *
   * @param $caseId
   * @return bool
   */
  private static function onlyOpenCase($caseId) {
    if (empty($caseId) || !class_exists('CRM_Threepeas_Config')) {
      return FALSE;
    }
    $config = CRM_Threepeas_Config::singleton();
    $query = "SELECT count(*) as countOthers FROM civicrm_case_activity ca
      JOIN civicrm_activity act ON ca.activity_id = act.id
      WHERE ca.case_id = %1 and activity_type_id != %2";
    $params = array(
      1 => array($caseId, 'Integer'),
      2 => array($config->openCaseActTypeId, 'Integer'));
    $countOthers = CRM_Core_DAO::singleValueQuery($query, $params);
    if ($countOthers > 0) {
      return FALSE;
    } else {
      return TRUE;
    }
  }
}
