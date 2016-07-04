<?php

class ExportToolkit_Plugin  extends Pimcore_API_Plugin_Abstract implements Pimcore_API_Plugin_Interface {

    protected $exportService;

    public function init(){
        parent::init();
        \Pimcore::getEventManager()->attach('system.console.init', function (\Zend_EventManager_Event $e) {
            $application = $e->getTarget();
            $application->add(new \ExportToolkit\Console\Command\ExportCommand());
        });
    }
    public function __construct($jsPaths = null, $cssPaths = null) {
        parent::__construct($jsPaths, $cssPaths);
    }

    private function getExportService() {
        if(empty($this->exportService)) {
            $this->exportService = new ExportToolkit_ExportService();
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
        } catch (Exception $e) {
            Logger::error($e);
        }

    }

    public function postUpdateObject($object) {
        try {
            $this->getExportService()->setUpExport(true);
            $this->getExportService()->updateExport($object, true);
            $this->getExportService()->commitData(true);
        } catch (Exception $e) {
            Logger::error($e);
        }
    }


    public function postDeleteObject($object) {
        try {
            $this->getExportService()->setUpExport(true, "delete");
            $this->getExportService()->deleteFromExport($object, true);
            $this->getExportService()->commitData(true, "delete");
        } catch (Exception $e) {
            Logger::error($e);
        }
    }


    public static function getTranslationFile($language){
        return "/ExportToolkit/texts/en.csv";
    }


}
