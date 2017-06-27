<?php

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
        return Config::locateConfigFile("ExportToolkit/config.php");
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

        if(file_exists($file)) {
            $config = include($file);
        } else {
            $config = [];
        }

        self::$_pluginConfig = $config;

        return self::$_pluginConfig;


    }
}
