<?php

namespace Sunnysideup\DataobjectSorter;

use Sunnysideup\DataobjectSorter\Api\DataObjectSorterRequirements;
use SilverStripe\Core\Injector\Injector;
use Sunnysideup\DataobjectSorter\DataObjectOneRecordUpdateController;
use SilverStripe\Core\Config\Config;
use SilverStripe\View\SSViewer;
use SilverStripe\Control\Director;
use SilverStripe\View\Requirements;
use SilverStripe\Versioned\Versioned;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\Form;

/**
 *@author nicolaas [at] sunnysideup.co.nz
 *@package: dataobjectsorter
 *@description: allows you to edit one record
 *
 **/

class DataObjectOneRecordUpdateController extends DataObjectSortBaseClass
{
    private static $allowed_actions = array(
        "onerecordform" => 'DATA_OBJECT_SORT_AND_EDIT_PERMISSION',
        "show" => 'DATA_OBJECT_SORT_AND_EDIT_PERMISSION',
        "save" => 'DATA_OBJECT_SORT_AND_EDIT_PERMISSION'
    );

    /**
     *
     * make sure to also change in routes if you change this link
     * @var string
     */
    private static $url_segment = 'dataobjectonerecordupdate';

    public static function popup_link_only($className, $recordID)
    {
        DataObjectSorterRequirements::popup_link_requirements();
        return Injector::inst()->get(DataObjectOneRecordUpdateController::class)->Link('show/'.$className."/".$recordID);
    }
    public static function popup_link($className, $recordID, $linkText = 'click here to edit')
    {
        $link = DataObjectOneRecordUpdateController::popup_link_only($className, $recordID);
        if ($link) {
            return '
                <a href="'.$link.'" class="modalPopUp modal-popup" data-width="800" data-height="600" data-rel="window.open(\''.$link.'\', \'sortlistFor'.$className.$recordID.'\',\'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=600,height=600,left = 440,top = 200\'); return false;">'.$linkText.'</a>';
        }
    }

    public function init()
    {
        //must set this first.
        Config::inst()->update(SSViewer::class, 'theme_enabled', Config::inst()->get(DataObjectSorterRequirements::class, 'run_through_theme'));
        parent::init();
        if (! Director::is_ajax()) {
            DataObjectSorterRequirements::popup_requirements('onerecord');
            $url = Director::absoluteURL(
                 Injector::inst()->get(DataObjectOneRecordUpdateController::class)->Link('onerecordform')
            );
            Requirements::customScript(
                "var DataObjectOneRecordUpdateURL = '".$url."'",
                'DataObjectOneRecordUpdateURL'
            );
        }
    }

    public function onerecordform()
    {
        Versioned::set_reading_mode('Stage.Stage');
        $table = $this->SecureTableToBeUpdated();
        $record = $this->SecureRecordToBeUpdated();
        $obj = $table::get()->byID($record);
        if (!$obj) {
            user_error("record could not be found!", E_USER_ERROR);
        }
        if (! $obj->canEdit()) {
            $this->permissionFailureStandard();
        }
        $formFields = $this->getFormFields($obj);
        if (!$formFields) {
            user_error("Form Fields could not be Found", E_USER_ERROR);
        }
        $fields = new FieldList(
            new HiddenField("Table", "Table", $table),
            new HiddenField("Record", "Record", $record)
        );
        foreach ($formFields as $f) {
            $fields->push($f);
        }

        $form = new Form(
            $controller = $this,
            $name = "OneRecordForm",
            $fields,
            $actions = new FieldList(new FormAction("save", "save and close"))
        );
        $form->loadDataFrom($obj);
        return $form;
    }

    public function save($data, $form)
    {
        $table = $this->SecureTableToBeUpdated();
        $record = $this->SecureRecordToBeUpdated();
        $obj = $table::get()->byID($record);
        if ($obj->canEdit()) {
            $form->saveInto($obj);
            $obj->write();
            return '
                <p>Your changes have been saved, please <a href="#" onclick="self.close(); return false;">close window</a>.</p>
                <script type="text/javascript">self.close();</script>';
        } else {
            return $this->permissionFailureStandard();
        }
    }

    public function show()
    {
        $table = $this->SecureTableToBeUpdated();
        $record = $this->SecureRecordToBeUpdated();
        $obj = $table::get()->byID($record);
        if ($obj->canEdit()) {
            //..
        } else {
            return $this->permissionFailure();
        }
    }
}
