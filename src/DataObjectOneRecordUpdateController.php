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
 *@description: allows you to edit one record
 */
class DataObjectOneRecordUpdateController extends DataObjectSortBaseClass
{
    private static $allowed_actions = [
        'onerecordform' => 'DATA_OBJECT_SORT_AND_EDIT_PERMISSION',
        'show' => 'DATA_OBJECT_SORT_AND_EDIT_PERMISSION',
        'save' => 'DATA_OBJECT_SORT_AND_EDIT_PERMISSION',
    ];

    /**
     * make sure to also change in routes if you change this link.
     *
     * @var string
     */
    private static $url_segment = 'dataobjectonerecordupdate';

    public static function popup_link_only($className, $recordID)
    {
        DataObjectSorterRequirements::popup_link_requirements();

        return Injector::inst()->get(DataObjectOneRecordUpdateController::class)->Link('show/' . $className . '/' . $recordID);
    }

    public static function popup_link($className, $recordID, $linkText = 'click here to edit')
    {
        $link = DataObjectOneRecordUpdateController::popup_link_only($className, $recordID);
        if ($link) {
            return '
                <a
                    href="' . $link . '"
                    class="modalPopUp modal-popup"
                    data-width="800"
                    data-height="600"
                    data-rel="window.open(\'' . $link . "', 'sortlistFor" . $className . $recordID . '\',\'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=600,height=600,left = 440,top = 200\'); return false;"
                >' . $linkText . '</a>';
        }
    }

    public function onerecordform()
    {
        Versioned::set_reading_mode('Stage.Stage');
        $className = $this->SecureClassNameToBeUpdated();
        $record = $this->SecureRecordToBeUpdated();
        $obj = $className::get()->byID($record);
        if (! $obj) {
            user_error('record could not be found!', E_USER_ERROR);
        }
        if (! $obj->canEdit()) {
            $this->permissionFailureStandard();
        }
        $formFields = $this->getFormFields($obj);
        if (! $formFields) {
            user_error('Form Fields could not be Found', E_USER_ERROR);
        }
        $fields = new FieldList(
            new HiddenField('Table', 'Table', self::classNameToString($className)),
            new HiddenField('Record', 'Record', $record)
        );
        foreach ($formFields as $f) {
            $fields->push($f);
        }

        $form = new Form(
            $controller = $this,
            $name = 'OneRecordForm',
            $fields,
            $actions = new FieldList(new FormAction('save', 'save and close'))
        );
        $form->loadDataFrom($obj);

        return $form;
    }

    public function save($data, $form)
    {
        $className = $this->SecureClassNameToBeUpdated();
        $record = $this->SecureRecordToBeUpdated();
        $obj = $className::get()->byID($record);
        if ($obj->canEdit()) {
            $form->saveInto($obj);
            $obj->write();

            return '
                <p>Your changes have been saved, please <a href="#" onclick="self.close(); return false;">close window</a>.</p>
                <script type="text/javascript">self.close();</script>';
        }

        return $this->permissionFailureStandard();
    }

    public function show()
    {
        $className = $this->SecureClassNameToBeUpdated();
        $record = $this->SecureRecordToBeUpdated();
        $obj = $className::get()->byID($record);
        if ($obj->canEdit()) {
            //..
        } else {
            return $this->permissionFailure();
        }
    }

    protected function init()
    {
        //must set this first.
        Config::modify()->update(SSViewer::class, 'theme_enabled', Config::inst()->get(DataObjectSorterRequirements::class, 'run_through_theme'));
        parent::init();
        if (! Director::is_ajax()) {
            DataObjectSorterRequirements::popup_requirements('onerecord');
            $url = Director::absoluteURL(
                Injector::inst()->get(DataObjectOneRecordUpdateController::class)->Link('onerecordform')
            );
            Requirements::customScript(
                "var DataObjectOneRecordUpdateURL = '" . $url . "'",
                'DataObjectOneRecordUpdateURL'
            );
        }
    }
}
