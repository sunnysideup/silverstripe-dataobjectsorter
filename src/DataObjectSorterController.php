<?php

namespace Sunnysideup\DataobjectSorter;

use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Convert;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;
use SilverStripe\View\Requirements;
use SilverStripe\View\SSViewer;
use Sunnysideup\DataobjectSorter\Api\DataObjectSorterRequirements;

/**
 * @author nicolaas [at] sunnysideup.co.nz
 * @description: allows you to sort dataobjects, you need to provide them in this way: http://www.app.com/dataobjectsorter/[dataobjectname]/
 *
 * @package: dataobjectsorter
 */
class DataObjectSorterController extends DataObjectSortBaseClass
{
    /**
     * standard SS variable.
     */
    private static $allowed_actions = [
        'sort' => 'DATA_OBJECT_SORT_AND_EDIT_PERMISSION',
        'dosort' => 'DATA_OBJECT_SORT_AND_EDIT_PERMISSION',
    ];

    /**
     * make sure to also change in routes if you change this link.
     *
     * @var string
     */
    private static $url_segment = 'dataobjectsorter';

    private static $_children_cache_for_sorting;

    /**
     * returns a link for sorting objects. You can use this in the CMS like this....
     * <code>
     * if(class_exists("DataObjectSorterController")) {
     * 	$fields->addFieldToTab("Root.Position", new LiteralField("AdvertisementsSorter", DataObjectSorterController::popup_link("Advertisement", $filterField = "", $filterValue = "", $linkText = "sort ".Advertisement::$plural_name, $titleField = "FullTitle")));
     * }
     * else {
     * 	$fields->addFieldToTab("Root.Position", new NumericField($name = "Sort", "Sort index number (the lower the number, the earlier it shows up"));
     * }
     * </code>.
     *
     * @param string     $className   - DataObject Class Name you want to sort
     * @param int|string $filterField - Field you want to filter for OR ParentID number (i.e. you are sorting children of Parent with ID = $filterField)
     * @param string     $filterValue - filter field should be equal to this integer OR string. You can provide a list of IDs like this: 1,2,3,4 where the filterFiel is probably equal to ID or MyRelationID
     * @param string     $titleField  - field to show in the sort list. This defaults to the DataObject method "getTitle", but you can use "name" or something like that.
     *
     * @return string - html
     */
    public static function popup_link_only($className, $filterField = '', $filterValue = '', $titleField = '')
    {
        DataObjectSorterRequirements::popup_link_requirements();
        $className = str_replace('\\', '-', $className);

        return Controller::join_links(
            Injector::inst()->get(DataObjectSorterController::class)->Link('sort'),
            $className,
            $filterField,
            $filterValue,
            $titleField
        );
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
     * </code>.
     *
     * @param string     $className   - DataObject Class Name you want to sort
     * @param int|string $filterField - Field you want to filter for OR ParentID number (i.e. you are sorting children of Parent with ID = $filterField)
     * @param string     $filterValue - filter field should be equal to this integer OR string. You can provide a list of IDs like this: 1,2,3,4 where the filterFiel is probably equal to ID or MyRelationID
     * @param string     $linkText    - text to show on the link
     * @param string     $titleField  - field to show in the sort list. This defaults to the DataObject method "getTitle", but you can use "name" or something like that.
     *
     * @return string - html
     */
    public static function popup_link($className, $filterField = '', $filterValue = '', $linkText = 'sort this list', $titleField = '')
    {
        $link = self::popup_link_only($className, $filterField, $filterValue, $titleField);
        if ('' !== $link) {
            return '
            <a
                href="' . $link . '"
                class="modalPopUp modal-popup"
                data-width="800"
                data-height="600"
                data-rel="window.open(\'' . $link . "', 'sortlistFor" . $className . $filterField . $filterValue . '\',\'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=600,height=600,left = 440,top = 200\'); return false;"
            >' . $linkText . '</a>';
        }

        return '';
    }

    /**
     * the standard action...
     * no need to add anything here now.
     */
    public function sort()
    {
        return $this->renderWith(static::class);
    }

    /**
     * runs the actual sorting...
     *
     * @param mixed $request
     */
    public function dosort($request)
    {
        Versioned::set_reading_mode('Stage.Stage');
        $class = $request->param('ID');
        if ($class) {
            $class = str_replace('-', '\\', $class);
            if (class_exists($class)) {
                $obj = $this->SecureObjectToBeUpdated();

                return $obj->dodataobjectsort($request->requestVar('dos'));
            }
            user_error("{$class} does not exist", E_USER_WARNING);
        } else {
            user_error('Please make sure to provide a class to sort e.g. /dataobjectsorter/MyLongList - where MyLongList is the DataObject you want to sort.', E_USER_WARNING);
        }
    }

    /**
     * runs the actual sorting...
     *
     * @return object - return dataobject set of items to be sorted
     */
    public function Children()
    {
        if (null === self::$_children_cache_for_sorting) {
            $class = $this->request->param('ID');
            if ('' !== $class) {
                $class = str_replace('-', '\\', $class);
                if (class_exists($class)) {
                    $filterField = Convert::raw2sql($this->request->param('OtherID'));
                    $filterValue = Convert::raw2sql($this->request->param('ThirdID'));
                    $titleField = Convert::raw2sql($this->request->param('FourthID'));
                    $objects = $class::get();
                    if ($filterField && $filterValue) {
                        $filterValue = explode(',', $filterValue);
                        $objects = $objects->filter([$filterField => $filterValue]);
                    } elseif (is_numeric($filterField)) {
                        $objects = $objects->filter(['ParentID' => $filterField]);
                    }
                    $singletonObj = \Singleton($class);
                    $sortField = $singletonObj->SortFieldForDataObjectSorter();
                    $objects = $objects->sort($sortField, 'ASC');
                    $tobeExcludedArray = [];
                    if ($objects->exists()) {
                        foreach ($objects as $obj) {
                            if ($obj->canEdit()) {
                                if ($titleField) {
                                    $method = 'getSortTitle';
                                    if ($obj->hasMethod($method)) {
                                        $obj->SortTitle = $obj->{$method}();
                                    } else {
                                        $method = 'SortTitle';
                                        if ($obj->hasMethod($method)) {
                                            $obj->SortTitle = $obj->{$method}();
                                        } elseif ($obj->hasDatabaseField($titleField)) {
                                            $obj->SortTitle = $obj->{$titleField};
                                        }
                                    }
                                } else {
                                    $obj->SortTitle = $obj->getTitle();
                                }
                            } else {
                                $tobeExcludedArray[$obj->ID] = $obj->ID;
                            }
                        }
                        if (count($tobeExcludedArray) > 0) {
                            $objects = $objects->exclude(['ID' => $tobeExcludedArray]);
                        }
                        $this->addRequirements($class);
                        self::$_children_cache_for_sorting = $objects;
                    } else {
                        return null;
                    }
                } else {
                    user_error("{$class} does not exist", E_USER_WARNING);
                }
            } else {
                user_error('Please make sure to provide a class to sort e.g. /dataobjectsorter/MyLongList - where MyLongList is the DataObject you want to sort.', E_USER_WARNING);
            }
        }

        return self::$_children_cache_for_sorting;
    }

    protected function init()
    {
        Config::modify()->update(SSViewer::class, 'theme_enabled', Config::inst()->get(DataObjectSorterRequirements::class, 'run_through_theme'));
        parent::init();
        if (Director::is_ajax()) {
        } else {
            DataObjectSorterRequirements::popup_requirements('sorter');
        }
    }

    /**
     * adds functionality for actual sorting...
     *
     * @param string $className - name of the class being sorted
     */
    protected function addRequirements($className)
    {
        $className = str_replace('\\', '-', $className);
        $url = Director::absoluteURL(
            Injector::inst()->get(DataObjectSorterController::class)->Link('dosort/' . $className)
        );
        Requirements::customScript(
            "var DataObjectSorterURL = '" . $url . "'",
            'DataObjectSorterURL'
        );
    }
}
