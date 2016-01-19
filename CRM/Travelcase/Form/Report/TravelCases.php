<?php

class CRM_Travelcase_Form_Report_TravelCases extends CRM_Report_Form {

  protected $_addressField = FALSE;

  protected $_emailField = FALSE;

  protected $_summary = NULL;

  protected $_customGroupExtends = array('Case');
  protected $_customGroupGroupBy = FALSE;
  protected $_add2groupSupported = FALSE;

  function __construct() {
    $project_officers = $this->getAllProjectOfficers();
    $this->case_types = CRM_Case_PseudoConstant::caseType();
    $this->case_statuses = CRM_Case_PseudoConstant::caseStatus();
    $this->deleted_labels = array(
      '' => ts('- select -'),
      0 => ts('No'),
      1 => ts('Yes')
    );
    
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
                array(
                  'title' => ts('Case ID'),
                  'required' => TRUE,
                  'no_display' => TRUE,
                ),
              'subject' => array(
                'title' => ts('Case Subject'),
                'default' => FALSE,
              ),
              'status_id' => array(
                'title' => ts('Status'),
                'default' => TRUE,
              ),
              'case_type_id' => array(
                'title' => ts('Case Type'),
                'default' => FALSE,
              ),
              'start_date' => array(
                'title' => ts('Start Date'),
                'default' => FALSE,
                'type' => CRM_Utils_Type::T_DATE,
              ),
              'end_date' => array(
                'title' => ts('End Date'),
                'default' => FALSE,
                'type' => CRM_Utils_Type::T_DATE,
              ),
              'duration' => array(
                'title' => ts('Duration (Days)'),
                'default' => FALSE,
              ),
            ),
          'filters' =>
            array(
              'start_date' => array(
                'title' => ts('Start Date'),
                'operatorType' => CRM_Report_Form::OP_DATE,
                'type' => CRM_Utils_Type::T_DATE,
              ),
              'end_date' => array(
                'title' => ts('End Date'),
                'operatorType' => CRM_Report_Form::OP_DATE,
                'type' => CRM_Utils_Type::T_DATE,
              ),
              'status_id' => array(
                'title' => ts('Status'),
                'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                'options' => $this->case_statuses,
              ),
              'is_deleted' => array(
                'title' => ts('Deleted?'),
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
      'customer_address' =>
        array(
          'dao' => 'CRM_Core_DAO_Address',
          'fields' =>
            array(
              'customer_country' =>
                array(
                  'name' => 'country_id',
                  'title' => ts('Country of Client (parent case)'),
                  'default' => TRUE,
                ),
            ),
          'grouping' => 'parentcase',
        ),
      'civicrm_parent_case' =>
        array(
          'dao' => 'CRM_Case_DAO_Case',
          'fields' =>
            array(
              'parent_id' =>
                array(
                  'title' => ts('Case ID'),
                  'name' => 'id',
                  'required' => TRUE,
                  'no_display' => TRUE,
                ),
              'parent_subject' => array(
                'name' => 'subject',
                'title' => ts('Case Subject (Parent case)'),
                'default' => FALSE,
              ),
              'parent_status_id' => array(
                'name' => 'status_id',
                'title' => ts('Status (Parent case)'),
                'default' => FALSE,
              ),
              'parent_case_type_id' => array(
                'name' => 'case_type_id',
                'title' => ts('Case Type (Parent case)'),
                'default' => TRUE,
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
      'proj_officer' => array(
        'dao' => 'CRM_Contact_DAO_Contact',
        'fields' =>
          array(
            'proj_officer_name' =>
              array(
                'name' => 'display_name',
                'title' => ts('Project officer (travel case)'),
                'default' => TRUE,
              ),
          ),
        'filters' => array(
          'proj_officer_id' => array(
            'name' => 'id',
            'title' => ts('Project officer'),
            'type' => CRM_Report_Form::OP_INT,
            'default' => 0,
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => $project_officers,
          )
        ),
        'grouping' => 'travelcase',
      ),
    );
    $this->_groupFilter = FALSE;
    $this->_tagFilter = FALSE;
    parent::__construct();
  }

  function addCustomDataToColumns($addFields = TRUE, $permCustomGroupIds = array()) {
    if (empty($this->_customGroupExtends)) {
      return;
    }
    if (!is_array($this->_customGroupExtends)) {
      $this->_customGroupExtends = array($this->_customGroupExtends);
    }
    $customGroupWhere = '';
    if (!empty($permCustomGroupIds)) {
      $customGroupWhere = "cg.id IN (" . implode(',', $permCustomGroupIds) . ") AND";
    }
    $sql = "
SELECT cg.table_name, cg.title, cg.extends, cf.id as cf_id, cf.label,
       cf.column_name, cf.data_type, cf.html_type, cf.option_group_id, cf.time_format,
       cg.extends_entity_column_value, cg.name as custom_group_name, cf.name as custom_field_name
FROM   civicrm_custom_group cg
INNER  JOIN civicrm_custom_field cf ON cg.id = cf.custom_group_id
WHERE cg.extends IN ('" . implode("','", $this->_customGroupExtends) . "') AND
      {$customGroupWhere}
      cg.is_active = 1 AND
      cf.is_active = 1 AND
      cf.is_searchable = 1
ORDER BY cg.weight, cf.weight";
    $customDAO = CRM_Core_DAO::executeQuery($sql);

    $curTable = NULL;
    while ($customDAO->fetch()) {
      if ($customDAO->table_name != $curTable) {
        $curTable = $customDAO->table_name;
        $curFields = $curFilters = array();

        // dummy dao object
        $this->_columns[$curTable]['dao'] = 'CRM_Contact_DAO_Contact';
        $this->_columns[$curTable]['extends'] = $customDAO->extends;
        $this->_columns[$curTable]['extends_entity_column_value'] = $customDAO->extends_entity_column_value;
        $this->_columns[$curTable]['grouping'] = $customDAO->table_name;
        $this->_columns[$curTable]['group_title'] = $customDAO->title;

        foreach (array('fields', 'filters', 'group_bys') as $colKey) {
          if (!array_key_exists($colKey, $this->_columns[$curTable])) {
            $this->_columns[$curTable][$colKey] = array();
          }
        }
      }
      $fieldName = 'custom_' . $customDAO->cf_id;

      if ($addFields) {
        // this makes aliasing work in favor
        $curFields[$fieldName] = array(
          'name' => $customDAO->column_name,
          'title' => $customDAO->label,
          'dataType' => $customDAO->data_type,
          'htmlType' => $customDAO->html_type,
        );
      }
      if ($this->_customGroupFilters) {
        // this makes aliasing work in favor
        $curFilters[$fieldName] = array(
          'name' => $customDAO->column_name,
          'title' => $customDAO->label,
          'dataType' => $customDAO->data_type,
          'htmlType' => $customDAO->html_type,
        );
      }

      switch ($customDAO->data_type) {
        case 'Date':
          // filters
          $curFilters[$fieldName]['operatorType'] = CRM_Report_Form::OP_DATE;
          $curFilters[$fieldName]['type'] = CRM_Utils_Type::T_DATE;
          // CRM-6946, show time part for datetime date fields
          if ($customDAO->time_format) {
            $curFields[$fieldName]['type'] = CRM_Utils_Type::T_TIMESTAMP;
          }
          break;

        case 'Boolean':
          $curFilters[$fieldName]['operatorType'] = CRM_Report_Form::OP_SELECT;
          $curFilters[$fieldName]['options'] = array(
            '' => ts('- select -'),
            1 => ts('Yes'),
            0 => ts('No'),
          );
          $curFilters[$fieldName]['type'] = CRM_Utils_Type::T_INT;
          break;

        case 'Int':
          $curFilters[$fieldName]['operatorType'] = CRM_Report_Form::OP_INT;
          $curFilters[$fieldName]['type'] = CRM_Utils_Type::T_INT;
          break;

        case 'Money':
          $curFilters[$fieldName]['operatorType'] = CRM_Report_Form::OP_FLOAT;
          $curFilters[$fieldName]['type'] = CRM_Utils_Type::T_MONEY;
          break;

        case 'Float':
          $curFilters[$fieldName]['operatorType'] = CRM_Report_Form::OP_FLOAT;
          $curFilters[$fieldName]['type'] = CRM_Utils_Type::T_FLOAT;
          break;

        case 'String':
          $curFilters[$fieldName]['type'] = CRM_Utils_Type::T_STRING;

          if (!empty($customDAO->option_group_id)) {
            if (in_array($customDAO->html_type, array(
              'Multi-Select',
              'AdvMulti-Select',
              'CheckBox'
            ))) {
              $curFilters[$fieldName]['operatorType'] = CRM_Report_Form::OP_MULTISELECT_SEPARATOR;
            }
            else {
              $curFilters[$fieldName]['operatorType'] = CRM_Report_Form::OP_MULTISELECT;
            }
            if ($this->_customGroupFilters) {
              $curFilters[$fieldName]['options'] = array();
              $ogDAO = CRM_Core_DAO::executeQuery("SELECT ov.value, ov.label FROM civicrm_option_value ov WHERE ov.option_group_id = %1 ORDER BY ov.weight", array(
                1 => array(
                  $customDAO->option_group_id,
                  'Integer'
                )
              ));
              while ($ogDAO->fetch()) {
                $curFilters[$fieldName]['options'][$ogDAO->value] = $ogDAO->label;
              }
            }
          }
          break;

        case 'StateProvince':
          if (in_array($customDAO->html_type, array(
            'Multi-Select State/Province'
          ))) {
            $curFilters[$fieldName]['operatorType'] = CRM_Report_Form::OP_MULTISELECT_SEPARATOR;
          }
          else {
            $curFilters[$fieldName]['operatorType'] = CRM_Report_Form::OP_MULTISELECT;
          }
          $curFilters[$fieldName]['options'] = CRM_Core_PseudoConstant::stateProvince();
          break;

        case 'Country':
          if (in_array($customDAO->html_type, array(
            'Multi-Select Country'
          ))) {
            $curFilters[$fieldName]['operatorType'] = CRM_Report_Form::OP_MULTISELECT_SEPARATOR;
          }
          else {
            $curFilters[$fieldName]['operatorType'] = CRM_Report_Form::OP_MULTISELECT;
          }
          $curFilters[$fieldName]['options'] = CRM_Core_PseudoConstant::country();
          break;

        case 'ContactReference':
          $curFilters[$fieldName]['type'] = CRM_Utils_Type::T_STRING;
          $curFilters[$fieldName]['name'] = 'display_name';
          $curFilters[$fieldName]['alias'] = "contact_{$fieldName}_civireport";

          $curFields[$fieldName]['type'] = CRM_Utils_Type::T_STRING;
          $curFields[$fieldName]['name'] = 'display_name';
          $curFields[$fieldName]['alias'] = "contact_{$fieldName}_civireport";
          break;

        default:
          $curFields[$fieldName]['type'] = CRM_Utils_Type::T_STRING;
          $curFilters[$fieldName]['type'] = CRM_Utils_Type::T_STRING;
      }

      if (!array_key_exists('type', $curFields[$fieldName])) {
        $curFields[$fieldName]['type'] = CRM_Utils_Array::value('type', $curFilters[$fieldName], array());
      }

      if ($addFields) {
        $this->_columns[$curTable]['fields'] = array_merge($this->_columns[$curTable]['fields'], $curFields);
      }
      if ($this->_customGroupFilters) {
        $this->_columns[$curTable]['filters'] = array_merge($this->_columns[$curTable]['filters'], $curFilters);
      }
      if ($this->_customGroupGroupBy) {
        $this->_columns[$curTable]['group_bys'] = array_merge($this->_columns[$curTable]['group_bys'], $curFields);
      }
    }

    //add order bys for custom fields
    $config = CRM_Travelcase_Config::singleton();
    $this->_columns[$config->getCustomGroupTravelAgencyInfo('table_name')]['fields']['custom_' . $config->getCustomFieldDepartureDate('id')]['default'] = true;
    $this->_columns[$config->getCustomGroupTravelAgencyInfo('table_name')]['fields']['custom_' . $config->getCustomFieldReturnDate('id')]['default'] = true;
    $this->_columns[$config->getCustomGroupTravelAgencyInfo('table_name')]['fields']['custom_' . $config->getCustomFieldDestination('id')]['default'] = true;

    $status_config = CRM_Travelcase_TravelCaseStatusConfig::singleton();
    $this->_columns[$status_config->getCustomGroupTravelCaseStatus('table_name')]['fields']['custom_'.$status_config->getCustomFieldAccomodation('id')]['default'] = true;
    $this->_columns[$status_config->getCustomGroupTravelCaseStatus('table_name')]['fields']['custom_'.$status_config->getCustomFieldDsa('id')]['default'] = true;
    $this->_columns[$status_config->getCustomGroupTravelCaseStatus('table_name')]['fields']['custom_'.$status_config->getCustomFieldInvitation('id')]['default'] = true;
    $this->_columns[$status_config->getCustomGroupTravelCaseStatus('table_name')]['fields']['custom_'.$status_config->getCustomFieldPickup('id')]['default'] = true;
    $this->_columns[$status_config->getCustomGroupTravelCaseStatus('table_name')]['fields']['custom_'.$status_config->getCustomFieldTicket('id')]['default'] = true;
    $this->_columns[$status_config->getCustomGroupTravelCaseStatus('table_name')]['fields']['custom_'.$status_config->getCustomFieldVisa('id')]['default'] = true;
  }

  function preProcess() {
    parent::preProcess();
  }

  function from() {
    $config = CRM_Travelcase_Config::singleton();
    $cc = $this->_aliases['civicrm_case'];
    $c2 = $this->_aliases['civicrm_contact_a'];
    $ccc = $this->_aliases['civicrm_case_contact'];
    $pcc = $this->_aliases['civicrm_parent_case'];
    $pccc = $this->_aliases['civicrm_parent_case_contact'];
    $c3 = $this->_aliases['customer'];
    $c3_address = $this->_aliases['customer_address'];
    $proff = $this->_aliases['proj_officer'];
    $proff_rel = $this->_aliases['proj_officer'] . '_relationship';

    $this->_from = "
          FROM civicrm_case {$cc}
          inner join civicrm_case_contact {$ccc} on {$ccc}.case_id = {$cc}.id
          inner join civicrm_contact {$c2} on {$c2}.id = {$ccc}.contact_id
          left join `" . $config->getCustomGroupLinkCaseTo('table_name') . "` `linkcase` ON `linkcase`.`entity_id` = `{$cc}`.`id`
          left join `civicrm_case` `{$pcc}` ON `linkcase`.`" . $config->getCustomFieldCaseId('column_name') . "` = `{$pcc}`.`id`
          left join `civicrm_case_contact` `{$pccc}` ON {$pccc}.case_id = `{$pcc}`.`id`
          left join civicrm_contact {$c3} on {$c3}.id={$pccc}.contact_id
          left join civicrm_address {$c3_address} on {$c3}.id = {$c3_address}.contact_id and is_primary = 1
          left join civicrm_relationship {$proff_rel} ON {$proff_rel}.case_id = {$pcc}.id AND {$proff_rel}.relationship_type_id = '" . $config->getRelationshipTypeProjOff('id') . "' AND is_active = 1
          left join civicrm_contact {$proff} ON {$proff_rel}.contact_id_b = {$proff}.id
      ";
  }

  function where() {
    $config = CRM_Travelcase_Config::singleton();
    $cc = $this->_aliases['civicrm_case'];

    $clauses = array();
    foreach ($this->_columns as $tableName => $table) {
      if (array_key_exists('filters', $table)) {
        foreach ($table['filters'] as $fieldName => $field) {
          $clause = NULL;
          if (CRM_Utils_Array::value('operatorType', $field) & CRM_Utils_Type::T_DATE) {
            $relative = CRM_Utils_Array::value("{$fieldName}_relative", $this->_params);
            $from = CRM_Utils_Array::value("{$fieldName}_from", $this->_params);
            $to = CRM_Utils_Array::value("{$fieldName}_to", $this->_params);

            $clause = $this->dateClause($field['name'], $relative, $from, $to, $field['type']);
          } else {

            $op = CRM_Utils_Array::value("{$fieldName}_op", $this->_params);
            if ($fieldName == 'case_type_id') {
              $value = CRM_Utils_Array::value("{$fieldName}_value", $this->_params);
              if (!empty($value)) {
                $clause = "( {$field['dbAlias']} REGEXP '[[:<:]]" . implode('[[:>:]]|[[:<:]]', $value) . "[[:>:]]' )";
              }
              $op = NULL;
            }
            if ($fieldName == 'proj_officer_id') {
              $changedValues = array();
              foreach ($this->_params['proj_officer_id_value'] as $projOfficerKey => $projOfficerValue) {

                if ($projOfficerValue == 0) {
                  $session = CRM_Core_Session::singleton();
                  $changedValues[$projOfficerKey] = $session->get("userID");
                } else {
                  $changedValues[$projOfficerKey] = $projOfficerValue;
                }
                $clause = $this->whereClause($field, $op, $changedValues,
                  CRM_Utils_Array::value("{$fieldName}_min", $this->_params),
                  CRM_Utils_Array::value("{$fieldName}_max", $this->_params)
                );
                $op = NULL;
              }
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

    $clauses[] = "{$cc}.case_type_id = '".$config->getCaseType('value')."'";
    //$clauses[] = $this->setUserClause();

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

  function customDataFrom() {
    if (empty($this->_customGroupExtends)) {
      return;
    }
    $mapper = CRM_Core_BAO_CustomQuery::$extendsMap;

    $config = CRM_Travelcase_Config::singleton();
    $cc = $this->_aliases['civicrm_case'];
    $pcc = $this->_aliases['civicrm_parent_case'];

    foreach ($this->_columns as $table => $prop) {
      if (isset($prop['extends']) && !empty($prop['extends'])) {
        $extendsTable = $mapper[$prop['extends']];

        // check field is in params
        if (!$this->isFieldSelected($prop)) {
          continue;
        }
        if ($extendsTable == 'civicrm_case') {
          if (stripos($prop['extends_entity_column_value'], CRM_Core_DAO::VALUE_SEPARATOR.$config->getCaseType('value').CRM_Core_DAO::VALUE_SEPARATOR)===false) {
            //this custom field does not extends a travel case
            $baseJoin = "{$pcc}.id";
          } else {
            $baseJoin = "{$cc}.id";
          }
        } else {
          $baseJoin = CRM_Utils_Array::value($prop['extends'], $this->_customGroupExtendsJoin, "{$this->_aliases[$extendsTable]}.id");
        }

        $customJoin = is_array($this->_customGroupJoin) ? $this->_customGroupJoin[$table] : $this->_customGroupJoin;
        $this->_from .= "
{$customJoin} {$table} {$this->_aliases[$table]} ON {$this->_aliases[$table]}.entity_id = {$baseJoin}";
        // handle for ContactReference
        if (array_key_exists('fields', $prop)) {
          foreach ($prop['fields'] as $fieldName => $field) {
            if (CRM_Utils_Array::value('dataType', $field) == 'ContactReference') {
              $columnName = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomField', CRM_Core_BAO_CustomField::getKeyID($fieldName), 'column_name');
              $this->_from .= "
LEFT JOIN civicrm_contact {$field['alias']} ON {$field['alias']}.id = {$this->_aliases[$table]}.{$columnName} ";
            }
          }
        }
      }
    }
  }

  function groupBy() {
    $this->_groupBy = "";
  }
  
  function modifyColumnHeaders() {
    $this->_columnHeaders['pick_up'] = array('title' => ts('Pick Up Information'), 'type' => CRM_Utils_Type::T_STRING);
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
    foreach ($rows as $rowNum => $row) {
      if (array_key_exists('civicrm_case_id', $row)) {
        $rows[$rowNum]['pick_up'] = $this->getPickUpInfo($row['civicrm_case_id']);
      }
      if (array_key_exists('civicrm_case_status_id', $row)) {
        if ($value = $row['civicrm_case_status_id']) {
          $rows[$rowNum]['civicrm_case_status_id'] = $this->case_statuses[$value];
          $entryFound = TRUE;
        }
      }

      if (array_key_exists('civicrm_parent_case_parent_status_id', $row)) {
        if ($value = $row['civicrm_parent_case_parent_status_id']) {
          $rows[$rowNum]['civicrm_parent_case_parent_status_id'] = $this->case_statuses[$value];
          $entryFound = TRUE;
        }
      }

      if (array_key_exists('civicrm_case_case_type_id', $row) &&
        CRM_Utils_Array::value('civicrm_case_case_type_id', $rows[$rowNum])
      ) {
        $value = $row['civicrm_case_case_type_id'];
        $typeIds = explode(CRM_Core_DAO::VALUE_SEPARATOR, $value);
        $value = array();
        foreach ($typeIds as $typeId) {
          if ($typeId) {
            $value[$typeId] = $this->case_types[$typeId];
          }
        }
        $rows[$rowNum]['civicrm_case_case_type_id'] = implode(', ', $value);
        $entryFound = TRUE;
      }
      
      if (array_key_exists('civicrm_parent_case_parent_case_type_id', $row) &&
        CRM_Utils_Array::value('civicrm_parent_case_parent_case_type_id', $rows[$rowNum])
      ) {
        $value = $row['civicrm_parent_case_parent_case_type_id'];
        $typeIds = explode(CRM_Core_DAO::VALUE_SEPARATOR, $value);
        $value = array();
        foreach ($typeIds as $typeId) {
          if ($typeId) {
            $value[$typeId] = $this->case_types[$typeId];
          }
        }
        $rows[$rowNum]['civicrm_parent_case_parent_case_type_id'] = implode(', ', $value);
        $entryFound = TRUE;
      }
      
      // convert Client ID to contact page
      if (CRM_Utils_Array::value('civicrm_contact_a_client_name', $rows[$rowNum])) {
        $url = CRM_Utils_System::url("civicrm/contact/view", "action=view&reset=1&cid=" . $row['civicrm_contact_a_id'], $this->_absoluteUrl);
        $rows[$rowNum]['civicrm_contact_a_client_name_link'] = $url;
        $rows[$rowNum]['civicrm_contact_a_client_name_hover'] = ts("View client");
        $entryFound = TRUE;
      }
      
      // convert Client ID to contact page
      if (CRM_Utils_Array::value('customer_customer_name', $rows[$rowNum])) {
        $url = CRM_Utils_System::url("civicrm/contact/view", "action=view&reset=1&cid=" . $row['customer_customer_id'], $this->_absoluteUrl);
        $rows[$rowNum]['customer_customer_name_link'] = $url;
        $rows[$rowNum]['customer_customer_name_hover'] = ts("View client");
        $entryFound = TRUE;
      }

      if (CRM_Utils_Array::value('customer_address_customer_country', $rows[$rowNum])) {
        $rows[$rowNum]['customer_address_customer_country'] = CRM_Core_PseudoConstant::country($rows[$rowNum]['customer_address_customer_country']);
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

  protected function getAllProjectOfficers() {
    $config = CRM_Travelcase_Config::singleton();
    $return = array();
    $return[0] = 'current user';
    $sql = "SELECT c.id, c.display_name
            from civicrm_contact c
            inner join civicrm_relationship cr on cr.contact_id_b = c.id
            where cr.case_id is not NULL
            and cr.relationship_type_id = %1
            order by c.sort_name";
    $params[1] = array($config->getRelationshipTypeProjOff('id'), 'Integer');
    $dao = CRM_Core_DAO::executeQuery($sql, $params);
    while ($dao->fetch()) {
      $return[$dao->id] = $dao->display_name;
    }
    return $return;
  }

  /**
   * Method to get the status of the Pick Up Information activity
   * (issue 2996)
   *
   * @param int $caseId
   * @return string
   * @access private
   */
  private function getPickUpInfo($caseId) {
    $pickUpInfoActivityType = CRM_Threepeas_Utils::getActivityTypeWithName('Pick Up Information');
    $pickUpInfoActivityTypeId = $pickUpInfoActivityType['value'];
    $activityStatus = CRM_Core_PseudoConstant::activityStatus();

    $caseActivityParams = array(
      'case_id' => $caseId,
      'activity_type_id' => $pickUpInfoActivityTypeId
    );
    try {
      $caseActivities = civicrm_api3('CaseActivity', 'Get', $caseActivityParams);
      if ($caseActivities['count'] == 0) {
        return "None";
      }
      foreach ($caseActivities['values'] as $caseActivity) {
        return $activityStatus[$caseActivity['status_id']];
      }
    } catch (CiviCRM_API3_Exception $ex) {
      return "None";
    }
  }

  /**
   * Overridden parent method order_by
   */
  function orderBy() {
    $this->_orderBy  = "";
    $this->_sections = array();
    $this->storeOrderByArray();
    $mainActivityCustomGroup = CRM_Threepeas_Utils::getCustomGroup('main_activity_info');
    if (!empty($mainActivityCustomGroup)) {
      $mainActivityStartDate = CRM_Threepeas_Utils::getCustomField($mainActivityCustomGroup['id'], 'main_activity_start_date');
      if (!empty($mainActivityStartDate)) {
        if (array_key_exists("custom_".$mainActivityStartDate['id'], $this->_params['fields'])) {
          $this->_orderByArray[] = $this->_aliases[$mainActivityCustomGroup['table_name']] . "." . $mainActivityStartDate['column_name'];
        }
      }
    }
    if(!empty($this->_orderByArray) && !$this->_rollup == 'WITH ROLLUP'){
      $this->_orderBy = "ORDER BY " . implode(', ', $this->_orderByArray);
    }
    $this->assign('sections', $this->_sections);
  }
}
