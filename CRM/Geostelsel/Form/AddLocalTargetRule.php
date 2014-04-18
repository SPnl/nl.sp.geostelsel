<?php

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Geostelsel_Form_AddLocalTargetRule extends CRM_Autorelationship_Form_AddTargetRule {
  
  function buildQuickForm() {

    // add form elements    
    $this->addButtons(array(
      array(
        'type' => 'done',
        'name' => ts('Save'),
        'isDefault' => TRUE,
      ),
      array(
        'type' => 'cancel',
        'name' => ts('Cancel'),
      ),
    ));

    parent::buildQuickForm();
  }

  function postProcess() {    
    //check if gemeente already exist
    $exist = false;    
    $sql = "SELECT * FROM `civicrm_autorelationship_local_member` WHERE `contact_id` = %1";
    $dao = CRM_Core_DAO::executeQuery($sql, array(
        '1' => array($this->targetContactId, 'Integer')
    ));
    if ($dao->fetch()) {
      $exist = true;
    }
    
    if (!$exist) {
      $sql = "INSERT INTO `civicrm_autorelationship_local_member` (`contact_id`) VALUES(%1)";
      $dao = CRM_Core_DAO::executeQuery($sql, array(
        '1' => array($this->targetContactId, 'Integer')
        ),
        TRUE,
        'CRM_Autorelationship_DAO'
      );
      
      $this->new_entity_id = $dao->getInsterId();
    }
    
    parent::postProcess();
  }
  
}
