<?php

class ExportToolkit_AdminController extends Pimcore_Controller_Action_Admin {

    protected $config;

    public function init() {
        parent::init();
    }

    public function settingsAction() {
        if ($this->getRequest()->isPost()) {
            $configData = array();


            $config = array(
                'blacklist' => $this->_getParam('blacklist'),
                'classlist' => $this->getParam("classlist"),
                'override' => $this->getParam("override")
            );

            $configData['classes'] = $config;

            $writer = new Zend_Config_Writer_Xml(array('config' => new Zend_Config(array('configData' => $configData)),
                'filename' => ExportToolkit_Helper::getConfigFilePath()));
            $writer->write();
            $this->view->saved = true;
        }
        $this->view->config = ExportToolkit_Helper::getPluginConfig()->toArray();
    }

}
