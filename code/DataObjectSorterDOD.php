<?php

class DataObjectSorterDOD extends DataObjectDecorator {


	protected static $also_update_sort_field = false;
		static function set_also_update_sort_field($v) {self::$also_update_sort_field = ($v ? true : false);}

	protected static $do_not_add_alternative_sort_field = false;
		static function set_do_not_add_alternative_sort_field($v) {self::$do_not_add_alternative_sort_field = ($v ? true : false);}


	function extraDBFields(){
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
						$i = $i + mysql_affected_rows();
						if("SiteTree" == $baseDataClass) {
							$sql_Live = str_replace('`SiteTree`', '`SiteTree_Live`', $sql);
							//echo $sql_Live .'<hr />';
							DB::query($sql_Live);
							$i = $i + mysql_affected_rows() - 1;
						}
					}
				}
			}
			return "Updated $i record(s)";
		}
		else {
			return "please log-in as an administrator to make changes to the sort order";
		}
	}

	function initDataObjectSorter() {
		Requirements::javascript("dataobjectsorter/javascript/jquery-1.3.2.min.js");
		Requirements::block(THIRDPARTY_DIR."/jquery/jquery.js");
		Requirements::javascript("dataobjectsorter/javascript/jquery-ui-1.7.2.custom.min.js");
		Requirements::themedCSS("dataobjectsorter");
		Requirements::customScript('var DataObjectSorterURL = "'.Director::absoluteURL($this->owner->Link()).'";');
	}
}


