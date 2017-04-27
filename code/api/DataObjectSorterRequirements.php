<?php


class DataObjectSorterRequirements extends Object
{

    /**
     * set to TRUE to add your own Requirements
     *
     * @var boolean
     */
    private static $popup_link_requirements_have_been_added = false;

    /**
     * set to TRUE to add your own Requirements
     *
     * @var boolean
     */
    private static $popup_requirements_have_been_added = false;
    /**
     * set to TRUE to add your own Requirements
     *
     * @var boolean
     */
    private static $run_through_theme = false;

    public static function popup_link_requirements()
    {
        $done = Config::inst()->get('DataObjectSorterRequirements', 'popup_link_requirements_have_been_added');
        $isCMS = Config::inst()->get('SSViewer', 'theme_enabled') ? false : true;
        if ($done || $isCMS) {
            //do nothing
        } else {
            Config::inst()->update('DataObjectSorterRequirements', 'popup_link_requirements_have_been_added', true);
            Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
            Requirements::javascript('dataobjectsorter/javascript/jquery.simplemodal-1.4.4.js');
            Requirements::javascript('dataobjectsorter/javascript/modalpopup.js');
            Requirements::themedCSS('modalpopup', 'dataobjectsorter');
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
        if (! Config::inst()->get('DataObjectSorterRequirements', 'popup_requirements_have_been_added')) {
            Config::inst()->update('DataObjectSorterRequirements', 'popup_requirements_have_been_added', true);
            Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
            Requirements::themedCSS('sorter', 'dataobjectsorter');
            $type = strtolower($type);
            switch ($type) {
                case "onefieldonerecord":
                    Requirements::themedCSS('onefieldonerecord', 'dataobjectsorter');
                    Requirements::javascript('dataobjectsorter/javascript/onefieldonerecord.js');
                    break;
                case "onefield":
                    Requirements::themedCSS('onefield', 'dataobjectsorter');
                    Requirements::javascript('dataobjectsorter/javascript/onefield.js');
                    break;
                case "onerecord":
                    Requirements::themedCSS('onerecord', 'dataobjectsorter');
                    Requirements::javascript('dataobjectsorter/javascript/onerecord.js');
                    break;
                case "sorter":
                    Requirements::javascript('dataobjectsorter/javascript/jquery-ui-1.9.1.custom.min.js');
                    Requirements::javascript('dataobjectsorter/javascript/sorter.js');
                    break;
                default:
                    user_error("type $type is not a valid option");
            }
        }
    }
}
