<?php

class CRM_Travelcase_Form_Report_TravelCases extends CRM_Report_Form {

  protected $_addressField = FALSE;

  protected $_emailField = FALSE;

  protected $_summary = NULL;

  protected $_customGroupExtends = array('Case');
  protected $_customGroupGroupBy = FALSE; 
  protected $_add2groupSupported = FALSE;
  
  function __construct() {
    
    $this->case_types    = CRM_Case_PseudoConstant::caseType();
    $this->case_statuses = CRM_Case_PseudoConstant::caseStatus();
    $this->deleted_labels = array('' => ts('- select -'), 0 => ts('No'), 1 => ts('Yes'));
    
    $this->_columns = array(
      'civicrm_contact_a' =>
      array(
        'dao' => 'CRM_Contact_DAO_Contact',
        'fields' =>
        array(
          'client_name' =>
          array(
            'name' => 'display_name',
            'title' => ts('Client'),
            'required' => TRUE,
          ),
          'id' =>
          array(
            'no_display' => TRUE,
            'required' => TRUE,
          ),
        ),
        'grouping' => 'travelcase',
      ),
      'civicrm_case' =>
      array(
        'dao' => 'CRM_Case_DAO_Case',
        'fields' =>
        array(
          'id' =>
          array('title' => ts('Case ID'),
            'required' => TRUE,
            'no_display' => TRUE,
          ),
          'subject' => array(
            'title' => ts('Case Subject'), 'default' => FALSE,
          ),
          'status_id' => array(
            'title' => ts('Status'), 'default' => TRUE,
          ),
          'case_type_id' => array(
            'title' => ts('Case Type'), 'default' => FALSE,
          ),
          'start_date' => array(
            'title' => ts('Start Date'), 'default' => FALSE,
            'type' => CRM_Utils_Type::T_DATE,
          ),
          'end_date' => array(
            'title' => ts('End Date'), 'default' => FALSE,
            'type' => CRM_Utils_Type::T_DATE,
          ),
          'duration' => array(
            'title' => ts('Duration (Days)'), 'default' => FALSE,
          ),
        ),
        'filters' =>
        array('start_date' => array('title' => ts('Start Date'),
            'operatorType' => CRM_Report_Form::OP_DATE,
            'type' => CRM_Utils_Type::T_DATE,
          ),
          'end_date' => array('title' => ts('End Date'),
            'operatorType' => CRM_Report_Form::OP_DATE,
            'type' => CRM_Utils_Type::T_DATE,
          ),
          'case_type_id' => array('title' => ts('Case Type'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => $this->case_types,
          ),
          'status_id' => array('title' => ts('Status'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => $this->case_statuses,
          ),
          'is_deleted' => array('title' => ts('Deleted?'),
            'type' => CRM_Report_Form::OP_INT,
            'operatorType' => CRM_Report_Form::OP_SELECT,
            'options' => $this->deleted_labels,
            'default' => 0,
          ),
        ),
        'grouping' => 'travelcase',
      ),
      'customer' =>
      array(
        'dao' => 'CRM_Contact_DAO_Contact',
        'fields' =>
        array(
          'customer_name' =>
          array(
            'name' => 'display_name',
            'title' => ts('Client (parent case)'),
            'default' => TRUE,
          ),
          'customer_id' =>
          array(
            'no_display' => TRUE,
            'required' => TRUE,
            'name' => 'id',
          ),
        ),
        'grouping' => 'parentcase',
      ),
      'civicrm_parent_case' =>
      array(
        'dao' => 'CRM_Case_DAO_Case',
        'fields' =>
        array(
          'id' =>
          array('title' => ts('Case ID'),
            'required' => TRUE,
            'no_display' => TRUE,
          ),
          'subject' => array(
            'title' => ts('Case Subject (Parent case)'), 'default' => FALSE,
          ),
          'status_id' => array(
            'title' => ts('Status (Parent case)'), 'default' => TRUE,
          ),
          'case_type_id' => array(
            'title' => ts('Case Type (Parent case)'), 'default' => FALSE,
          ),
        ),
        'grouping' => 'parentcase',
      ),
       'civicrm_case_contact' =>
      array(
        'dao' => 'CRM_Case_DAO_CaseContact',
      ),
      'civicrm_parent_case_contact' =>
      array(
        'dao' => 'CRM_Case_DAO_CaseContact',
      ),
    );
    $this->_groupFilter = FALSE;
    $this->_tagFilter = FALSE;
    parent::__construct();
  }

  function preProcess() {
    $this->assign('reportTitle', ts('Membership Detail Report'));
    parent::preProcess();
  }

  function select() {
    $select = $this->_columnHeaders = array();

    foreach ($this->_columns as $tableName => $table) {
      if (array_key_exists('fields', $table)) {
        foreach ($table['fields'] as $fieldName => $field) {
          if (CRM_Utils_Array::value('required', $field) ||
            CRM_Utils_Array::value($fieldName, $this->_params['fields'])
          ) {
            if ($tableName == 'civicrm_address') {
              $this->_addressField = TRUE;
            }
            elseif ($tableName == 'civicrm_email') {
              $this->_emailField = TRUE;
            }
            $select[] = "{$field['dbAlias']} as {$tableName}_{$fieldName}";
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = $field['title'];
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['type'] = CRM_Utils_Array::value('type', $field);
          }
        }
      }
    }

    $this->_select = "SELECT " . implode(', ', $select) . " ";
  }

  function from() {
    $config = CRM_Travelcase_Config::singleton();
    $cc  = $this->_aliases['civicrm_case'];
    $c2  = $this->_aliases['civicrm_contact_a'];
    $ccc = $this->_aliases['civicrm_case_contact'];
    $pcc  = $this->_aliases['civicrm_parent_case'];
    $pccc  = $this->_aliases['civicrm_parent_case_contact'];
    $c3  = $this->_aliases['customer'];

    $this->_from = "
          FROM civicrm_case {$cc}
          inner join civicrm_case_contact {$ccc} on {$ccc}.case_id = {$cc}.id
          inner join civicrm_contact {$c2} on {$c2}.id={$ccc}.contact_id
          left join `".$config->getCustomGroupLinkCaseTo('table_name')."` `linkcase` ON `linkcase`.`entity_id` = `{$cc}`.`id`
          left join `civicrm_case` `{$pcc}` ON `linkcase`.`".$config->getCustomFieldCaseId('column_name')."` = `{$pcc}`.`id`
          left join `civicrm_case_contact` `{$pccc}` ON {$pccc}.case_id = `{$pcc}`.`id`
          left join civicrm_contact {$c3} on {$c3}.id={$pccc}.contact_id
      ";
  }

  function where() {
    $clauses = array();
    foreach ($this->_columns as $tableName => $table) {
      if (array_key_exists('filters', $table)) {
        foreach ($table['filters'] as $fieldName => $field) {
          $clause = NULL;
          if (CRM_Utils_Array::value('operatorType', $field) & CRM_Utils_Type::T_DATE) {
            $relative = CRM_Utils_Array::value("{$fieldName}_relative", $this->_params);
            $from     = CRM_Utils_Array::value("{$fieldName}_from", $this->_params);
            $to       = CRM_Utils_Array::value("{$fieldName}_to", $this->_params);

            $clause = $this->dateClause($field['name'], $relative, $from, $to, $field['type']);
          }
          else {
            
            $op = CRM_Utils_Array::value("{$fieldName}_op", $this->_params);
            if ($fieldName == 'case_type_id') {
              $value = CRM_Utils_Array::value("{$fieldName}_value", $this->_params);
              if (!empty($value)) {
                $clause = "( {$field['dbAlias']} REGEXP '[[:<:]]" . implode('[[:>:]]|[[:<:]]', $value) . "[[:>:]]' )";
              }
              $op = NULL;
            }
            
            if ($op) {
              $clause = $this->whereClause($field,
                $op,
                CRM_Utils_Array::value("{$fieldName}_value", $this->_params),
                CRM_Utils_Array::value("{$fieldName}_min", $this->_params),
                CRM_Utils_Array::value("{$fieldName}_max", $this->_params)
              );
            }
          }

          if (!empty($clause)) {
            $clauses[] = $clause;
          }
        }
      }
    }

    if (empty($clauses)) {
      $this->_where = "WHERE ( 1 ) ";
    }
    else {
      $this->_where = "WHERE " . implode(' AND ', $clauses);
    }

    if ($this->_aclWhere) {
      $this->_where .= " AND {$this->_aclWhere} ";
    }
  }

  function groupBy() {
    $this->_groupBy = "";
  }
  
  function modifyColumnHeaders() {
    $this->_columnHeaders['manage_case'] = array(
      'title' => '',
      'type' => CRM_Utils_Type::T_STRING,
    );
  }

  function postProcess() {

    $this->beginPostProcess();

    // get the acl clauses built before we assemble the query
    $this->buildACLClause($this->_aliases['civicrm_contact_a']);
    $sql = $this->buildQuery(TRUE);

    $rows = array();
    $this->buildRows($sql, $rows);

    $this->formatDisplay($rows);
    $this->doTemplateAssignment($rows);
    $this->endPostProcess($rows);
  }

  function alterDisplay(&$rows) {
    $entryFound = FALSE;
    $activityTypes = CRM_Core_PseudoConstant::activityType(TRUE, TRUE);
    foreach ($rows as $rowNum => $row) {
      if (array_key_exists('civicrm_case_status_id', $row)) {
        if ($value = $row['civicrm_case_status_id']) {
          $rows[$rowNum]['civicrm_case_status_id'] = $this->case_statuses[$value];
          $entryFound = TRUE;
        }
      }
      
      if (array_key_exists('civicrm_parent_case_status_id', $row)) {
        if ($value = $row['civicrm_parent_case_status_id']) {
          $rows[$rowNum]['civicrm_parent_case_status_id'] = $this->case_statuses[$value];
          $entryFound = TRUE;
        }
      }

      if (array_key_exists('civicrm_case_case_type_id', $row) &&
        CRM_Utils_Array::value('civicrm_case_case_type_id', $rows[$rowNum])
      ) {
        $value   = $row['civicrm_case_case_type_id'];
        $typeIds = explode(CRM_Core_DAO::VALUE_SEPARATOR, $value);
        $value   = array();
        foreach ($typeIds as $typeId) {
          if ($typeId) {
            $value[$typeId] = $this->case_types[$typeId];
          }
        }
        $rows[$rowNum]['civicrm_case_case_type_id'] = implode(', ', $value);
        $entryFound = TRUE;
      }
      
      if (array_key_exists('civicrm_parent_case_case_type_id', $row) &&
        CRM_Utils_Array::value('civicrm_parent_case_case_type_id', $rows[$rowNum])
      ) {
        $value   = $row['civicrm_parent_case_case_type_id'];
        $typeIds = explode(CRM_Core_DAO::VALUE_SEPARATOR, $value);
        $value   = array();
        foreach ($typeIds as $typeId) {
          if ($typeId) {
            $value[$typeId] = $this->case_types[$typeId];
          }
        }
        $rows[$rowNum]['civicrm_parent_case_case_type_id'] = implode(', ', $value);
        $entryFound = TRUE;
      }
      
      // convert Client ID to contact page
      if (CRM_Utils_Array::value('civicrm_contact_a_client_name', $rows[$rowNum])) {
        $url = CRM_Utils_System::url("civicrm/contact/view?action=view&reset=1&cid". $row['civicrm_contact_a_id'], $this->_absoluteUrl);
        $rows[$rowNum]['civicrm_contact_a_client_name_link'] = $url;
        $rows[$rowNum]['civicrm_contact_a_client_name_hover'] = ts("View client");
        $entryFound = TRUE;
      }
      
      // convert Client ID to contact page
      if (CRM_Utils_Array::value('customer_customer_name', $rows[$rowNum])) {
        $url = CRM_Utils_System::url("civicrm/contact/view?action=view&reset=1&cid". $row['customer_id'], $this->_absoluteUrl);
        $rows[$rowNum]['customer_customer_name_link'] = $url;
        $rows[$rowNum]['customer_customer_name_hover'] = ts("View client");
        $entryFound = TRUE;
      }
      
      if (array_key_exists('civicrm_case_id', $row) &&
        CRM_Utils_Array::value('civicrm_contact_a_id', $rows[$rowNum])
      ) {
        $url = CRM_Utils_System::url("civicrm/contact/view/case",
          'reset=1&action=view&cid=' . $row['civicrm_contact_a_id'] . '&id=' . $row['civicrm_case_id'],
          $this->_absoluteUrl
        );
        $rows[$rowNum]['manage_case'] = ts('Manage');
        $rows[$rowNum]['manage_case_link'] = $url;
        $rows[$rowNum]['manage_case_hover'] = ts("Manage Case");
        $entryFound = TRUE;
      }

      // convert Case ID and Subject to links to Manage Case
      if (array_key_exists('civicrm_case_id', $row) &&
        CRM_Utils_Array::value('civicrm_contact_a_id', $rows[$rowNum])
      ) {
        $url = CRM_Utils_System::url("civicrm/contact/view/case",
          'reset=1&action=view&cid=' . $row['civicrm_contact_a_id'] . '&id=' . $row['civicrm_case_id'],
          $this->_absoluteUrl
        );
        $rows[$rowNum]['civicrm_case_id_link'] = $url;
        $rows[$rowNum]['civicrm_case_id_hover'] = ts("Manage Case");
        $entryFound = TRUE;
      }
      if (array_key_exists('civicrm_case_subject', $row) &&
        CRM_Utils_Array::value('civicrm_contact_a_id', $rows[$rowNum])
      ) {
        $url = CRM_Utils_System::url("civicrm/contact/view/case",
          'reset=1&action=view&cid=' . $row['civicrm_contact_a_id'] . '&id=' . $row['civicrm_case_id'],
          $this->_absoluteUrl
        );
        $rows[$rowNum]['civicrm_case_subject_link'] = $url;
        $rows[$rowNum]['civicrm_case_subject_hover'] = ts("Manage Case");
        $entryFound = TRUE;
      }
      if (array_key_exists('civicrm_case_case_type_id', $row) &&
        CRM_Utils_Array::value('civicrm_contact_a_id', $rows[$rowNum])
      ) {
        $url = CRM_Utils_System::url("civicrm/contact/view/case",
          'reset=1&action=view&cid=' . $row['civicrm_contact_a_id'] . '&id=' . $row['civicrm_case_id'],
          $this->_absoluteUrl
        );
        $rows[$rowNum]['civicrm_case_case_type_id_link'] = $url;
        $rows[$rowNum]['civicrm_case_case_type_id_hover'] = ts("Manage Case");
        $entryFound = TRUE;
      }
      if (array_key_exists('civicrm_case_status_id', $row) &&
        CRM_Utils_Array::value('civicrm_contact_a_id', $rows[$rowNum])
      ) {
        $url = CRM_Utils_System::url("civicrm/contact/view/case",
          'reset=1&action=view&cid=' . $row['civicrm_contact_a_id'] . '&id=' . $row['civicrm_case_id'],
          $this->_absoluteUrl
        );
        $rows[$rowNum]['civicrm_case_status_id_link'] = $url;
        $rows[$rowNum]['civicrm_case_status_id_hover'] = ts("Manage Case");
        $entryFound = TRUE;
      }

      if (array_key_exists('civicrm_case_is_deleted', $row)) {
        $value = $row['civicrm_case_is_deleted'];
        $rows[$rowNum]['civicrm_case_is_deleted'] = $this->deleted_labels[$value];
        $entryFound = TRUE;
      }

      if (!$entryFound) {
        break;
      }
    }
  }
}
