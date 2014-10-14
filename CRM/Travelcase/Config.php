<?php

class CRM_Travelcase_Config {
  
  protected static $_singleton;
  
  protected $link_case_to;
  
  protected $case_id;
  
  protected $event_id;
  
  protected $travel_agency_info;
  
  protected $departure_date;
  
  protected $return_date;
  
  protected $expert_relationship_type;
  
  protected function __construct() {
    $this->link_case_to = civicrm_api3('CustomGroup', 'getsingle', array('name' => 'travel_parent'));
    $this->case_id = civicrm_api3('CustomField', 'getsingle', array('name' => 'case_id', 'custom_group_id' => $this->link_case_to['id']));
    $this->event_id = civicrm_api3('CustomField', 'getsingle', array('name' => 'event_id', 'custom_group_id' => $this->link_case_to['id']));

    $this->travel_agency_info = civicrm_api3('CustomGroup', 'getsingle', array('name' => 'info_for_travel_agency'));
    $this->departure_date = civicrm_api3('CustomField', 'getsingle', array('name' => 'requested_departure_date', 'custom_group_id' => $this->travel_agency_info['id']));
    $this->return_date = civicrm_api3('CustomField', 'getsingle', array('name' => 'requested_return_date', 'custom_group_id' => $this->travel_agency_info['id']));
    $this->destination = civicrm_api3('CustomField', 'getsingle', array('name' => 'destination', 'custom_group_id' => $this->travel_agency_info['id']));
    
    $this->expert_relationship_type = civicrm_api3('RelationshipType', 'getsingle', array('name_a_b' => 'Expert'));
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
  
  public function getCustomFieldDepartureDate($key='id') {
    return $this->departure_date[$key];
  }
  
  public function getCustomFieldReturnDate($key='id') {
    return $this->return_date[$key];
  }
  
  public function getCustomFieldDestination($key='id') {
    return $this->destination[$key];
  }
  
  public function getRelationshipTypeExpert($key='id') {
    return $this->expert_relationship_type[$key];
  }
}