<?php

namespace Sunnysideup\DataobjectSorter\Api;

use SilverStripe\Core\Config\Config;
use Sunnysideup\DataobjectSorter\Api\DataObjectSorterRequirements;
use SilverStripe\View\SSViewer;
use SilverStripe\View\Requirements;
use SilverStripe\View\ViewableData;

class DataObjectSorterRequirements extends ViewableData
{

    /**
     * set to TRUE to add your own Requirements
     *
     * @var boolean
     */
    private static $popup_link_requirements_have_been_added = false;

    public static function get_popup_link_requirements_have_been_added()
    {
        return self::$popup_link_requirements_have_been_added;
    }

    public static function set_popup_link_requirements_have_been_added($bool)
    {
        self::$popup_link_requirements_have_been_added = $bool;

        return true;
    }
    /**
     * set to TRUE to add your own Requirements
     *
     * @var boolean
     */
    private static $popup_requirements_have_been_added = false;

    public static function get_popup_requirements_have_been_added()
    {
        return self::$popup_requirements_have_been_added;
    }

    public static function set_popup_requirements_have_been_added($bool)
    {
        self::$popup_link_requirements_have_been_added = $bool;

        return true;
    }

    private static $run_through_theme = false;

    public static function popup_link_requirements()
    {
        $done = self::get_popup_link_requirements_have_been_added();
        $isCMS = Config::inst()->get(SSViewer::class, 'theme_enabled') ? false : true;
        if ($done || $isCMS) {
            //do nothing
        } else {
            self::set_popup_link_requirements_have_been_added(true);
            // Requirements::javascript('silverstripe/admin: thirdparty/jquery/jquery.js');
            Requirements::javascript('sunnysideup/dataobjectsorter: client/javascript/jquery.simplemodal-1.4.4.js');
            Requirements::javascript('sunnysideup/dataobjectsorter: client/javascript/modalpopup.js');
            Requirements::themedCSS('client/css/modalpopup');
        }
    }

    /**
     *
     *
     * @param  string $type - one of the following: onefieldonerecord, onefield, onerecord, sorter
     * @return [type]       [description]
     */
    public static function popup_requirements($type)
    {
        if (! self::get_popup_requirements_have_been_added()) {
            self::set_popup_requirements_have_been_added(true);
            Requirements::javascript('silverstripe/admin: thirdparty/jquery/jquery.js');
            Requirements::themedCSS('client/css/sorter');
            $type = strtolower($type);
            switch ($type) {
                case "onefieldonerecord":
                    Requirements::themedCSS('client/css/onefieldonerecord');
                    Requirements::javascript('sunnysideup/dataobjectsorter: client/javascript/onefieldonerecord.js');
                    break;
                case "onefield":
                    Requirements::themedCSS('client/css/onefield');
                    Requirements::javascript('sunnysideup/dataobjectsorter: client/javascript/onefield.js');
                    break;
                case "onerecord":
                    Requirements::themedCSS('client/css/onerecord');
                    Requirements::javascript('sunnysideup/dataobjectsorter: client/javascript/onerecord.js');
                    break;
                case "sorter":
                    Requirements::javascript('sunnysideup/dataobjectsorter: client/javascript/jquery-ui-1.9.1.custom.min.js');
                    Requirements::javascript('sunnysideup/dataobjectsorter: client/javascript/sorter.js');
                    break;
                default:
                    user_error("type $type is not a valid option");
            }
        }
    }
}
