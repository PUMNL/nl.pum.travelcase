<?php

class CRM_Travelcase_Config {
  
  protected static $_singleton;
  
  protected $link_case_to;
  
  protected $case_id;
  
  protected $event_id;
  
  protected $travel_agency_info;

  protected $travelData;
  
  protected $departure_date;

  protected $travelDataDepartureDate;
  protected $travelDataReturnDate;
  protected $travelDataDestination;
  
  protected $return_date;
  
  protected $expert_relationship_type;
  
  protected $cc_relationship_type;
  
  protected $sc_relationship_type;
  
  protected $rep_relationship_type;
  
  protected $travel_case_type;

  protected $travel_open_case_status;
  
  protected $proj_off_relationship_type;
  
  protected $mt_member_relationship_type;
  
  protected function __construct() {
    $this->link_case_to = civicrm_api3('CustomGroup', 'getsingle', array('name' => 'travel_parent'));
    $this->case_id = civicrm_api3('CustomField', 'getsingle', array('name' => 'case_id', 'custom_group_id' => $this->link_case_to['id']));
    $this->event_id = civicrm_api3('CustomField', 'getsingle', array('name' => 'event_id', 'custom_group_id' => $this->link_case_to['id']));

    $this->travelData = civicrm_api3('CustomGroup', 'getsingle', array('name' => 'travel_data'));
    $this->travelDataDepartureDate = civicrm_api3('CustomField', 'getsingle',
      array('name' => 'departure_time', 'custom_group_id' => $this->travelData['id']));
    $this->travelDataReturnDate = civicrm_api3('CustomField', 'getsingle',
      array('name' => 'return_departure_time', 'custom_group_id' => $this->travelData['id']));
    $this->travelDataDestination = civicrm_api3('CustomField', 'getsingle',
      array('name' => 'destination', 'custom_group_id' => $this->travelData['id']));

    $this->travel_agency_info = civicrm_api3('CustomGroup', 'getsingle', array('name' => 'info_for_travel_agency'));
    $this->departure_date = civicrm_api3('CustomField', 'getsingle', array('name' => 'requested_departure_date', 'custom_group_id' => $this->travel_agency_info['id']));
    $this->return_date = civicrm_api3('CustomField', 'getsingle', array('name' => 'requested_return_date', 'custom_group_id' => $this->travel_agency_info['id']));
    $this->destination = civicrm_api3('CustomField', 'getsingle', array('name' => 'destination', 'custom_group_id' => $this->travel_agency_info['id']));
    
    $this->expert_relationship_type = civicrm_api3('RelationshipType', 'getsingle', array('name_a_b' => 'Expert'));
    $this->cc_relationship_type = civicrm_api3('RelationshipType', 'getsingle', array('name_a_b' => 'Country Coordinator is'));
    $this->sc_relationship_type = civicrm_api3('RelationshipType', 'getsingle', array('name_a_b' => 'Sector Coordinator'));
    $this->rep_relationship_type = civicrm_api3('RelationshipType', 'getsingle', array('name_a_b' => 'Representative is'));
    $this->proj_off_relationship_type = civicrm_api3('RelationshipType', 'getsingle', array('name_a_b' => 'Project Officer for'));
    $this->mt_member_relationship_type = civicrm_api3('RelationshipType', 'getsingle', array('name_a_b' => 'MT Member is'));
    
    $case_type_id = civicrm_api3('OptionGroup', 'getvalue', array('name' => 'case_type', 'return' => 'id'));
    $this->travel_case_type = civicrm_api3('OptionValue', 'getsingle', array('name' => 'Travelcase', 'option_group_id' => $case_type_id));

    $case_status_id = civicrm_api3('OptionGroup', 'getvalue', array('name' => 'case_status', 'return' => 'id'));
    $this->travel_open_case_status = civicrm_api3('OptionValue', 'getsingle', array('name' => 'Open', 'option_group_id' => $case_status_id));
  }
  
  /**
   * @return CRM_Travelcase_Config 
   */
  public static function singleton() {
    if (!self::$_singleton) {
      self::$_singleton = new CRM_Travelcase_Config;
    }
    return self::$_singleton;
  }
  
