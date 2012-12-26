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
 * dataobjectonefieldupdate/update/$Action/$ID/$OtherID
 * dataobjectonefieldupdate/[show]/[updatefield]/[tablename]/[fieldname]
 *
 **/

class DataObjectOneFieldUpdateController extends Controller{

	protected static $page_size = 50;
		static function set_page_size($v) {self::$page_size = $v;}
		static function get_page_size() {return self::$page_size;}

	protected static $field = null;
		static function set_field($v) {self::$field = $v;}
		static function get_field() {return self::$field;}

	protected static $objects = null;
		static function set_objects($v) {self::$objects = $v;}
		static function get_objects() {return self::$objects;}


	public static function popup_link($ClassName, $FieldName, $where = '', $sort = '', $linkText = '') {
		$obj = singleton($ClassName);
		$params = array();
		if($where) {
			$params["where"] = "where=".urlencode($where);
		}
		if($sort) {
			$params["sort"] = "sort=".urlencode($sort);
		}
		if($obj->canEdit()) {
			$link = '/dataobjectonefieldupdate/show/'.$ClassName."/".$FieldName.'/?'.implode("&amp;", $params);
			if(!$linkText) {
				$linkText = 'click here to edit';
			}
			return '
				<a href="'.$link.'" onclick="window.open(\''.$link.'\', \'sortlistFor'.$ClassName.$FieldName.'\',\'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=600,height=600,left = 440,top = 200\'); return false;">'.$linkText.'</a>';
		}
	}

	static $allowed_actions = array("updatefield", "show");

	function init() {
		// Only administrators can run this method

		parent::init();
		if(!Permission::check("CMS_ACCESS_CMSMain")) {
			Security::permissionFailure($this, _t('Security.PERMFAILURE',' This page is secured and you need administrator rights to access it. Enter your credentials below and we will send you right along.'));
			return;
		}
		Requirements::javascript(THIRDPARTY_DIR."/jquery/jquery.js");
		//Requirements::block(THIRDPARTY_DIR."/jquery/jquery.js");
		//Requirements::javascript(Director::protocol()."ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js");
		Requirements::javascript("dataobjectsorter/javascript/dataobjectonefieldupdate.js");
		$url = Director::absoluteURL("dataobjectonefieldupdate/updatefield/");
		Requirements::customScript("DataObjectOneFieldUpdateURL = '".$url."'");
		Requirements::themedCSS("dataobjectonefieldupdate", "dataobjectsorter");

	}


	function show() {
		return array();
	}

	function updatefield($request = null) {
		if(Permission::check("CMS_ACCESS_CMSMain")) {
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

	//used in template
	public function DataObjectsToBeUpdated() {
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";
		if(!self::$objects) {
			$table = $this->SecureTableToBeUpdated();
			$field = $this->SecureFieldToBeUpdated();
			$where = '';
			if(isset($this->requestParams["where"]) && $this->requestParams["where"]) {
				$where = Convert::raw2sql(urldecode($this->requestParams["where"]));
			}
			$sort = '';
			if(isset($this->requestParams["sort"]) && $this->requestParams["sort"]) {
				$sort = Convert::raw2sql(urldecode($this->requestParams["sort"]));
			}
			$start = 0;
			if(isset($this->requestParams["start"])) {
				$start = $this->requestParams["start"];
			}

			if(isset($_GET["debug"])) {
				print_r("SELECT * FROM $table $where SORT BY $sort LIMIT $start, ".self::get_page_size());
			}
			$objects = DataObject::get(
				$table,
				$where,
				$sort,
				$join = '',
				$limit = "$start, ".self::get_page_size()
			);
			if($objects) {
				foreach($objects as $obj) {
					$obj->FormField = $obj->dbObject($field)->scaffoldFormField();
					$obj->FormField->setName($obj->ClassName."/".$obj->ID);
					$obj->FormField->addExtraClass("updateField");
					$obj->FieldToBeUpdatedValue = $obj->$field;
					$obj->FormField->setValue($obj->$field);
				}
				if(!$obj->canEdit()) {
					Security::permissionFailure($this, _t('Security.PERMFAILURE',' This page is secured and you need administrator rights to access it. Enter your credentials below and we will send you right along.'));
					return;
				}
			}
			self::$objects = $objects;
		}

		return self::$objects;
	}

	protected function getFormField($obj, $fieldName) {
		if(!self::$field) {
			self::$field  = $obj->dbObject($fieldName)->scaffoldFormField($obj->Title);
		}
		return self::$field;
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

	protected function HumanReadableTableName() {
		return singleton($this->SecureTableToBeUpdated())->plural_name();
	}



}
