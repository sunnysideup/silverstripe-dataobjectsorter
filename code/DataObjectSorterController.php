<?php
/**
 *@author nicolaas [at] sunnysideup.co.nz
 *@description: allows you to sort dataobjects, you need to provide them in this way: http://www.mysite.com/dataobjectsorter/[dataobjectname]/
 *
 *@package: dataobjectsorter
 **/

class DataObjectSorterController  extends Controller{

	function Children() {
		$class = Director::URLParam("Action");
		if($class) {
			if(class_exists($class)) {
				$where = '';
				$filterField = Convert::raw2sql(Director::URLParam("ID"));
				$filterValue = Convert::raw2sql(Director::URLParam("OtherID"));
				if($filterField && $filterValue) {
					$where = "`$filterField` = '$filterValue'";
				}
				elseif(is_numeric($filterField)) {
					$where = "`ParentID` = '$filterField'";
				}
				$sort = "";
				if(DataObjectSorterDOD::get_do_not_add_alternative_sort_field()) {
					$sort = "`Sort` ASC";
				}
				else{
					$sort = "`AlternativeSortNumber` ASC";
				}
				$objects = DataObject::get($class, $where, $sort);
				if($objects && $objects->count()) {
					foreach($objects as $obj) {
						if($obj->hasField("Sort") || $obj->hasField("AlternativeSortNumber")) {
							$obj->initDataObjectSorter();
							return $objects;
						}
						else {
							user_error("No field Sort or AlternativeSortNumber was found on data object: ".$class, E_USER_WARNING);
						}
					}
				}
				else {
						user_error("No objects could be found that matched: select from ".$class." where ".$where, E_USER_WARNING);
				}
			}
			else {
				user_error("$class does not exist", E_USER_WARNING);
			}
		}
		else {
			user_error("Please make sure to provide a class to sort e.g. http://www.sunnysideup.co.nz/dataobjectsorter/MyLongList - where MyLongList is the DataObject you want to sort.", E_USER_WARNING);
		}
	}

	function startsort() {
		return array();
	}

	function index() {
		return array();
	}

	function dodataobjectsort() {
		$class = Director::URLParam("ID");
		if($class) {
			if(class_exists($class)) {
				$obj = DataObject::get_one($class);
				return $obj->dodataobjectsort();
			}
			else {
				user_error("$class does not exist", E_USER_WARNING);
			}
		}
		else {
			user_error("Please make sure to provide a class to sort e.g. http://www.sunnysideup.co.nz/dataobjectsorter/MyLongList - where MyLongList is the DataObject you want to sort.", E_USER_WARNING);
		}
	}

}