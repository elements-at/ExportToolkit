<?php

class ExportToolkit_AdminController extends \Pimcore\Controller\Action\Admin {

    protected $config;

    public function init() {
        parent::init();
    }

    public function settingsAction() {
        if ($this->getRequest()->isPost()) {
            $configData = array();

            $regex = '/\r\n|[\r\n]/';

            if(!($blacklist = preg_split($regex, $this->getParam("blacklist")))) {
                $blacklist = [];
            }

            if(!($classlist = preg_split($regex, $this->getParam("classlist")))) {
                $classlist = [];
            }

            $config = array(
                'blacklist' => array_filter($blacklist),
                'classlist' => array_filter($classlist),
                'override' => (boolean) $this->getParam("override")
            );

            $configData['classes'] = $config;

            \Pimcore\File::putPhpFile(\ExportToolkit\Helper::getConfigFilePath(), to_php_data_file_format($configData));

            $this->view->saved = true;
        }
        
        $this->view->config = \ExportToolkit\Helper::getPluginConfig()->toArray();
    }

}
