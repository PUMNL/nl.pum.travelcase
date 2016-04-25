<?php

/**
 * Class to get parent case id for travel case
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 4 Apr 2016
 * @license AGPL-3.0
 */
class CRM_Travelcase_Utils_GetParentCaseId {

  /**
   * Method to find parent case for Travel Case. Will return FALSE if not found.
   *
   * @param $caseId
   * @return bool|string
   */
  public static function getParentCaseId($caseId) {
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

}