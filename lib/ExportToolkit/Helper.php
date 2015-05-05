<?php

class ExportToolkit_Helper {

    protected static $_pluginConfig;

    public static function getConfigFilePath()
    {
        $path = PIMCORE_WEBSITE_PATH . '/var/plugins/ExportToolkit/';
        if (!is_dir($path)) {
            mkdir($path);
        }

        return $path . 'config.xml';
    }


    public static function getPluginConfig()
    {
        $pluginFile = self::getConfigFilePath();

        if (is_null(self::$_pluginConfig)) {
            try {
                self::$_pluginConfig = new Zend_Config_Xml($pluginFile, 'configData');
            } catch (Exception $e) {
                return new Zend_Config(array());
            }
        }
        return self::$_pluginConfig;
    }

}