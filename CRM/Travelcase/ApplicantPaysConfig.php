<?php

class CRM_Travelcase_ApplicantPaysConfig {
  
  protected static $singleton;
  
  protected $custom_group_ids;
  
  protected $activity_type_id;
  
  protected $restrictied_activities;
  
  protected function __construct() {
    $this->activity_type_id = civicrm_api3('OptionValue', 'getvalue', array('name' => 'Debriefing Expert', 'option_group_id' => 2, 'return' => 'value'));
    $this->loadCustomGroupIds();
    $this->loadRestrictedActivities();
  }
  
  /**
   * 
   * @return CRM_Travelcase_ApplicantPaysConfig
   */
  public static function singleton() {
    if (!self::$singleton) {
      self::$singleton = new CRM_Travelcase_ApplicantPaysConfig();
    }
    return self::$singleton;
  }
  
  protected function loadCustomGroupIds() {
    $ta_config = CRM_Travelcase_InfoForTravelAgencyConfig::singleton();
    $dsa_config = CRM_Travelcase_InfoForDsaConfig::singleton();
    
    $this->custom_group_ids = array();
    $this->custom_group_ids[] = $ta_config->getInfoForTravelAgencyCustomGroup('id');
    $this->custom_group_ids[] = $dsa_config->getCustomGroupInfoForDsa('id');
    $this->custom_group_ids[] = civicrm_api3('CustomGroup', 'getvalue', array('name' => 'travel_parent', 'return' => 'id'));
    $this->custom_group_ids[] = civicrm_api3('CustomGroup', 'getvalue', array('name' => 'travel_data', 'return' => 'id'));
    $this->custom_group_ids[] = civicrm_api3('CustomGroup', 'getvalue', array('name' => 'traveler_information', 'return' => 'id'));
    $this->custom_group_ids[] = civicrm_api3('CustomGroup', 'getvalue', array('name' => 'pickup', 'return' => 'id'));
  }
  
  protected function loadRestrictedActivities() {
    $this->restrictied_activities = array();
    $this->restrictied_activities[] = $this->activity_type_id;
    $this->restrictied_activities[] = civicrm_api3('OptionValue', 'getvalue', array('name' => 'Change Case Status', 'option_group_id' => 2, 'return' => 'value'));
    $this->restrictied_activities[] = civicrm_api3('OptionValue', 'getvalue', array('name' => 'Letter of Invitation', 'option_group_id' => 2, 'return' => 'value'));
    $this->restrictied_activities[] = civicrm_api3('OptionValue', 'getvalue', array('name' => 'DSA', 'option_group_id' => 2, 'return' => 'value'));
  }
  
  public function getRestrictiedActivites() {
    return $this->restrictied_activities;
  }
  
  /**
   * Return the custom group IDs for applicant pays
   * Those groups are going to be checked
   * 
   */
  public function getCustomGroupIds() {
    return $this->custom_group_ids;
  }
  
  /**
   * 
   * @return id of the applicant pays activity
   */
  public function getActivityTypeId() {
    return $this->activity_type_id;
  }
  
}

