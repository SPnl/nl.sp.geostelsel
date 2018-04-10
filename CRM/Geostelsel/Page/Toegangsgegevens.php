<?php

class CRM_Geostelsel_Page_Toegangsgegevens extends CRM_Core_Page_Basic {

  protected static $_links;

  function getBAOName() {
    return 'CRM_Geostelsel_BAO_Toegangsgegevens';
  }

  function userContext($mode = NULL) {
    return 'civicrm/contact/view';
  }

  function userContextParams($mode=null) {
    $cid = CRM_Utils_Request::retrieve('cid', 'Positive', $this, FALSE, 0);
    return 'cid='.$cid.'&selectedChild=toegangsgegevens&reset=1';
  }
	
	
  public function run() {
		if (!CRM_Geostelsel_Config_Toegang::accessToToegangsgegevensCustomGroup()) {
  		CRM_Core_Session::setStatus('U hebt geen toegang tot deze pagina.', '', 'info');
	    $referer = CRM_Utils_System::refererPath();
	    if ($referer && strpos($referer, $_SERVER['REQUEST_URI']) === false) {
	      CRM_Utils_System::redirect($referer);
	    }
	    else {
	      CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/dashboard'));
	    }
  	}
		return parent::run();
	}

  /**
   * Get action Links
   *
   * @return array (reference) of action links
   */
  function &links() {
    if (!(self::$_links)) {
      self::$_links = array(
        CRM_Core_Action::UPDATE  => array(
          'name'  => ts('Edit'),
          'url'   => 'civicrm/contact/toegangsgegevens',
          'qs'    => 'action=update&id=%%id%%&reset=1&cid=%%cid%%',
          'title' => ts('Edit Toegangsgegevens'),
        ),
        CRM_Core_Action::DELETE  => array(
          'name'  => ts('Delete'),
          'url'   => 'civicrm/contact/toegangsgegevens',
          'qs'    => 'action=delete&id=%%id%%&cid=%%cid%%',
          'title' => ts('Delete Toegangsgegevens'),
        ),
      );
    }
    return self::$_links;
  }

  /**
   * name of the edit form class
   *
   * @return string
   * @access public
   */
  function editForm() {
    return 'CRM_Geostelsel_Form_Toegangsgegevens';
  }

  /**
   * name of the form
   *
   * @return string
   * @access public
   */
  function editName() {
    return 'CRM_Geostelsel_Form_Toegangsgegevens';
  }

  function browse() {
    $n = func_num_args();
    $action = ($n > 0) ? func_get_arg(0) : NULL;
    $links = &$this->links();
    if ($action == NULL) {
      if (!empty($links)) {
        $action = array_sum(array_keys($links));
      }
    }

    $cid = CRM_Utils_Request::retrieve('cid', 'Positive', $this, FALSE, 0);
    $values = CRM_Geostelsel_BAO_Toegangsgegevens::buildTree($cid);
    foreach($values as $rid => $row) {
      $values[$rid]['action'] = CRM_Core_Action::formLink($links, null, array('id' => $row['id'], 'cid' => $cid));
    }
    $this->assign('rows', $values);
    $this->assign('cid', $cid);
  }

}