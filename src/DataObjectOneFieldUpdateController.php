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

class DataObjectOneFieldUpdateController extends DataObjectSortBaseClass
{
    private static $allowed_actions = array(
        "updatefield" => 'DATA_OBJECT_SORT_AND_EDIT_PERMISSION',
        "show" => 'DATA_OBJECT_SORT_AND_EDIT_PERMISSION'
    );

    /**
     *
     * make sure to also change in routes if you change this link
     * @var string
     */
    private static $url_segment = 'dataobjectonefieldupdate';

    private static $page_size = 50;

    private static $field = null;

    private static $_objects = null;

    private static $_objects_without_field = null;

    /**
     *
     * @param  string $ClassName
     * @param  string $FieldName
     * @param  string $where
     * @param  string $sort
     * @param  string $titleField
     *
     * @return string
     */
    public static function popup_link_only($ClassName, $FieldName, $where = '', $sort = '', $titleField = "Title")
    {
        DataObjectSorterRequirements::popup_link_requirements();
        $params = array();
        if ($where) {
            $params["where"] = "where=".urlencode($where);
        }
        if ($sort) {
            $params["sort"] = "sort=".urlencode($sort);
        }
        if ($titleField) {
            $params["titlefield"] = "titlefield=".urlencode($titleField);
        }
        return Injector::inst()->get('DataObjectOneFieldUpdateController')
            ->Link('show/'.$ClassName."/".$FieldName).'?'.implode("&amp;", $params);
    }

    /**
     *
     * @param  string $ClassName
     * @param  string $FieldName
     * @param  string $where
     * @param  string $sort
     * @param  string $linkText
     * @param  string $titleField
     *
     * @return string
     */
    public static function popup_link($ClassName, $FieldName, $where = '', $sort = '', $linkText = 'click here to edit', $titleField = "Title")
    {
        $link = self::popup_link_only($ClassName, $FieldName, $where, $sort, $titleField = "Title");
        if ($link) {
            return '
                <a href="'.$link.'" class="modalPopUp modal-popup" data-width="800" data-height="600" data-rel="window.open(\''.$link.'\', \'sortlistFor'.$ClassName.$FieldName.'\',\'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=600,height=600,left = 440,top = 200\'); return false;">'.$linkText.'</a>';
        }
    }


    public function init()
    {
        //must set this first ...
        Config::inst()->update('SSViewer', 'theme_enabled', Config::inst()->get('DataObjectSorterRequirements', 'run_through_theme'));
        parent::init();
        DataObjectSorterRequirements::popup_requirements('onefield');
        $url = Director::absoluteURL(
            Injector::inst()->get('DataObjectOneFieldUpdateController')
                ->Link('updatefield')
        );
        Requirements::customScript(
            "var DataObjectOneFieldUpdateURL = '".$url."'",
            'DataObjectOneFieldUpdateURL'
        );
    }

