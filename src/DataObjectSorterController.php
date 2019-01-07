<?php

namespace Sunnysideup\DataobjectSorter;

use Sunnysideup\DataobjectSorter\Api\DataObjectSorterRequirements;
use SilverStripe\Core\Injector\Injector;
use Sunnysideup\DataobjectSorter\DataObjectSorterController;
use SilverStripe\Core\Config\Config;
use SilverStripe\View\SSViewer;
use SilverStripe\Control\Director;
use SilverStripe\Versioned\Versioned;
use SilverStripe\ORM\DataObject;
use SilverStripe\Core\Convert;
use SilverStripe\View\Requirements;

/**
 * @author nicolaas [at] sunnysideup.co.nz
 * @description: allows you to sort dataobjects, you need to provide them in this way: http://www.app.com/dataobjectsorter/[dataobjectname]/
 *
 *
 *
 * @package: dataobjectsorter
 **/

class DataObjectSorterController extends DataObjectSortBaseClass
{

    /**
     * standard SS variable
     *
     */
    private static $allowed_actions = array(
        "sort" => 'DATA_OBJECT_SORT_AND_EDIT_PERMISSION',
        "dosort" => 'DATA_OBJECT_SORT_AND_EDIT_PERMISSION'
    );

    /**
     *
     * make sure to also change in routes if you change this link
     * @var string
     */
    private static $url_segment = 'dataobjectsorter';


    /**
     * returns a link for sorting objects. You can use this in the CMS like this....
     * <code>
     * if(class_exists("DataObjectSorterController")) {
     * 	$fields->addFieldToTab("Root.Position", new LiteralField("AdvertisementsSorter", DataObjectSorterController::popup_link("Advertisement", $filterField = "", $filterValue = "", $linkText = "sort ".Advertisement::$plural_name, $titleField = "FullTitle")));
     * }
     * else {
     * 	$fields->addFieldToTab("Root.Position", new NumericField($name = "Sort", "Sort index number (the lower the number, the earlier it shows up"));
     * }
     * </code>
     *
     * @param String $className - DataObject Class Name you want to sort
     * @param String | Int $filterField - Field you want to filter for OR ParentID number (i.e. you are sorting children of Parent with ID = $filterField)
     * @param String $filterValue - filter field should be equal to this integer OR string. You can provide a list of IDs like this: 1,2,3,4 where the filterFiel is probably equal to ID or MyRelationID
     * @param String $linkText - text to show on the link
     * @param String $titleField - field to show in the sort list. This defaults to the DataObject method "getTitle", but you can use "name" or something like that.
     *
     * @return String - html
     */
    public static function popup_link_only($className, $filterField = "", $filterValue = "", $titleField = "")
    {
        DataObjectSorterRequirements::popup_link_requirements();
        $link = Injector::inst()->get(DataObjectSorterController::class)->Link('sort/'.$className);
        if ($filterField) {
            $link .= $filterField.'/';
        }
        if ($filterValue) {
            $link .= $filterValue.'/';
        }
        if ($titleField) {
            $link .= $titleField.'/';
        }
        return $link;
    }
    /**
     * returns a link for sorting objects. You can use this in the CMS like this....
     * <code>
     * if(class_exists("DataObjectSorterController")) {
     * 	$fields->addFieldToTab("Root.Position", new LiteralField("AdvertisementsSorter", DataObjectSorterController::popup_link("Advertisement", $filterField = "", $filterValue = "", $linkText = "sort ".Advertisement::$plural_name, $titleField = "FullTitle")));
     * }
     * else {
     * 	$fields->addFieldToTab("Root.Position", new NumericField($name = "Sort", "Sort index number (the lower the number, the earlier it shows up"));
     * }
     * </code>
     *
     * @param String $className - DataObject Class Name you want to sort
     * @param String | Int $filterField - Field you want to filter for OR ParentID number (i.e. you are sorting children of Parent with ID = $filterField)
     * @param String $filterValue - filter field should be equal to this integer OR string. You can provide a list of IDs like this: 1,2,3,4 where the filterFiel is probably equal to ID or MyRelationID
     * @param String $linkText - text to show on the link
     * @param String $titleField - field to show in the sort list. This defaults to the DataObject method "getTitle", but you can use "name" or something like that.
     *
     * @return String - html
     */
    public static function popup_link($className, $filterField = "", $filterValue = "", $linkText = "sort this list", $titleField = "")
    {
        $link = self::popup_link_only($className, $filterField, $filterValue, $titleField);
        if ($link) {
            return '
            <a href="'.$link.'" class="modalPopUp modal-popup" data-width="800" data-height="600" data-rel="window.open(\''.$link.'\', \'sortlistFor'.$className.$filterField.$filterValue.'\',\'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=600,height=600,left = 440,top = 200\'); return false;">'.$linkText.'</a>';
        }
    }

