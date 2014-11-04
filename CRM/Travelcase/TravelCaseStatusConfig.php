<?php

class CRM_Travelcase_TravelCaseStatusConfig {
  
  protected static $_singleton;
  
  protected $case_status;
  
  protected $visa;
  
  protected $ticket;
  
  protected $pickup;
  
  protected $accomodation;
  
  
  protected function __construct() {
    $this->case_status = civicrm_api3('CustomGroup', 'getsingle', array('name' => 'travelcase_status'));
    $this->visa = civicrm_api3('CustomField', 'getsingle', array('name' => 'visa', 'custom_group_id' => $this->case_status['id']));
    $this->ticket = civicrm_api3('CustomField', 'getsingle', array('name' => 'ticket', 'custom_group_id' => $this->case_status['id']));
    $this->pickup = civicrm_api3('CustomField', 'getsingle', array('name' => 'pickup', 'custom_group_id' => $this->case_status['id']));
    $this->accomodation = civicrm_api3('CustomField', 'getsingle', array('name' => 'accomodation', 'custom_group_id' => $this->case_status['id']));
  }
  
  /**
   * @return CRM_Travelcase_TravelCaseStatusConfig 
   */
  public static function singleton() {
    if (!self::$_singleton) {
      self::$_singleton = new CRM_Travelcase_TravelCaseStatusConfig();
    }
    return self::$_singleton;
  }
  
  public function getCustomFieldVisa($key='id') {
    return $this->visa[$key];
  }
  
  public function getCustomFieldTicket($key='id') {
    return $this->ticket[$key];
  }
  
  public function getCustomFieldPickup($key='id') {
    return $this->pickup[$key];
  }
  
  public function getCustomFieldAccomodation($key='id') {
    return $this->accomodation[$key];
  }
  
  public function getCustomGroupTravelCaseStatus($key='id') {
    return $this->case_status[$key];
  }
  
}