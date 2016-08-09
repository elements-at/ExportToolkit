<?php

namespace ExportToolkit;

class Plugin extends \Pimcore\API\Plugin\AbstractPlugin implements \Pimcore\API\Plugin\PluginInterface {

    protected $exportService;

    public function __construct($jsPaths = null, $cssPaths = null) {
        parent::__construct($jsPaths, $cssPaths);
    }

    private function getExportService() {
        if(empty($this->exportService)) {
            $this->exportService = new ExportService();
        }
        return $this->exportService;
    }


    public static function install (){
        // implement your own logic here
        return true;
    }

    public static function uninstall (){
        // implement your own logic here
        return true;
    }

    public static function isInstalled () {
        // implement your own logic here
        return true;
    }


    public function postAddObject($object) {
        try {
            $this->getExportService()->setUpExport(true);
            $this->getExportService()->updateExport($object, true);
            $this->getExportService()->commitData(true);
        } catch (\Exception $e) {
            \Logger::error($e);
        }

    }

    public function postUpdateObject($object) {
        try {
            $this->getExportService()->setUpExport(true);
            $this->getExportService()->updateExport($object, true);
            $this->getExportService()->commitData(true);
        } catch (\Exception $e) {
            \Logger::error($e);
        }
    }


    public function postDeleteObject($object) {
        try {
            $this->getExportService()->setUpExport(true, "delete");
            $this->getExportService()->deleteFromExport($object, true);
            $this->getExportService()->commitData(true, "delete");
        } catch (\Exception $e) {
            \Logger::error($e);
        }
    }


    public static function getTranslationFile($language){
        return "/ExportToolkit/texts/en.csv";
    }


}
