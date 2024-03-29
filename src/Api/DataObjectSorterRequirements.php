<?php

namespace Sunnysideup\DataObjectSorter\Api;

use SilverStripe\Admin\LeftAndMain;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\View\Requirements;
use SilverStripe\View\SSViewer;

class DataObjectSorterRequirements
{
    use Configurable;
    use Extensible;
    use Injectable;

    /**
     * set to TRUE to add your own Requirements.
     *
     * @var bool
     */
    private static $popup_link_requirements_have_been_added = false;

    /**
     * set to TRUE to add your own Requirements.
     *
     * @var bool
     */
    private static $popup_requirements_have_been_added = false;

    private static $run_through_theme = false;

    public static function get_popup_link_requirements_have_been_added()
    {
        return self::$popup_link_requirements_have_been_added;
    }

    public static function set_popup_link_requirements_have_been_added($bool)
    {
        self::$popup_link_requirements_have_been_added = $bool;

        return true;
    }

    public static function get_popup_requirements_have_been_added()
    {
        return self::$popup_requirements_have_been_added;
    }

    public static function set_popup_requirements_have_been_added($bool)
    {
        self::$popup_link_requirements_have_been_added = $bool;

        return true;
    }

    public static function popup_link_requirements()
    {
        $done = self::get_popup_link_requirements_have_been_added();
        $isCMS = ! (bool) Config::inst()->get(SSViewer::class, 'theme_enabled') ||
            Controller::curr() instanceof LeftAndMain;
        if ($done || $isCMS) {
            //do nothing
        } else {
            self::set_popup_link_requirements_have_been_added(true);
            Requirements::javascript('https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js');
            Requirements::javascript('sunnysideup/dataobjectsorter: client/javascript/jquery.simplemodal-1.4.4.js');
            Requirements::javascript('sunnysideup/dataobjectsorter: client/javascript/modalpopup.js');
            Requirements::themedCSS('client/css/modalpopup');
        }
    }

    /**
     * @param string $type - one of the following: onefieldonerecord, onefield, onerecord, sorter
     */
    public static function popup_requirements(string $type)
    {
        if (! self::get_popup_requirements_have_been_added()) {
            self::set_popup_requirements_have_been_added(true);
            Requirements::javascript('https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js');
            Requirements::themedCSS('client/css/sorter');
            $type = strtolower($type);
            switch ($type) {
                case 'onefieldonerecord':
                    Requirements::themedCSS('client/css/onefieldonerecord');
                    Requirements::javascript('sunnysideup/dataobjectsorter: client/javascript/onefieldonerecord.js');

                    break;
                case 'onefield':
                    Requirements::themedCSS('client/css/onefield');
                    Requirements::javascript('sunnysideup/dataobjectsorter: client/javascript/onefield.js');

                    break;
                case 'onerecord':
                    Requirements::themedCSS('client/css/onerecord');
                    Requirements::javascript('sunnysideup/dataobjectsorter: client/javascript/onerecord.js');

                    break;
                case 'sorter':
                    Requirements::javascript('sunnysideup/dataobjectsorter: client/javascript/jquery-ui-1.9.1.custom.min.js');
                    Requirements::javascript('sunnysideup/dataobjectsorter: client/javascript/sorter.js');

                    break;
                default:
                    user_error("type {$type} is not a valid option");
            }
        }
    }

    public static function url_variable(string $className, string $varName, ?string $action = 'updatefield')
    {
        $url = Director::absoluteURL(
            Injector::inst()->get($className)
                ->Link($action)
        );
        Requirements::customScript(
            'var ' . $varName . " = '" . rtrim($url, '/').'/' . "'",
            $varName . 'URL'
        );
    }

    public static function theme_fix(?string $className = '')
    {
        if (! $className) {
            $className = DataObjectSorterRequirements::class;
        }

        Config::modify()->set(
            SSViewer::class,
            'theme_enabled',
            Config::inst()->get($className, 'run_through_theme') ? true : false
        );
    }
}
