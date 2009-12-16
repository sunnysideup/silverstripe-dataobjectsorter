<?php
/**
 *@author nicolaas [at] sunnysideup.co.nz
 *@description: adds dataobject sorting functionality
 *
 *@package: dataobjectsorter
 **/

class DataObjectSorterDOD extends DataObjectDecorator {


	protected static $also_update_sort_field = false;
		static function set_also_update_sort_field($v) {self::$also_update_sort_field = ($v ? true : false);}
		static function get_also_update_sort_field() {return self::$also_update_sort_field;}

	protected static $do_not_add_alternative_sort_field = false;
		static function set_do_not_add_alternative_sort_field($v) {self::$do_not_add_alternative_sort_field = ($v ? true : false);}
		static function get_do_not_add_alternative_sort_field() {return self::$do_not_add_alternative_sort_field;}

	function extraStatics(){
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
							$extraSet = ', `'.$baseDataClass.'`.`Sort` = '.$position;
							$extraWhere = ' OR `'.$baseDataClass.'`.`Sort` <> '.$position;
						}
						$sql = 'UPDATE `'.$baseDataClass.'` SET `'.$baseDataClass.'`.`'.$field.'` = '.$position.' '.$extraSet.' WHERE `'.$baseDataClass.'`.`ID` = '.$id.' AND (`'.$baseDataClass.'`.`'.$field.'` <> '.$position.' '.$extraWhere.') LIMIT 1;';
						//echo $sql .'<hr />';
						DB::query($sql);
						if("SiteTree" == $baseDataClass) {
							$sql_Live = str_replace('`SiteTree`', '`SiteTree_Live`', $sql);
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

	function initDataObjectSorter() {
		Requirements::block(THIRDPARTY_DIR."/jquery/jquery.js");
		Requirements::javascript("dataobjectsorter/javascript/jquery-1.3.2.min.js");
		Requirements::javascript("dataobjectsorter/javascript/jquery-ui-1.7.2.custom.min.js");
		Requirements::javascript("dataobjectsorter/javascript/dataobjectsorter.js");
		Requirements::themedCSS("dataobjectsorter");
		Requirements::customScript('var DataObjectSorterURL = "'.Director::absoluteURL("dataobjectsorter/dodataobjectsort/".$this->owner->ClassName."/").'";');
	}

	function dataObjectSorterPopupLink($fieldOrID = "", $id = 0) {
		$link = 'dataobjectsorter/'.$this->owner->ClassName."/";
		if($fieldOrID) {
			$link .= $fieldOrID.'/';
		}
		if($id) {
		 $link .= $id.'/';
		}
		return '
		<a href="'.$link.'" onclick="window.open(\''.$link.'\', \'sortlistFor'.$this->owner->ClassName.$fieldOrID.$id.'\',\'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=600,height=600,left = 440,top = 200\'); return false;">click here to sort list</a>';
	}


}


