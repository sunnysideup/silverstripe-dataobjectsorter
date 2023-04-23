<?php

namespace Sunnysideup\DataObjectSorter;

use SilverStripe\Control\Director;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;
use Sunnysideup\DataObjectSorter\Api\DataObjectSorterRequirements;

/**
 * Class \Sunnysideup\DataObjectSorter\DataObjectSorterController
 *
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
     *     $fields->addFieldToTab("Root.Position", new LiteralField("AdvertisementsSorter", DataObjectSorterController::popup_link("Advertisement", $filterField = "", $filterValue = "", $linkText = "sort ".Advertisement::$plural_name, $titleField = "FullTitle")));
     * }
     * else {
     *     $fields->addFieldToTab("Root.Position", new NumericField($name = "Sort", "Sort index number (the lower the number, the earlier it shows up"));
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
    public static function popup_link_only(
        string $className,
        $filterField = '',
        ?string $filterValue = '',
        ?string $titleField = ''
    ) {
        $params = self::params_builder(
            [
                'filterField' => $filterField,
                'filterValue' => $filterValue,
                'titleField' => $titleField,
            ]
        );

        return self::link_only_maker(
            DataObjectSorterController::class,
            'sort/' . self::classNameToString($className),
            $params
        );
    }

    /**
     * returns a link for sorting objects. You can use this in the CMS like this....
     * <code>
     * if(class_exists("DataObjectSorterController")) {
     *     $fields->addFieldToTab("Root.Position", new LiteralField("AdvertisementsSorter", DataObjectSorterController::popup_link("Advertisement", $filterField = "", $filterValue = "", $linkText = "sort ".Advertisement::$plural_name, $titleField = "FullTitle")));
     * }
     * else {
     *     $fields->addFieldToTab("Root.Position", new NumericField($name = "Sort", "Sort index number (the lower the number, the earlier it shows up"));
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
    public static function popup_link(
        string $className,
        $filterField = '',
        ?string $filterValue = '',
        ?string $linkText = 'sort this list',
        ?string $titleField = ''
    ) {
        $link = self::popup_link_only($className, $filterField, $filterValue, $titleField);

        return self::link_html_maker(
            $link,
            'modalPopUp modal-popup',
            'sortlistFor' . self::classNameToString($className) . $filterField . $filterValue,
            $linkText
        );
    }

    /**
     * returns a link for sorting objects. You can use this in the CMS like this....
     * <code>
     * if(class_exists("DataObjectSorterController")) {
     *     $fields->addFieldToTab("Root.Position", new LiteralField("AdvertisementsSorter", DataObjectSorterController::popup_link("Advertisement", $filterField = "", $filterValue = "", $linkText = "sort ".Advertisement::$plural_name, $titleField = "FullTitle")));
     * }
     * else {
     *     $fields->addFieldToTab("Root.Position", new NumericField($name = "Sort", "Sort index number (the lower the number, the earlier it shows up"));
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
    public static function button_link(
        string $className,
        $filterField = '',
        ?string $filterValue = '',
        ?string $linkText = 'sort this list',
        ?string $titleField = ''
    ) {
        $link = self::popup_link_only($className, $filterField, $filterValue, $titleField);

        return self::button_maker(
            $link,
            'modalPopUp modal-popup',
            'sortlistFor' . self::classNameToString($className) . $filterField . $filterValue,
            $linkText
        );
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
        $obj = $this->SecureSingletonToBeUpdated();
        if ($obj) {
            return $obj->dodataobjectsort($request->requestVar('dos'));
            user_error("{$class} does not exist", E_USER_WARNING);
        } else {
            user_error('Please make sure to provide a class to sort e.g. /dataobjectsorter/MyLongList - where MyLongList is the DataObject you want to sort.', E_USER_WARNING);
        }
    }

    /**
     * runs the actual sorting...
     *
     * @return null|DataList - return dataobject set of items to be sorted
     */
    public function Children(): ?DataList
    {
        if (null === self::$_children_cache_for_sorting) {
            $objects = $this->getRecords();
            if ($objects) {
                $singletonObj = $this->SecureSingletonToBeUpdated();
                $className = $this->SecureClassNameToBeUpdated();
                $sortField = $singletonObj->SortFieldForDataObjectSorter();
                $objects = $objects->sort($sortField, 'ASC');
                $tobeExcludedArray = [];
                $titleField = (string) $this->request->requestVar('titleField');
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

                    if ([] !== $tobeExcludedArray) {
                        $objects = $objects->exclude(['ID' => $tobeExcludedArray]);
                    }

                    $this->addRequirements($className);
                    self::$_children_cache_for_sorting = $objects;
                } else {
                    return null;
                }
            } else {
                user_error('Please make sure to provide a class to sort e.g. /dataobjectsorter/MyLongList - where MyLongList is the DataObject you want to sort.', E_USER_WARNING);
            }
        }

        return self::$_children_cache_for_sorting;
    }

    protected function init()
    {
        DataObjectSorterRequirements::theme_fix();
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
        $classNameShort = self::classNameToString($className);
        DataObjectSorterRequirements::url_variable(
            DataObjectSorterController::class,
            'DataObjectSorterURL',
            'dosort/' . $classNameShort
        );
        // $url = Director::absoluteURL(
        //     Injector::inst()->get()->Link()
        // );
    }
}
