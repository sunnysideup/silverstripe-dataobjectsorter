<?php

namespace Sunnysideup\DataObjectSorter\Extensions;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Core\Extension;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;
use Sunnysideup\DataObjectSorter\DataObjectOneFieldUpdateController;

/**
 *
 */
class DataObjectEditAnythingExtension extends Extension
{

    public function updateCMSFields(FieldList $fields)
    {
        $owner = $this->getOwner();
        $dbFields = array_keys($owner->config()->get('db'));
        foreach ($dbFields as $dbField) {
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
                    DataObjectOneFieldUpdateController::popup_link($owner->ClassName, $dbField, null, null, 'Edit this field for all Entries'),
                ];
                $rightTitleArray = array_filter($rightTitleArray);
                $myFormField->$setMethod(implode('<br />', $rightTitleArray));
            }
        }
    }
}
