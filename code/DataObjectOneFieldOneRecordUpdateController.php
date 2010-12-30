<?php
/**
 *@author nicolaas [at] sunnysideup.co.nz
 *@package: dataobjectsorter
 *@description: allows you to edit one field in one record
 *
 **/

class DataObjectOneFieldOneRecordUpdateController extends Controller{

	public static function popup_link($ClassName, $FieldName, $recordID, $linkText = '') {
		if(!$linkText) {
			$linkText = 'click here to edit';
		}
		$obj = singleton($ClassName);
		if($obj->canEdit()) {
			$link = 'dataobjectonefieldonerecordupdate/show/'.$ClassName."/".$FieldName."/?id=".$recordID;
			return '
				<a href="'.$link.'" onclick="window.open(\''.$link.'\', \'sortlistFor'.$ClassName.$FieldName.$recordID.'\',\'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=600,height=600,left = 440,top = 200\'); return false;">'.$linkText.'</a>';
		}
	}

	static $allowed_actions = array("onefieldform", "show", "save");

	function init() {
		// Only administrators can run this method
		parent::init();
		if(!Permission::check("CMS_ACCESS_CMSMain")) {
			Security::permissionFailure($this, _t('Security.PERMFAILURE',' This page is secured and you need administrator rights to access it. Enter your credentials below and we will send you right along.'));
		}
		Requirements::javascript(THIRDPARTY_DIR."/jquery/jquery.js");
		Requirements::javascript("dataobjectsorter/javascript/dataobjectonefieldonerecordupdate.js");
		$url = Director::absoluteURL("dataobjectonefieldonerecordupdate/updatefield/");
		Requirements::customScript("DataObjectOneFieldOneRecordUpdateURL = '".$url."'");
		Requirements::themedCSS("dataobjectonefieldonerecordupdate");
	}

	function onefieldform() {
		$table = $this->SecureTableToBeUpdated();
		$field = $this->SecureFieldToBeUpdated();
		$record = $this->SecureRecordToBeUpdated();
		$obj = DataObject::get_by_id($table, $record);
		if(!$obj) {
			user_error("record could not be found!", E_USER_ERROR);
		}
		if(!$obj->canEdit()) {
			Security::permissionFailure($this, _t('Security.PERMFAILURE',' This page is secured and you need administrator rights to access it. Enter your credentials below and we will send you right along.'));
		}
		$FormField = $this->getFormField($obj, $field);
		if(!$FormField) {
			user_error("Form Field could not be Found", E_USER_ERROR);
		}
		$FormField->setValue($obj->$field);
		$form = new Form(
			$controller = $this,
			$name = "OneFieldForm",
			$fields = new FieldSet(
				$FormField,
				new HiddenField("Table", "Table", $table),
				new HiddenField("Field", "Field", $field),
				new HiddenField("Record", "Record", $record)
			),
			$actions = new FieldSet(new FormAction("save", "save and close"))
		);
		return $form;
	}

	function save($data, $form) {
		$table = $this->SecureTableToBeUpdated();
		$field = $this->SecureFieldToBeUpdated();
		$record = $this->SecureRecordToBeUpdated();
		$obj = DataObject::get_by_id($table, $record);
		$obj->$field = $data[$field];
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

	public function Link() {
		return "dataobjectonefieldonerecordupdate/";
	}

	protected function getFormField($obj, $fieldName) {
		return $obj->dbObject($fieldName)->scaffoldFormField($obj->Title);
	}



	protected function SecureFieldToBeUpdated() {
		if(isset($_POST["Field"])) {
			return addslashes($_POST["Field"]);
		}
		$field = Director::URLParam("OtherID");
		if($table = $this->SecureTableToBeUpdated()) {
			if($tableObject = DataObject::get_one($table)) {
				if($tableObject->hasField($field)) {
					return $field;
				}
				else {
					user_error("$field does not exist on $table", E_USER_ERROR);
				}
			}
			else {
				user_error("there are no records in $table", E_USER_ERROR);
			}
		}
		else {
			user_error("there is no table specified", E_USER_ERROR);
		}
	}

	protected function SecureTableToBeUpdated() {
		if(isset($_POST["Table"])) {
			$table = addslashes($_POST["Table"]);
		}
		else {
			$table = Director::URLParam("ID");
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
			return intval($_POST["Record"]);
		}
		if(isset( $_GET["id"])) {
			$record = $_GET["id"];
			return intval($record);
		}
		return 0;
	}


}
