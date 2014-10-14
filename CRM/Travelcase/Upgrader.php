<?php

/**
 * Collection of upgrade steps
 */
class CRM_Travelcase_Upgrader extends CRM_Travelcase_Upgrader_Base {

  protected $case_type_id;
  
  public function install() {
    $this->addCaseTypes();
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