  public function getCaseType($key='id') {
    return $this->travel_case_type[$key];
  }

  public function getOpenCaseStatus($key='id') {
    return $this->travel_open_case_status[$key];
  }
  
  public function getCustomFieldCaseId($key='id') {
    return $this->case_id[$key];
  }
  
  public function getCustomFieldEventId($key='id') {
    return $this->event_id[$key];
  }
  
  public function getCustomGroupLinkCaseTo($key='id') {
    return $this->link_case_to[$key];
  }
  
  public function getCustomGroupTravelAgencyInfo($key='id') {
    return $this->travel_agency_info[$key];
  }
  
  public function getCustomGroupTravelData($key='id') {
    return $this->travelData[$key];
  }

  public function getCustomFieldDepartureDate($key='id') {
    return $this->departure_date[$key];
  }

  public function getCustomFieldTravelDataDepartureDate($key='id') {
    return $this->travelDataDepartureDate[$key];
  }

  public function getCustomFieldReturnDate($key='id') {
    return $this->return_date[$key];
  }

  public function getCustomFieldTravelDataReturnDate($key='id') {
    return $this->travelDataReturnDate[$key];
  }
  
  public function getCustomFieldDestination($key='id') {
    return $this->destination[$key];
  }

  public function getCustomFieldTravelDataDestination($key='id') {
    return $this->travelDataDestination[$key];
  }

  public function getRelationshipTypeExpert($key='id') {
    return $this->expert_relationship_type[$key];
  }
  
  public function getRelationshipTypeCC($key='id') {
    return $this->cc_relationship_type[$key];
  }
  
  public function getRelationshipTypeSC($key='id') {
    return $this->sc_relationship_type[$key];
  }
  
  public function getRelationshipTypeRep($key='id') {
    return $this->rep_relationship_type[$key];
  }
  
  public function getRelationshipTypeProjOff($key='id') {
    return $this->proj_off_relationship_type[$key];
  }
  
  public function getRelationshipTypeMtMember($key='id') {
    return $this->mt_member_relationship_type[$key];
  }
  
  public function getTravelCaseRelationshipsForCaseType($case_type_id) {
    $case_type_option_group_id = civicrm_api3('OptionGroup', 'getvalue', array('name' => 'case_type', 'return' => 'id'));
    $case_type = civicrm_api3('OptionValue', 'getvalue', array('value' => $case_type_id, 'option_group_id' => $case_type_option_group_id, 'return' => 'name'));
    switch ($case_type) {      
      case 'Advice':
        return array(
          $this->getRelationshipTypeExpert('id'),
        );
        break;
      case 'Seminar':
        return array(
          $this->getRelationshipTypeExpert('id'),
        );
        break;
      case 'PDV':
        return array(
          $this->getRelationshipTypeCC('id'),
          $this->getRelationshipTypeProjOff('id'),
          $this->getRelationshipTypeMtMember('id'),
        );
        break;
      case 'FactFinding':
        return array(
          $this->getRelationshipTypeExpert('id'),
          'additional_person',
        );
        break;
      case 'CTM':
        return array(
          $this->getRelationshipTypeCC('id'),
          $this->getRelationshipTypeRep('id'),
          $this->getRelationshipTypeProjOff('id'),
          $this->getRelationshipTypeMtMember('id'),
          'additional_person',
        );
        break;
    }
    return array();
  }
  
  public function getTravelCaseManagerByParentCaseRole($case_type_id) {
    $case_type_option_group_id = civicrm_api3('OptionGroup', 'getvalue', array('name' => 'case_type', 'return' => 'id'));
    $case_type = civicrm_api3('OptionValue', 'getvalue', array('value' => $case_type_id, 'option_group_id' => $case_type_option_group_id, 'return' => 'name'));
    switch ($case_type) {      
      case 'Advice':
        return array();
        break;
      case 'Seminar':
        return array();
        break;
      case 'PDV':
        return array(
          $this->getRelationshipTypeCC('id'),
        );
        break;
      case 'FactFinding';
        return array();
        break;
      case 'CTM':
        return array();
        break;
    }
    return array();
  }
}
