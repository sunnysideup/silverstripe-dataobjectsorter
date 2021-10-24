<?php

namespace Sunnysideup\DataObjectSorter;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\Versioned\Versioned;
use SilverStripe\View\ArrayData;
use Sunnysideup\DataObjectSorter\Api\DataObjectSorterRequirements;

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
 */
class DataObjectOneFieldUpdateController extends DataObjectSortBaseClass
{
    private static $allowed_actions = [
        'updatefield' => 'DATA_OBJECT_SORT_AND_EDIT_PERMISSION',
        'show' => 'DATA_OBJECT_SORT_AND_EDIT_PERMISSION',
    ];

    /**
     * make sure to also change in routes if you change this link.
     *
     * @var string
     */
    private static $url_segment = 'dataobjectonefieldupdate';

    private static $page_size = 5000;

    private static $field;

    private static $_objects;

    private static $_objects_without_field;

    /**
     * @param string $where
     * @param string $sort
     * @param string $titleField
     *
     * @return string
     */
    public static function popup_link_only(string $className, string $fieldName, ?string $where = '', ?string $sort = '', ?string $titleField = 'Title')
    {
        $params = self::params_builder(
            [
                'where' => $where,
                'sort' => $sort,
                'titleField' => $titleField,
            ]
        );

        return self::link_only_maker(
            DataObjectOneFieldUpdateController::class,
            'show/' . self::classNameToString($className) . '/' . $fieldName,
            $params
        );
    }

    /**
     * @param string $where
     * @param string $sort
     * @param string $linkText
     * @param string $titleField
     */
    public static function popup_link(
        string $className,
        string $fieldName,
        ?string $where = '',
        ?string $sort = '',
        ?string $linkText = 'click here to edit',
        ?string $titleField = 'Title'
    ): string {
        $link = self::popup_link_only($className, $fieldName, $where, $sort, $titleField = 'Title');

        return self::link_html_maker(
            $link,
            'modalPopUp modal-popup',
            'editOne' . self::classNameToString($className) . $fieldName,
            $linkText
        );
    }

    public static function button_link(
        string $className,
        string $fieldName,
        ?string $where = '',
        ?string $sort = '',
        ?string $linkText = 'click here to edit',
        ?string $titleField = 'Title'
    ): string {
        $link = self::popup_link_only($className, $fieldName, $where, $sort, $titleField = 'Title');

        return self::button_maker(
            $link,
            'modalPopUp modal-popup',
            'editOne' . self::classNameToString($className) . $fieldName,
            $linkText
        );
    }

    public function updatefield($request = null)
    {
        Versioned::set_reading_mode('Stage.Stage');
        $updateMessage = '';
        $updateCount = 0;
        $className = $this->SecureClassNameToBeUpdated();
        $field = $request->param('OtherID');
        $titleField = $request->requestVar('titlefield');
        $ids = trim($request->requestVar('id')) ? explode(',', $request->requestVar('id')) : [];
        $newValue = $request->requestVar('value');
        if (0 !== Member::currentUserID()) {
            if (class_exists($className) && count($ids) > 0 && ($newValue || 0 === (int) $newValue)) {
                foreach ($ids as $id) {
                    if ((int) $id > 0) {
                        /** @var null|DataObject $obj */
                        $obj = $className::get()->byID($id);
                        if ($obj) {
                            if ($obj->hasDatabaseField($field)) {
                                if ($obj->canEdit()) {
                                    $obj->{$field} = $newValue;
                                    $title = 'no title for record'l
                                    if ($obj instanceof SiteTree) {
                                        $obj->writeToStage(Versioned::DRAFT);
                                        $obj->publishRecursive();
                                    } else {
                                        $obj->write();
                                    }
                                    if ($titleField && $obj->hasDatabaseField($titleField)) {
                                        $title = $obj->{$titleField};
                                    } elseif ($obj->hasMethod('Title')) {
                                        $title = $obj->Title();
                                    } elseif ($obj->hasMethod('getTitle')) {
                                        $title = $obj->getTitle();
                                    }
                                    $newValueObject = $obj->dbObject($field);
                                    $newValueFancy = $newValueObject->hasMethod('Nice') ? $newValueObject->Nice() : $newValueObject->Raw();
                                    ++$updateCount;
                                    $updateMessage .= "
                                        Record updated:
                                        <i class=\"fieldTitle\">{$this->SecureFieldToBeUpdatedNice()}</i>
                                        for <i class=\"recordTitle\">" . $title . '</i>
                                        updated to <i class="newValue">' . $newValueFancy . '</i><br />';
                                }
                            } else {
                                user_error('field does not exist', E_USER_ERROR);
                            }
                        } else {
                            user_error("could not find record: {${$className}}, {$id} ", E_USER_ERROR);
                        }
                    }
                }
                if ($updateCount > 1) {
                    return "{$updateCount} records Updated";
                }

                return $updateMessage;
            }
            user_error("data object specified: '{$className}' or id count: '" . count($ids) . "' or newValue: '{$newValue}' is not valid", E_USER_ERROR);
        } else {
            user_error('you need to be logged in to make the changes', E_USER_ERROR);
        }
    }

    //used in template
    public function DataObjectsToBeUpdated()
    {
        Versioned::set_reading_mode('Stage.Stage');
        if (null === self::$_objects) {
            $field = $this->SecureFieldToBeUpdated();
            $records = $records->getRecordsPaginated();
            $arrayList = ArrayList::create();
            if ($records->exists()) {
                foreach ($records as $obj) {
                    if ($obj->canEdit()) {
                        $ids[$obj->ID] = $obj->ID;
                        $obj->FormField = $obj->dbObject($field)->scaffoldFormField();
                        $obj->FormField->setName(self::classNameToString($obj->ClassName) . '/' . $obj->ID);
                        //3.0TODO Check that I work vvv.
                        $obj->FormField->addExtraClass('updateField');
                        $obj->FieldToBeUpdatedValue = $obj->{$field};
                        $obj->FormField->setValue($obj->{$field});
                        $title = $obj->getTitle();
                        $arrayList->push(new ArrayData(['FormField' => $obj->FormField, 'MyTitle' => $title]));
                    }
                }
            }
            self::$_objects = $arrayList;
            self::$_objects_without_field = $records;
        }

        return self::$_objects;
    }

    /**
     * retun a list of objects
     * we need it like this for pagination....
     *
     * @return \SilverStripe\ORM\DataList
     */
    public function PaginatedListItems()
    {
        $this->DataObjectsToBeUpdated();

        return self::$_objects_without_field;
    }

    protected function init()
    {
        //must set this first ...
        DataObjectSorterRequirements::theme_fix();
        parent::init();
        DataObjectSorterRequirements::popup_requirements(
            'onefield',
        );
        DataObjectSorterRequirements::url_variable(
            DataObjectOneFieldUpdateController::class,
            'DataObjectOneFieldUpdateURL'
        );
    }
}
