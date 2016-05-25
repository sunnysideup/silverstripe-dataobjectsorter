<?php
/**
 *@author nicolaas [at] sunnysideup.co.nz
 *@package: dataobjectsorter
 *@description: allows you to edit one record
 *
 **/

class DataObjectOneRecordUpdateController extends Controller{

	public static function popup_link($className, $recordID, $linkText = '') {
		Requirements::javascript("dataobjectsorter/javascript/jquery.simplemodal-1.4.4.js");
		Requirements::javascript("dataobjectsorter/javascript/dataobjectmodalpopup.js");
		Requirements::themedCSS("dataobjectmodalpopup", "dataobjectsorter");
		if(!$linkText) {
			$linkText = 'click here to edit';
		}
		$obj = singleton($className);
		if($obj->canEdit()) {
			$link = '/dataobjectonerecordupdate/show/'.$className."/".$recordID;
			return '
				<a href="'.$link.'" class="modalPopUp" data-width="800" data-height="600" data-rel="window.open(\''.$link.'\', \'sortlistFor'.$className.$recordID.'\',\'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=600,height=600,left = 440,top = 200\'); return false;">'.$linkText.'</a>';
		}
	}

	private static $allowed_actions = array("onerecordform", "show", "save");

	function init() {
		// Only administrators can run this method
		parent::init();
		if(!Permission::check("CMS_ACCESS_CMSMain")) {
			Security::permissionFailure($this, _t('Security.PERMFAILURE',' This page is secured and you need administrator rights to access it. Enter your credentials below and we will send you right along.'));
		}
		Requirements::javascript(THIRDPARTY_DIR."/jquery/jquery.js");
		//Requirements::block(THIRDPARTY_DIR."/jquery/jquery.js");
		//Requirements::javascript(Director::protocol()."ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js");
		Requirements::javascript("dataobjectsorter/javascript/dataobjectonerecordupdate.js");
		$url = Director::absoluteURL("dataobjectonerecordupdate/updaterecord/");
		Requirements::customScript("DataObjectOneRecordUpdateURL = '".$url."'");
		Requirements::themedCSS("dataobjectonerecordupdate", "dataobjectsorter");
	}

	function onerecordform() {
		$table = $this->SecureTableToBeUpdated();
		$record = $this->SecureRecordToBeUpdated();
		$obj = $table::get()->byID($record);
		if(!$obj) {
			user_error("record could not be found!", E_USER_ERROR);
		}
		if(!$obj->canEdit()) {
			Security::permissionFailure($this, _t('Security.PERMFAILURE',' This page is secured and you need administrator rights to access it. Enter your credentials below and we will send you right along.'));
		}
		$formFields = $this->getFormFields($obj);
		if(!$formFields) {
			user_error("Form Fields could not be Found", E_USER_ERROR);
		}
		$fields = new FieldList(
			new HiddenField("Table", "Table", $table),
			new HiddenField("Record", "Record", $record)
		);
		foreach($formFields as $f) {
			$fields->push($f);
		}

		$form = new Form(
			$controller = $this,
			$name = "OneRecordForm",
			$fields,
			$actions = new FieldList(new FormAction("save", "save and close"))
		);
		$form->loadDataFrom($obj);
		return $form;
	}

	function save($data, $form) {
		$table = $this->SecureTableToBeUpdated();
		$record = $this->SecureRecordToBeUpdated();
		$obj = $table::get()->byID($record);
		$form->saveInto($obj);
		$obj->write();
		return '
			<p>Your changes have been saved, please <a href="#" onclick="self.close(); return false;">close window</a>.</p>
			<script type="text/javascript">self.close();</script>';
	}

	function show() {
		return array();
	}


	public function HumanReadableTableName() {
		return singleton($this->SecureTableToBeUpdated())->plural_name();
	}

	public function Link($action = null) {
		$link = "dataobjectonerecordupdate/";
		if($action) {
			$link .= "$action/";
		}
		return $link;
	}

	protected function getFormFields($obj) {
		return $obj->getFrontEndFields();
	}


	protected function SecureTableToBeUpdated() {
		if(isset($_POST["Table"])) {
			$table = addslashes($_POST["Table"]);
		}
		else {
			$table = $this->getRequest()->param("ID");
		}
		if(class_exists($table)) {
			return $table;
		}
		else {
			user_error("could not find record: $table", E_USER_ERROR);
		}
	}

	protected function SecureRecordToBeUpdated() {
		if(isset($_POST["Record"])) {
			$recordID = $_POST["Record"];
		}
		else {
			$recordID = $this->getRequest()->param("OtherID");
		}
		return intval($recordID);
	}


}
