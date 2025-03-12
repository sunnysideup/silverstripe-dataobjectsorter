<?php

namespace Sunnysideup\DataObjectSorter\Extensions;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Core\Extension;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\ORM\FieldType\DBCurrency;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\FieldType\DBHTMLVarchar;
use SilverStripe\ORM\FieldType\DBInt;
use SilverStripe\ORM\FieldType\DBVarchar;
use SilverStripe\Versioned\Versioned;
use Sunnysideup\DataObjectSorter\DataObjectOneFieldUpdateController;

/**
 * Class \Sunnysideup\DataObjectSorter\Extensions\DataObjectEditAnythingExtension
 *
 * @property DataObjectEditAnythingExtension $owner
 */
class DataObjectEditAnythingExtension extends Extension
{

    private static $included_field_types_for_quick_edit = [
        'Varchar',
        DBVarchar::class,

        'Int',
        DBInt::class,

        'Boolean',
        DBBoolean::class,

        'Currency',
        DBCurrency::class,
    ];
    private static $excluded_field_types_for_quick_edit = [
        'HTMLText',
        'HTMLVarchar',
        DBHTMLText::class,
        DBHTMLVarchar::class,
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $owner = $this->getOwner();
        $dbFields = $owner->config()->get('db');
        $excludeFields = $owner->config()->get('excluded_field_types_for_quick_edit');
        $includeFields = $owner->config()->get('included_field_types_for_quick_edit');
        foreach ($dbFields as $dbField => $dbType) {
            if (!empty($excludeFields) && in_array($dbField, $excludeFields)) {
                continue;
            }
            if (!empty($includeFields)) {
                $includeField = false;
                foreach ($includeFields as $includeField) {
                    if (stripos($dbType, $includeField) === 0) {
                        $includeField = true;
                        break;
                    }
                }
                if (!$includeField) {
                    continue;
                }
            }
            $myFormField = $fields->dataFieldByName($dbField);
            if ($myFormField) {
                if ($myFormField->isReadonly()) {
                    continue;
                }
                if ($myFormField->isDisabled()) {
                    continue;
                }
                $getMethod = 'getRightTitle';
                $setMethod = 'setRightTitle';
                if (!$myFormField->hasMethod('getRightTitle')) {
                    $getMethod = 'getDescription';
                    $setMethod = 'setDescription';
                }
                $rightTitle = $myFormField->$getMethod();
                $rightTitleArray = [
                    $rightTitle,
                    DataObjectOneFieldUpdateController::popup_link($owner->ClassName, $dbField, null, null, '✎ Edit this field for all records'),
                ];
                $rightTitleArray = array_filter($rightTitleArray);
                $myFormField->$setMethod(implode('<br />', $rightTitleArray));
            }
        }
    }
}
