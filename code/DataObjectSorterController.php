<?php
/**
 *@author nicolaas [at] sunnysideup.co.nz
 *@description: allows you to sort dataobjects, you need to provide them in this way: http://www.mysite.com/dataobjectsorter/[dataobjectname]/
 *
 *@package: dataobjectsorter
 **/

class DataObjectSorterController extends Controller{

	static $allowed_actions = array("sort", "startsort", "dodataobjectsort" );

	function sort() {
		return array();
	}

	function startsort() {
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

	public function Children() {
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";
		$class = Director::URLParam("ID");
		if($class) {
			if(class_exists($class)) {
				$where = '';
				$filterField = Convert::raw2sql(Director::URLParam("OtherID"));
				$filterValue = Convert::raw2sql(Director::URLParam("ThirdID"));
				if($filterField && $filterValue) {
					$where = "{$bt}$filterField{$bt} = '$filterValue'";
				}
				elseif(is_numeric($filterField)) {
					$where = "{$bt}ParentID{$bt} = '$filterField'";
				}
				$sort = "";
				if(DataObjectSorterDOD::get_do_not_add_alternative_sort_field()) {
					$sort = "{$bt}Sort{$bt} ASC";
				}
				else{
					$sort = "{$bt}AlternativeSortNumber{$bt} ASC";
				}
				$objects = DataObject::get($class, $where, $sort);
				if($objects && $objects->count()) {
					foreach($objects as $obj) {
						if($obj->hasField("Sort") || $obj->hasField("AlternativeSortNumber")) {
							self::add_requirements($class);
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


	function add_requirements($className) {
		Requirements::javascript(THIRDPARTY_DIR."/jquery/jquery.js");
		Requirements::javascript("dataobjectsorter/javascript/jquery-ui-1.7.2.custom.min.js");
		Requirements::javascript("dataobjectsorter/javascript/dataobjectsorter.js");
		Requirements::themedCSS("dataobjectsorter");
		Requirements::customScript('var DataObjectSorterURL = "'.Director::absoluteURL("dataobjectsorter/dodataobjectsort/".$className."/").'";', 'initDataObjectSorter');
	}

	function popup_link($className, $filterField = "", $filterValue = "", $linkText = "sort this list") {
		$obj = singleton($className);
		if($obj->canEdit()) {
			$link = 'dataobjectsorter/sort/'.$className."/";
			if($filterField) {
				$link .= $filterField.'/';
			}
			if($filterValue) {
			 $link .= $filterValue.'/';
			}
			return '
			<a href="'.$link.'" onclick="window.open(\''.$link.'\', \'sortlistFor'.$className.$filterField.$filterValue.'\',\'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=600,height=600,left = 440,top = 200\'); return false;">'.$linkText.'</a>';
		}
	}



}
