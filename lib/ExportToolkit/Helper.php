<?php

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
        return Config::locateConfigFile("export-toolkit.php");
    }

    /**
     * get the plugin configuration.
     *
     * @return \Zend_Config
     */
    public static function getPluginConfig()
    {
        if (self::$_pluginConfig) return self::$_pluginConfig;

        $file = self::getConfigFilePath();

        if (file_exists($file)) {
            self::$_pluginConfig = new \Zend_Config(include($file));
        } else {
            self::$_pluginConfig = new \Zend_Config([]);
        }

        return self::$_pluginConfig;
    }
}