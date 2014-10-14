<?php

/**
 * Collection of upgrade steps
 */
class CRM_Travelcase_Upgrader extends CRM_Travelcase_Upgrader_Base {

  protected $case_type_id;
  
  protected $activity_type;
  
  public function install() {
    $this->addCaseTypes();
    $this->addActivityTYpes();
    
    $this->executeCustomDataFile('xml/traveler_information.xml');
    $this->executeCustomDataFile('xml/travelinformation.xml');
    $this->executeCustomDataFile('xml/travelcase.xml');
    $this->executeCustomDataFile('xml/info_for_travel_agency.xml');
  }
  
  protected function addActivityTYpes() {
    if (empty($this->activity_type)) {
      $this->activity_type = civicrm_api3('OptionGroup', 'getvalue', array('return' => 'id', 'name' => 'activity_type'));
    }
    
    $this->addOptionValue('DSA', 'DSA', $this->activity_type);
    $this->addOptionValue('Pick Up Information Customer', 'Pick Up Information Customer', $this->activity_type);
    $this->addOptionValue('Letter of Invitation', 'Letter of Invitation', $this->activity_type);
    $this->addOptionValue('Visa documents from Expert', 'Visa documents from Expert', $this->activity_type);
    $this->addOptionValue('Visa Request', 'Visa Request', $this->activity_type);
  }
  
  protected function addCaseTypes() {
    if (empty($this->case_type_id)) {
      $this->case_type_id = civicrm_api3('OptionGroup', 'getvalue', array('return' => 'id', 'name' => 'case_type'));
    }
    
    $this->addOptionValue('TravelCase', 'Travel Case', $this->case_type_id);
  }
  
  protected function addOptionValue($name, $label, $option_group_id) {
    try {
      $exist_id = civicrm_api3('OptionValue', 'getvalue', array('return' => 'id', 'name' => $name, 'option_group_id' => $option_group_id));
      return; //aleardy exist
    } catch (Exception $e) {
      //do nothing
    }
    
    $params['name'] = $name;
    $params['label'] = $label;
    $params['option_group_id'] = $option_group_id;
    civicrm_api3('OptionValue','create', $params);
  }

}
