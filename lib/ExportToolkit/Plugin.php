<?php

namespace ExportToolkit;

use Pimcore\File;

class Plugin extends \Pimcore\API\Plugin\AbstractPlugin implements \Pimcore\API\Plugin\PluginInterface {

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
            $this->exportService = new ExportService();
        }
        return $this->exportService;
    }


    public static function install (){
        // create backend permission
        \Pimcore\Model\User\Permission\Definition::create("plugin_exporttoolkit_config");

        // create default config if non exists yet
        if(!file_exists(Helper::getConfigFilePath())) {
            $defaultConfig = include(PIMCORE_PLUGINS_PATH . "/ExportToolkit/install/example-config.php");

            File::putPhpFile(Helper::getConfigFilePath(), to_php_data_file_format($defaultConfig));
        }

        return true;
    }

    public static function uninstall (){
        // implement your own logic here
        return true;
    }

    public static function isInstalled () {
        return file_exists(Helper::getConfigFilePath());
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