    public function init()
    {
        Config::inst()->update(SSViewer::class, 'theme_enabled', Config::inst()->get(DataObjectSorterRequirements::class, 'run_through_theme'));
        parent::init();
        if (Director::is_ajax()) {
        } else {
            DataObjectSorterRequirements::popup_requirements('sorter');
        }
    }

    /**
     * the standard action...
     * no need to add anything here now
     */
    public function sort()
    {
        return array();
    }



    /**
     * runs the actual sorting...
     */
    public function dosort($request)
    {
        Versioned::set_reading_mode('Stage.Stage');
        $class = $request->param("ID");
        if ($class) {
            if (class_exists($class)) {
                $obj = DataObject::get_one($class);
                return $obj->dodataobjectsort($request->requestVar('dos'));
            } else {
                user_error("$class does not exist", E_USER_WARNING);
            }
        } else {
            user_error("Please make sure to provide a class to sort e.g. http://www.sunnysideup.co.nz/dataobjectsorter/MyLongList - where MyLongList is the DataObject you want to sort.", E_USER_WARNING);
        }
    }

    private static $_children_cache_for_sorting = null;

    /**
     * runs the actual sorting...
     * @return Object - return dataobject set of items to be sorted
     */
    public function Children()
    {
        if (self::$_children_cache_for_sorting === null) {
            $class = $this->request->param("ID");
            if ($class) {
                if (class_exists($class)) {
                    $where = '';
                    $filterField = Convert::raw2sql($this->request->param("OtherID"));
                    $filterValue = Convert::raw2sql($this->request->param("ThirdID"));
                    $titleField = Convert::raw2sql($this->request->param("FourthID"));
                    $objects = $class::get();
                    if ($filterField && $filterValue) {
                        $filterValue = explode(",", $filterValue);
                        $objects = $objects->filter(array($filterField => $filterValue));
                    } elseif (is_numeric($filterField)) {
                        $objects = $objects->filter(array("ParentID" => $filterField));
                    }
                    $singletonObj = singleton($class);
                    $sortField = $singletonObj->SortFieldForDataObjectSorter();
                    $objects = $objects->sort($sortField, "ASC");
                    $tobeExcludedArray = array();
                    if ($objects->count()) {
                        foreach ($objects as $obj) {
                            if ($obj->canEdit()) {
                                if ($titleField) {
                                    $method = "getSortTitle";
                                    if ($obj->hasMethod($method)) {
                                        $obj->SortTitle = $obj->$method();
                                    } else {
                                        $method = 'SortTitle';
                                        if ($obj->hasMethod($method)) {
                                            $obj->SortTitle = $obj->$method();
                                        } elseif ($obj->hasDatabaseField($titleField)) {
                                            $obj->SortTitle = $obj->$titleField;
                                        }
                                    }
                                } else {
                                    $obj->SortTitle = $obj->getTitle();
                                }
                            } else {
                                $tobeExcludedArray[$obj->ID] = $obj->ID;
                            }
                        }
                        $objects = $objects->exclude(array('ID' => $tobeExcludedArray));
                        $this->addRequirements($class);
                        self::$_children_cache_for_sorting = $objects;
                    } else {
                        return null;
                    }
                } else {
                    user_error("$class does not exist", E_USER_WARNING);
                }
            } else {
                user_error("Please make sure to provide a class to sort e.g. http://www.sunnysideup.co.nz/dataobjectsorter/MyLongList - where MyLongList is the DataObject you want to sort.", E_USER_WARNING);
            }
        } else {
        }
        return self::$_children_cache_for_sorting;
    }

    /**
     * adds functionality for actual sorting...
     *
     * @param string $className - name of the class being sorted
     */
    protected function addRequirements($className)
    {
        $url = Director::absoluteURL(
            Injector::inst()->get(DataObjectSorterController::class)->Link('dosort/'.$className)
        );
        Requirements::customScript(
            "var DataObjectSorterURL = '".$url."'",
            'DataObjectSorterURL'
        );
    }
}
