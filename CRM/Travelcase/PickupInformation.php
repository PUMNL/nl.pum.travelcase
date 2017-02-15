<?php

/**
 * Class for Pick Up Information processing
 *
 * @author Erik Hommel (CiviCooP)
 * @date 15 Feb 2017
 * @license AGPL-3.0
 */
class CRM_Travelcase_PickupInformation {
  /**
   * Method to process civicrm hook buildForm
   *
   * @param $form
   */
  public static function buildForm(&$form) {
    $formAction = $form->getVar('_action');
    $formCaseType = $form->getVar('_caseType');
    // case type travel case and action is add
    if ($formCaseType == 'TravelCase' && $formAction == CRM_Core_Action::ADD) {
      $formActivityType = $form->getVar('_activityTypeName');
      // activity type is Request Business Programme
      if ($formActivityType == 'Pick Up Information') {
        // set defaults for pick up information
        self::setDefaults($form);
      }
    }
  }

  /**
   * Method to set defaults for pick up information
   *
   * @param $form
   */
  private static function setDefaults(&$form) {
    $defaults = array();
    $caseId = $form->getVar('_caseId');
    // set default assignee to authorised contact if found on parent or parent customer
    $authorizedContactId = self::getAuthorisedContactId($caseId);
    if ($authorizedContactId) {
      $defaults['assignee_contact_id'] = $authorizedContactId;
      $form->setDefaults($defaults);
    }
  }

  /**
   * Method to get authorised contact -first try to get authorizedContactId from _relatedContact in Form,
   * if not found get from api on case, if not found get on customer
   *
   * @param int $caseId
   * @return int
   */
  private static function getAuthorisedContactId($caseId) {
    $authorisedContactId = NULL;
    // get parent case
    $parentCaseId = CRM_Travelcase_Utils_GetParentCaseId::getParentCaseId($caseId);
    if ($parentCaseId) {
      // retrieve from case relationship
      try {
        $authorisedRelationshipTypeId = civicrm_api3('RelationshipType', 'getvalue', array(
          'name_a_b' => 'Has authorised',
          'name_b_a' => 'Has authorised',
          'return' => 'id'
        ));
        $authorisedContactId = civicrm_api3('Relationship', 'getvalue', array(
          'relationship_type_id' => $authorisedRelationshipTypeId,
          'case_id' => $parentCaseId,
          'return' => 'contact_id_b'));
      } catch (CiviCRM_API3_Exception $ex) {
        // get from customer
        if (method_exists('CRM_Threepeas_Utils', 'getCaseClientId')) {
          $parentClientId = CRM_Threepeas_Utils::getCaseClientId($parentCaseId);
          if ($parentClientId) {
            if (method_exists('CRM_Threepeas_BAO_PumCaseRelation', 'getAuthorisedContactId')) {
              $authorisedContactId = CRM_Threepeas_BAO_PumCaseRelation::getAuthorisedContactId($parentClientId);
            }
          }
        }
      }
    }
    return $authorisedContactId;
  }
}