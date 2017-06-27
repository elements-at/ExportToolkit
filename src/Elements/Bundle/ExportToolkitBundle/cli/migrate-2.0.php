<?php

$workingDirectory = getcwd();

$realPath = realpath(dirname(__FILE__) . "/../../../../../pimcore/config/");
include_once($realPath . "/startup.php");

chdir($workingDirectory);

ini_set('max_execution_time', 0);
ini_set("memory_limit", "-1");

define('EXTK_OLD_CONFIG', PIMCORE_WEBSITE_PATH . '/var/plugins/ExportToolkit/config.xml');
define('EXTK_OLD_CONFIG_FOLDER', PIMCORE_WEBSITE_PATH . "/var/plugins/ExportToolkit/configs");

if(file_exists(EXTK_OLD_CONFIG)) {
    try {
        $config = new Zend_Config_Xml(EXTK_OLD_CONFIG, 'configData');
    } catch(Exception $e) {
        $config = new Zend_Config([]);
    }

    $config = $config->toArray();

    if(isset($config["classes"])) {
        $regex = '/\r\n|[\r\n]/';

        if (!empty($config["classes"]["blacklist"])) {
            $config["classes"]["blacklist"] = preg_split($regex, $config["classes"]["blacklist"]);
            array_filter($config["classes"]["blacklist"]);
        } else {
            $config["classes"]["blacklist"] = [];
        }

        if (!empty($config["classes"]["classlist"])) {
            $config["classes"]["classlist"] = preg_split($regex, $config["classes"]["classlist"]);
            array_filter($config["classes"]["classlist"]);
        } else {
            $config["classes"]["classlist"] = [];
        }

        $config["classes"]["override"] = (boolean) $config["classes"]["override"];
    }

    \Pimcore\File::putPhpFile(\Elements\Bundle\ExportToolkitBundle\Helper::getConfigFilePath(), to_php_data_file_format($config));
}

if(file_exists(EXTK_OLD_CONFIG_FOLDER)) {
    $files = scandir(EXTK_OLD_CONFIG_FOLDER);

    foreach ($files as $file) {
        if (preg_match('/exportconfig_[a-zA-Z0-9\-\_]+/', $file)) {
            $fileData = file_get_contents(EXTK_OLD_CONFIG_FOLDER . "/" . $file);

            $data = json_decode($fileData, true);
            $model = new \Elements\Bundle\ExportToolkitBundle\Configuration(null, $data["general"]["name"], $data);

            $model->save();
        }
    }
}
