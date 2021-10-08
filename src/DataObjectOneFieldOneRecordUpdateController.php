<?php

namespace Sunnysideup\DataobjectSorter;

use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Versioned\Versioned;
use SilverStripe\View\Requirements;
use SilverStripe\View\SSViewer;
use Sunnysideup\DataobjectSorter\Api\DataObjectSorterRequirements;

/**
 *@author nicolaas [at] sunnysideup.co.nz
 *@package: dataobjectsorter
 *@description: allows you to edit one field in one record
 */
class DataObjectOneFieldOneRecordUpdateController extends DataObjectSortBaseClass
{
    private static $allowed_actions = [
        'onefieldform' => 'DATA_OBJECT_SORT_AND_EDIT_PERMISSION',
        'show' => 'DATA_OBJECT_SORT_AND_EDIT_PERMISSION',
        'save' => 'DATA_OBJECT_SORT_AND_EDIT_PERMISSION',
    ];

    /**
     * make sure to also change in routes if you change this link.
     *
     * @var string
     */
    private static $url_segment = 'dataobjectonefieldonerecordupdate';

    /**
     * get a link.
     *
     * @param string $className
     * @param string $fieldName
     * @param string $recordID
     */
    public static function popup_link_only($className, $fieldName, $recordID): string
    {
        DataObjectSorterRequirements::popup_link_requirements();
        $className = self::classNameToString($className);

        return Injector::inst()->get(DataObjectOneFieldOneRecordUpdateController::class)
            ->Link('show/' . $className . '/' . $fieldName) . '?id=' . $recordID;
    }

    /**
     * get a link.
     *
     * @param string $className
     * @param string $fieldName
     * @param string $recordID
     * @param string $linkText
     */
    public static function popup_link($className, $fieldName, $recordID, $linkText = 'click here to edit'): string
    {
        $link = self::popup_link_only($className, $fieldName, $recordID);
        if ('' !== $link) {
            return '
                <a href="' . $link . '" class="modalPopUp modal-popup" data-width="800" data-height="600" data-rel="window.open(\'' . $link . "', 'sortlistFor" . $className . $fieldName . $recordID . '\',\'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=600,height=600,left = 440,top = 200\'); return false;">' . $linkText . '</a>';
        }

        return '';
    }

    public function onefieldform()
    {
        Versioned::set_reading_mode('Stage.Stage');
        $className = $this->SecureClassNameToBeUpdated();
        $field = $this->SecureFieldToBeUpdated();
        $recordId = $this->SecureRecordIdToBeUpdated();
        $obj = $className::get()->byID($recordId);
        if (! $obj) {
            user_error('record could not be found!', E_USER_ERROR);
        }
        if (! $obj->canEdit()) {
            return $this->permissionFailureStandard();
        }
        $FormField = $this->getFormField($obj, $field);
        $FormField->setValue($obj->{$field});

        return new Form(
            $controller = $this,
            $name = 'OneFieldForm',
            $fields = new FieldList(
                $FormField,
                new HiddenField('Table', 'Table', self::classNameToString($className)),
                new HiddenField('Field', 'Field', $field),
                new HiddenField('Record', 'Record', $recordId)
            ),
            $actions = new FieldList(new FormAction('save', 'save and close'))
        );
    }

    public function save($data, $form)
    {
        $className = $this->SecureClassNameToBeUpdated();
        $field = $this->SecureFieldToBeUpdated();
        $recordId = $this->SecureRecordIdToBeUpdated();
        $obj = $className::get()->byID($recordId);
        if (! $obj->canEdit()) {
            return $this->permissionFailureStandard();
        }
        $obj->{$field} = $data[$field];
        $obj->write();

        return '
            <p>Your changes have been saved, please <a href="#" onclick="self.close(); return false;">close window</a>.</p>
            <script type="text/javascript">self.close();</script>';
    }

    protected function init()
    {
        //must set this first ...
        Config::modify()->update(SSViewer::class, 'theme_enabled', Config::inst()->get(DataObjectSorterRequirements::class, 'run_through_theme'));
        // Only administrators can run this method
        parent::init();
        DataObjectSorterRequirements::popup_requirements('onefieldonerecord');
        $url = Director::absoluteURL(
            Injector::inst()->get(DataObjectOneFieldOneRecordUpdateController::class)->Link('updatefield')
        );
        Requirements::customScript(
            "var DataObjectOneFieldOneRecordUpdateURL = '" . $url . "'",
            'DataObjectOneFieldOneRecordUpdateURL'
        );
    }
}
