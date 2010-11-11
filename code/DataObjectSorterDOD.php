<?php
/**
 *@author nicolaas [at] sunnysideup.co.nz
 *@description: adds dataobject sorting functionality
 *
 *@package: dataobjectsorter
 **/

class DataObjectSorterDOD extends DataObjectDecorator {


	protected static $also_update_sort_field = false;
		static function set_also_update_sort_field($v) {self::$also_update_sort_field = $v;}
		static function get_also_update_sort_field() {return self::$also_update_sort_field;}

	protected static $do_not_add_alternative_sort_field = false;
		static function set_do_not_add_alternative_sort_field($v) {self::$do_not_add_alternative_sort_field = $v;}
		static function get_do_not_add_alternative_sort_field() {return self::$do_not_add_alternative_sort_field;}

	function extraStatics(){
		//this is not actually working because in dev/build, this statement is executed BEFORE the settting above is applied!
		//maybe add field in another way????
		if(self::$do_not_add_alternative_sort_field) {
			return array();
		}
		else {
			return array(
				'db' =>   array(
					"AlternativeSortNumber" => "Int"
				)
			);
		}
	}



	function dodataobjectsort() {
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";
		if(!Permission::check("ADMIN")) {
			Security::permissionFailure($this, _t('Security.PERMFAILURE',' This page is secured and you need administrator rights to access it. Enter your credentials below and we will send you right along.'));
		}
		$i = 0;
		if($this->owner->canEdit()) {
			$extraSet = '';
			$extraWhere = '';
			if(self::$do_not_add_alternative_sort_field) {
				$field = "Sort";
			}
			else {
				$field = "AlternativeSortNumber";
			}
			$baseDataClass = ClassInfo::baseDataClass($this->owner->ClassName);
			if($baseDataClass) {
				if(isset ($_REQUEST["dos"])) {
					foreach ($_REQUEST['dos'] as $position => $id) {
						$i++;
						$position = intval($position);
						$id = intval($id);
						if(self::$also_update_sort_field && !self::$do_not_add_alternative_sort_field) {
							$extraSet = ", {$bt}".$baseDataClass."{$bt}.{$bt}Sort{$bt} = ".$position;
							$extraWhere = " OR {$bt}".$baseDataClass."{$bt}.{$bt}Sort{$bt} <> ".$position;
						}
						$sql = "
							UPDATE {$bt}".$baseDataClass."{$bt}
							SET {$bt}".$baseDataClass."{$bt}.{$bt}".$field."{$bt} = ".$position. " ".$extraSet."
							WHERE {$bt}".$baseDataClass."{$bt}.{$bt}ID{$bt} = ".$id."
								AND ({$bt}".$baseDataClass."{$bt}.{$bt}".$field."{$bt} <> ".$position." ".$extraWhere.")
							LIMIT 1;";
						//echo $sql .'<hr />';
						DB::query($sql);
						if("SiteTree" == $baseDataClass) {
							$sql_Live = str_replace("{$bt}SiteTree{$bt}", "{$bt}SiteTree_Live{$bt}", $sql);
							//echo $sql_Live .'<hr />';
							DB::query($sql_Live);
						}
					}
				}
			}
			return "Updated record(s)";
		}
		else {
			return "please log-in as an administrator to make changes to the sort order";
		}
	}

	/**
	 *legacy function
	 **/

	function($filterField, $filterValue) {
		return DataObjectSorterController::popup_link($className = $this->ClassName, $filterField, $filterValue, $linkText = "sort this list")
	}

}


