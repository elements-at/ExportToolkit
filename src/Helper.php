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
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace Elements\Bundle\ExportToolkitBundle;

use Pimcore\Config;

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
        return Config::locateConfigFile('ExportToolkit/config.php');
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
            $config = include($file);
        } else {
            $config = [];
        }

        self::$_pluginConfig = $config;

        return self::$_pluginConfig;
    }
}
