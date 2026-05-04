<?php

namespace Sunnysideup\DataObjectSorter\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\FieldType\DBHTMLVarchar;
use Sunnysideup\DataObjectSorter\DataObjectOneFieldUpdateController;

/**
 * Class \Sunnysideup\DataObjectSorter\Extensions\DataObjectEditAnythingExtension
 *
 * @property DataObjectEditAnythingExtension $owner
 */
class DataObjectEditAnythingExtension extends Extension
{
    private static $included_field_types_for_quick_edit = [
        // 'Varchar',
        // DBVarchar::class,

        // 'Int',
        // DBInt::class,

        // 'Float',
        // DBFloat::class,

        // 'percentage',
        // DBPercentage::class,

        // 'Boolean',
        // DBBoolean::class,

        // 'Currency',
        // DBCurrency::class,

        // 'Date',
        // DBDate::class,

        // 'Datetime',
        // DBDatetime::class,

        // 'Text',
        // DBText::class,
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
            if (
                (
                    ! empty($excludeFields) &&
                    in_array(
                        true,
                        array_map(
                            fn ($field) => stripos((string) $dbType, (string) $field) === 0,
                            $excludeFields
                        ),
                        true
                    )
                ) ||
                (
                    ! empty($includeFields) &&
                    ! in_array(true, array_map(fn ($field) => stripos((string) $dbType, (string) $field) === 0, $includeFields), true)
                )
            ) {
                continue;
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
                if ($myFormField->hasMethod('getRightTitle') !== true) {
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
