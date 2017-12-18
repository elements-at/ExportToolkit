<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) elements.at New Media Solutions GmbH (http://www.elements.at)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace ExportToolkit;

use Pimcore\Config;
use Pimcore\File;

class Helper
{
    /**
     * @var \Zend_Config $_pluginConfig
     */
    private static $_pluginConfig;

    /**
     * get the path to the plugin configuration file.
     *
     * @return string
     *  path to config
     */
    public static function getConfigFilePath()
    {
        return Config::locateConfigFile('export-toolkit.php');
    }

    /**
     * get the plugin configuration.
     *
     * @return \Zend_Config
     */
    public static function getPluginConfig()
    {
        if (self::$_pluginConfig) {
            return self::$_pluginConfig;
        }

        $file = self::getConfigFilePath();

        if (file_exists($file)) {
            self::$_pluginConfig = new \Zend_Config(include($file));
        } else {
            self::$_pluginConfig = new \Zend_Config([]);
        }

        return self::$_pluginConfig;
    }
}