    public function updatefield($request = null)
    {
        Versioned::set_reading_mode('Stage.Stage');
        $updateMessage = "";
        $updateCount = 0;
        $table = $request->param("ID");
        $field = $request->param("OtherID");
        $titleField = $request->getVar('titlefield');
        $ids = explode(",", $request->getVar("id"));
        $newValue = $request->getVar("value");
        if ($memberID = Member::currentUserID()) {
            if (class_exists($table) && count($ids) > 0 && ($newValue || $newValue == 0)) {
                foreach ($ids as $id) {
                    if (intval($id)) {
                        if ($obj = $table::get()->byID($id)) {
                            if ($obj->hasDatabaseField($field)) {
                                if ($obj->canEdit()) {
                                    $obj->$field = $newValue;
                                    if ($obj instanceof SiteTree) {
                                        $obj->writeToStage("Stage");
                                        $obj->publish("Stage", "Live");
                                    } else {
                                        $obj->write();
                                    }
                                    if ($titleField && $obj->hasDatabaseField($titleField)) {
                                        $title = $obj->$titleField;
                                    } elseif ($obj->hasMethod("Title")) {
                                        $title = $obj->Title();
                                    } elseif ($obj->hasMethod("getTitle")) {
                                        $title = $obj->getTitle();
                                    } elseif ($title = $obj->Title) {
                                        //do nothing
                                    } elseif ($title = $obj->Name) {
                                        //do nothing
                                    } else {
                                        $title = $obj->ID;
                                    }
                                    $dbField = $obj->stat('db');
                                    $newValueObject = $obj->dbObject($field);
                                    if ($newValueObject->hasMethod('Nice')) {
                                        $newValueFancy = $newValueObject->Nice();
                                    } else {
                                        $newValueFancy = $newValueObject->Raw();
                                    }
                                    $updateCount++;
                                    $updateMessage .= "Record updated: <i class=\"fieldTitle\">$field</i>  for <i class=\"recordTitle\">".$title ."</i> updated to <i class=\"newValue\">".$newValueFancy."</i><br />";
                                }
                            } else {
                                user_error("field does not exist", E_USER_ERROR);
                            }
                        } else {
                            user_error("could not find record: $table, $id ", E_USER_ERROR);
                        }
                    }
                }
                if ($updateCount > 1) {
                    return "$updateCount records Updated";
                } else {
                    return $updateMessage;
                }
            } else {
                user_error("data object specified: '$table' or id count: '".count($ids)."' or newValue: '$newValue' is not valid", E_USER_ERROR);
            }
        } else {
            user_error("you need to be logged in to make the changes", E_USER_ERROR);
        }
    }

    //used in template
    public function DataObjectsToBeUpdated()
    {
        Versioned::set_reading_mode('Stage.Stage');
        if (self::$_objects === null) {
            $table = $this->SecureTableToBeUpdated();
            $field = $this->SecureFieldToBeUpdated();
            $where = '';
            if (isset($this->requestParams["where"]) && $this->requestParams["where"]) {
                $where = urldecode($this->requestParams["where"]);
            }
            $sort = '';
            if (isset($this->requestParams["sort"]) && $this->requestParams["sort"]) {
                $sort = urldecode($this->requestParams["sort"]);
            }
            $titleField = 'Title';
            if (isset($this->requestParams["titlefield"]) && $this->requestParams["titlefield"]) {
                $titleField = urldecode($this->requestParams["titlefield"]);
            }
            $start = 0;
            if (isset($this->requestParams["start"])) {
                $start = intval($this->requestParams["start"]);
            }

            if (isset($_GET["debug"])) {
                print_r("SELECT * FROM $table $where SORT BY $sort LIMIT $start, ". Config::inst()->get("DataObjectOneFieldUpdateController", "page_size"));
            }
            $dataList = $table::get()->where($where)->sort($sort)->limit(1000);
            $ids = array();
            if ($dataList->count()) {
                foreach ($dataList as $obj) {
                    if ($obj->canEdit() && $obj->canView()) {
                        $ids[$obj->ID] = $obj->ID;
                    }
                }
            }
            $dataList = $table::get()->filter(array('ID' => $ids))->sort($sort)->limit(1000);
            $_objects = new PaginatedList($dataList, $this->request);
            $_objects->setPageLength(Config::inst()->get("DataObjectOneFieldUpdateController", "page_size"));
            $arrayList = ArrayList::create();
            if ($_objects->count()) {
                foreach ($_objects as $obj) {
                    $obj->FormField = $obj->dbObject($field)->scaffoldFormField();
                    $obj->FormField->setName($obj->ClassName."/".$obj->ID);
                    //3.0TODO Check that I work vvv.
                    $obj->FormField->addExtraClass("updateField");
                    $obj->FieldToBeUpdatedValue = $obj->$field;
                    $obj->FormField->setValue($obj->$field);
                    $title = $obj->getTitle();
                    $arrayList->push(new ArrayData(array("FormField" => $obj->FormField, "MyTitle" => $title)));
                }
            }
            self::$_objects = $arrayList;
            self::$_objects_without_field = $_objects;
        }

        return self::$_objects;
    }

    /**
     * retun a list of objects
     * we need it like this for pagination....
     * @return DataList
     */
    public function PaginatedListItems()
    {
        $this->DataObjectsToBeUpdated();
        return self::$_objects_without_field;
    }
}
