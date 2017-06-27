<?php

namespace Elements\Bundle\ExportToolkitBundle;

use Pimcore\Extension\Bundle\Installer\AbstractInstaller;
use Pimcore\File;

class Installer extends AbstractInstaller
{
    /**
     * {@inheritdoc}
     */
    public function isInstalled()
    {
        return file_exists(Helper::getConfigFilePath());
    }


    public function needsReloadAfterInstall(){
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function canBeInstalled()
    {
        return !$this->isInstalled();

    }

    /**
     * {@inheritdoc}
     */
    public function install()
    {
        // create backend permission
        \Pimcore\Model\User\Permission\Definition::create("plugin_exporttoolkit_config");

        // create default config if non exists yet
        if(!file_exists(Helper::getConfigFilePath())) {
            $defaultConfigFile = dirname(__FILE__).'/install/example-config.php';
            $defaultConfig = include($defaultConfigFile);

            File::putPhpFile(Helper::getConfigFilePath(), to_php_data_file_format($defaultConfig));
        }

        return true;
    }

}
