<?php
/**
 *@author nicolaas [at] sunnysideup.co.nz
 *@todo: pagination (and many other things)
 *@package: dataobjectsorter
 *@description: allows you to quickly review and update one field for all records
 * e.g. update price for all products
 * URL is like this
 * dataobjectonefieldupdate//$Action/$ID/$OtherID
 * dataobjectonefieldupdate/[show]/[updatefield]/[tablename]/[fieldname]
 *
 **/

class DataObjectOneFieldUpdateController  extends Controller{

	static $allowed_actions = array("updatefield", "show");

	function init() {
		// Only administrators can run this method
		if(!Permission::check("ADMIN")) {
			Security::permissionFailure($this, _t('Security.PERMFAILURE',' This page is secured and you need administrator rights to access it. Enter your credentials below and we will send you right along.'));
		}
		parent::init();
		Requirements::themedCSS("DataObjectOneFieldUpdate");
		Requirements::javascript("dataobjectsorter/javascript/DataObjectOneFieldUpdate.js");
		$url = Director::absoluteURL("dataobjectonefieldupdate/");
		Requirements::customScript("DataObjectOneFieldUpdateURL = '".$url."'");
	}

	function DataObjectsToBeUpdated() {
		return DataObject::get($this->TableToBeUpdated);
	}


	function show() {
		return array();
	}

	function updatefield($request = null) {
		if(Permission::check("ADMIN")) {
			$table = $request->param("ID");
			$field = $request->param("OtherID");
			$id = intval($request->getVar("id"));
			$newValue = intval($request->getVar("value"));
			if($memberID = Member::currentUserID() ) {
				if(class_exists($table) && $id && ($newValue || $newValue === 0)) {
					if($obj = DataObject::get_by_id($table, $id)) {
						if($obj->hasField($field)) {
							$obj->$field = $newValue;
							if($obj instanceOf SiteTree) {
								$obj->writeToStage();
								$obj->publish("Stage", "Live");
							}
							else {
								$obj->write();
							}
							return $obj->Name . ".$field (in $table) updated to ".$newValue;
						}
						else {
							user_error("field does not exist", E_ERROR);
						}
					}
					else {
						user_error("could not find record: $table, $id ", E_ERROR);
					}
				}
				else {
					user_error("data object specified: $table or id: $id or newValue: $newValue is not valid", E_ERROR);
				}
			}
			else {
				user_error("you need to be logged-in to make the changes", E_ERROR);
			}
		}
		else {
			user_error("sorry, you do not have access to this page", E_ERROR);
		}
	}

	protected function FieldToBeUpdated() {
		return Director::URLParam("OtherID");
	}

	protected function TableToBeUpdated() {
		$table = return Director::URLParam("ID");
		if(class_exists($table)) {
			return $table;
		}
		else {
			user_error("could not find record: $table", E_ERROR);
		}
	}


}