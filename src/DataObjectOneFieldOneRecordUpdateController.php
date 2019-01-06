<?php

namespace Sunnysideup\DataobjectSorter;











use Sunnysideup\DataobjectSorter\Api\DataObjectSorterRequirements;
use SilverStripe\Core\Injector\Injector;
use Sunnysideup\DataobjectSorter\DataObjectOneFieldOneRecordUpdateController;
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
 *@description: allows you to edit one field in one record
 *
 **/

class DataObjectOneFieldOneRecordUpdateController extends DataObjectSortBaseClass
{
    private static $allowed_actions = array(
        "onefieldform" => 'DATA_OBJECT_SORT_AND_EDIT_PERMISSION',
        "show" => 'DATA_OBJECT_SORT_AND_EDIT_PERMISSION',
        "save" => 'DATA_OBJECT_SORT_AND_EDIT_PERMISSION'
    );

    /**
     *
     * make sure to also change in routes if you change this link
     * @var string
     */
    private static $url_segment = 'dataobjectonefieldonerecordupdate';

    /**
     * get a link
     * @param  string $ClassName
     * @param  string $FieldName
     * @param  string $recordID
     *
     * @return string
     */
    public static function popup_link_only($ClassName, $FieldName, $recordID)
    {
        DataObjectSorterRequirements::popup_link_requirements();
        return Injector::inst()->get(DataObjectOneFieldOneRecordUpdateController::class)
            ->Link('show/'.$ClassName."/".$FieldName).'?id='.$recordID;
    }

    /**
     * get a link
     * @param  string $ClassName
     * @param  string $FieldName
     * @param  string $recordID
     * @param  string $linkText
     * @return string
     */
    public static function popup_link($ClassName, $FieldName, $recordID, $linkText = 'click here to edit')
    {
        if ($link = self::popup_link_only($ClassName, $FieldName, $recordID)) {
            return '
                <a href="'.$link.'" class="modalPopUp modal-popup" data-width="800" data-height="600" data-rel="window.open(\''.$link.'\', \'sortlistFor'.$ClassName.$FieldName.$recordID.'\',\'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=600,height=600,left = 440,top = 200\'); return false;">'.$linkText.'</a>';
        }
    }

    public function init()
    {
        //must set this first ...
        Config::inst()->update(SSViewer::class, 'theme_enabled', Config::inst()->get(DataObjectSorterRequirements::class, 'run_through_theme'));
        // Only administrators can run this method
        parent::init();
        DataObjectSorterRequirements::popup_requirements('onefieldonerecord');
        $url = Director::absoluteURL(
            Injector::inst()->get(DataObjectOneFieldOneRecordUpdateController::class)->Link('updatefield')
        );
        Requirements::customScript(
            "var DataObjectOneFieldOneRecordUpdateURL = '".$url."'",
            'DataObjectOneFieldOneRecordUpdateURL'
        );
    }

    public function onefieldform()
    {
        Versioned::set_reading_mode('Stage.Stage');
        $table = $this->SecureTableToBeUpdated();
        $field = $this->SecureFieldToBeUpdated();
        $record = $this->SecureRecordToBeUpdated();
        $obj = $table::get()->byID($record);
        if (! $obj) {
            user_error("record could not be found!", E_USER_ERROR);
        }
        if (! $obj->canEdit()) {
            return $this->permissionFailureStandard();
        }
        $FormField = $this->getFormField($obj, $field);
        if (!$FormField) {
            user_error("Form Field could not be Found", E_USER_ERROR);
        }
        $FormField->setValue($obj->$field);
        $form = new Form(
            $controller = $this,
            $name = "OneFieldForm",
            $fields = new FieldList(
                $FormField,
                new HiddenField("Table", "Table", $table),
                new HiddenField("Field", "Field", $field),
                new HiddenField("Record", "Record", $record)
            ),
            $actions = new FieldList(new FormAction("save", "save and close"))
        );

        return $form;
    }

    public function save($data, $form)
    {
        $table = $this->SecureTableToBeUpdated();
        $field = $this->SecureFieldToBeUpdated();
        $record = $this->SecureRecordToBeUpdated();
        $obj = $table::get()->byID($record);
        if (! $obj->canEdit()) {
            return $this->permissionFailureStandard();
        }
        $obj->$field = $data[$field];
        $obj->write();
        return '
            <p>Your changes have been saved, please <a href="#" onclick="self.close(); return false;">close window</a>.</p>
            <script type="text/javascript">self.close();</script>';
    }
}
