<?php
/**
 *@author nicolaas [at] sunnysideup.co.nz
 *@todo:
 *  pagination
 *  use scaffolding method (of some sort) to get right field type
 * (and many other things)
 *
 *@package: dataobjectsorter
 *@description: allows you to quickly review and update one field for all records
 * e.g. update price for all products
 * URL is like this
 * dataobjectonefieldupdate//$Action/$ID/$OtherID
 * dataobjectonefieldupdate/[show]/[updatefield]/[tablename]/[fieldname]
 *
 **/

class DataObjectOneFieldUpdateController extends Controller{

	protected static $page_size = 50;
		static function set_page_size($v) {self::$page_size = $v;}
		static function get_page_size() {return self::$page_size;}


	public static function popup_link($ClassName, $FieldName, $where = '') {
		$obj = singleton($ClassName);
		if($where) {
			self::set_dataobject_one_field_update_controller_where($where);
		}
		else {
			self::unset_dataobject_one_field_update_controller_where($where);
		}
		if($obj->canEdit()) {
			$link = 'dataobjectonefieldupdate/show/'.$ClassName."/".$FieldName;
			return '
				<a href="'.$link.'" onclick="window.open(\''.$link.'\', \'sortlistFor'.$ClassName.$FieldName.'\',\'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=600,height=600,left = 440,top = 200\'); return false;">click here to edit</a>';
		}
	}

	static function get_dataobject_one_field_update_controller_where() {
		return Session::get("DataObjectOneFieldUpdateControllerWhere");
	}

	static function set_dataobject_one_field_update_controller_where($where) {
		Session::set("DataObjectOneFieldUpdateControllerWhere", $where);
	}

	static function unset_dataobject_one_field_update_controller_where($where) {
		Session::set("DataObjectOneFieldUpdateControllerWhere", "");
		Session::clear("DataObjectOneFieldUpdateControllerWhere");
	}


	static $allowed_actions = array("updatefield", "show");

	function init() {
		// Only administrators can run this method
		if(!Permission::check("ADMIN")) {
			Security::permissionFailure($this, _t('Security.PERMFAILURE',' This page is secured and you need administrator rights to access it. Enter your credentials below and we will send you right along.'));
		}
		parent::init();
		Requirements::javascript(THIRDPARTY_DIR."/jquery/jquery.js");
		Requirements::javascript("dataobjectsorter/javascript/dataobjectonefieldupdate.js");
		$url = Director::absoluteURL("dataobjectonefieldupdate/updatefield/");
		Requirements::customScript("DataObjectOneFieldUpdateURL = '".$url."'");
		Requirements::themedCSS("dataobjectonefieldupdate");
	}

	function DataObjectsToBeUpdated() {
		$table = $this->SecureTableToBeUpdated();
		$field = $this->SecureFieldToBeUpdated();
		$where = self::get_dataobject_one_field_update_controller_where();
		if(!$where) {
			$where = '';
		}
		$start = 0;
		if(isset($this->requestParams["start"])) {
			$start = $this->requestParams["start"];
		}
		$objects = DataObject::get($table, $where, $sort = null, $join = null, $limit = "$start, ".self::get_page_size());
		foreach($objects as $obj) {
			$obj->FieldToBeUpdatedValue = $obj->$field;
			$obj->FormField = $this->getFormField($obj, $field);
			$obj->FormField->setName($obj->ClassName."/".$obj->ID);
			$obj->FormField->addExtraClass("updateField");
			$obj->FormField->setValue($obj->$field);
		}
		if(!$obj->canEdit()) {
			Security::permissionFailure($this, _t('Security.PERMFAILURE',' This page is secured and you need administrator rights to access it. Enter your credentials below and we will send you right along.'));
		}
		return $objects;
	}

	function getFormField($obj, $fieldName) {
		$fields = $obj->getFrontEndFields();
		foreach($fields as $field) {
			if($field->Name() == $fieldName) {
				return $field;
			}
		}
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
				user_error("you need to be logged in to make the changes", E_USER_ERROR);
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

	function HumanReadableTableName() {
		return singleton($this->SecureTableToBeUpdated())->plural_name();
	}



}
