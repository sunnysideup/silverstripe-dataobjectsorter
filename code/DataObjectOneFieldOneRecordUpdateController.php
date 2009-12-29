<?php
/**
 *@author nicolaas [at] sunnysideup.co.nz
 *@todo:
 *  pagination (and many other things)
 *  use scaffolding method (of some sort) to get right field type
 *
 *
 *@package: dataobjectsorter
 *@description: allows you to quickly review and update one field for all records
 * e.g. update price for all products
 * URL is like this
 * dataobjectonefieldupdate//$Action/$ID/$OtherID
 * dataobjectonefieldupdate/[show]/[updatefield]/[tablename]/[fieldname]
 * TO BE COMPLETED!
 *
 **/

class DataObjectOneFieldOneRecordUpdateController extends Controller{

	public static function popup_link($ClassName, $FieldName, $recordID) {
		$obj = singleton($ClassName);
		if($obj->canEdit()) {
			$link = 'dataobjectonefieldonerecordupdate/show/'.$ClassName."/".$FieldName."/".$recordID;
			return '
				<a href="'.$link.'" onclick="window.open(\''.$link.'\', \'sortlistFor'.$ClassName.$FieldName.$recordID.'\',\'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=600,height=600,left = 440,top = 200\'); return false;">click here to edit</a>';
		}
	}

	static $allowed_actions = array("updatefield", "show");

	function init() {
		// Only administrators can run this method
		parent::init();
		if(!Permission::check("ADMIN")) {
			Security::permissionFailure($this, _t('Security.PERMFAILURE',' This page is secured and you need administrator rights to access it. Enter your credentials below and we will send you right along.'));
		}
		Requirements::javascript(THIRDPARTY_DIR."/jquery/jquery.js");
		Requirements::javascript("dataobjectsorter/javascript/dataobjectonefieldonerecordupdate.js");
		$url = Director::absoluteURL("dataobjectonefieldonerecordupdate/updatefield/");
		Requirements::customScript("DataObjectOneFieldOneRecordUpdateURL = '".$url."'");
		Requirements::themedCSS("dataobjectonefieldonerecordupdate");
	}

	function DataObjectsToBeUpdated() {
		$table = $this->SecureTableToBeUpdated();
		$field = $this->SecureFieldToBeUpdated();
		$record = $this->SecureRecordToBeUpdated();
		$obj = DataObject::get_by_id($table, $record);
		if(!$obj->canEdit()) {
			Security::permissionFailure($this, _t('Security.PERMFAILURE',' This page is secured and you need administrator rights to access it. Enter your credentials below and we will send you right along.'));
		}
		$obj->FieldToBeUpdatedValue = $obj->$field;
		$obj->FormField = $this->getFormField($obj, $field);
		$obj->FormField->setName($obj->ClassName."/".$obj->ID);
		$obj->FormField->addExtraClass("updateField");
		$obj->FormField->setValue($obj->$field);
		return $obj;
	}

	function getFormField($obj, $fieldName) {
		$fields = $obj->getFrontEndFields();
		foreach($fields as $field) {
			if($field->Name() == $fieldName) {
				return $field;
			}
		}
	}


	function HumanReadableTableName() {
		return singleton($this->SecureTableToBeUpdated())->plural_name();
	}

	function show() {
		return array();
	}

	function updatefield($request = null) {
		if(Permission::check("ADMIN")) {
			$table = $request->param("ID");
			$field = $request->param("OtherID");
			$id = intval($request->getVar("id"));
			$newValue = $request->getVar("value");
			if($memberID = Member::currentUserID() ) {
				if(class_exists($table) && $id && ($newValue || $newValue == 0)) {
					if($obj = DataObject::get_by_id($table, $id)) {
						if($obj->hasField($field)) {
							$obj->$field = $newValue;
							if($obj instanceOf SiteTree) {
								$obj->writeToStage("Stage");
								$obj->publish("Stage", "Live");
							}
							else {
								$obj->write();
							}
							if(method_exists($obj, "Title")) {
								$title = $obj->Title();
							}
							elseif(method_exists($obj, "getTitle")) {
								$title = $obj->getTitle();
							}
							elseif($title = $obj->Title) {
								//do nothing
							}
							elseif($title = $obj->Name) {
								//do nothing
							}
							else {
								$title = $obj->ID;
							}
							return "Record updated: <i class=\"fieldTitle\">$field</i>  for <i class=\"recordTitle\">".$title ."</i> updated to <i class=\"newValue\">".$newValue."</i>";
						}
						else {
							user_error("field does not exist", E_USER_ERROR);
						}
					}
					else {
						user_error("could not find record: $table, $id ", E_USER_ERROR);
					}
				}
				else {
					user_error("data object specified: $table or id: $id or newValue: $newValue is not valid", E_USER_ERROR);
				}
			}
			else {
				user_error("you need to be logged-in to make the changes", E_USER_ERROR);
			}
		}
		else {
			user_error("sorry, you do not have access to this page", E_USER_ERROR);
		}
	}

	protected function SecureFieldToBeUpdated() {
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
		$table = Director::URLParam("ID");
		if(class_exists($table)) {
			return $table;
		}
		else {
			user_error("could not find record: $table", E_USER_ERROR);
		}
	}

	protected function SecureRecordToBeUpdated() {
		$record = Director::URLParam("RecordID");
		return intval($record);
	}




}