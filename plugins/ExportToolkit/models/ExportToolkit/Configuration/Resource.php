<?php

class ExportToolkit_Configuration_Resource extends Pimcore_Model_Resource_Abstract {

    public function save() {
        Pimcore_Model_Cache::clearTag("exporttoolkit");
        $name = $this->model->getName();
        file_put_contents(self::getFolder() . "/exportconfig_" . Pimcore_File::getValidFilename($name) . ".json", json_encode($this->model->getConfiguration(), JSON_PRETTY_PRINT));
    }

    public function delete() {
        Pimcore_Model_Cache::clearTag("exporttoolkit");
        $name = $this->model->getName();
        unlink(self::getFolder() . "/exportconfig_" . Pimcore_File::getValidFilename($name) . ".json");
    }

    public static function getList() {

        if(!$configurations = Pimcore_Model_Cache::load("exporttoolkit_configurations")) {

            $files = scandir(self::getFolder());
            $configurations = array();

            foreach($files as $file) {
                if(substr($file, 0, 13) == "exportconfig_") {
                    $model = self::loadFile($file);
                    $configurations[$model->getName()] = $model;
                }
            }
            Pimcore_Model_Cache::save($configurations, "exporttoolkit_configurations", array("exporttoolkit"), 9999);
        }

        return $configurations;
    }

    /**
     * @param $name
     * @return ExportToolkit_Configuration
     */
    public static function getByName($name) {
        $configurations = self::getList();
        return $configurations[$name];
    }




    public static function loadFile($filename) {
        $fileData = file_get_contents(self::getFolder() . "/" . $filename);
        $data = json_decode($fileData);
        return new ExportToolkit_Configuration($data->general->name, $data);
    }

    private static function getFolder() {
        $folder = PIMCORE_WEBSITE_PATH . "/var/plugins/ExportToolkit/configs";
        if(!is_dir($folder)) {
            mkdir($folder, 0755, true);
        }
        return $folder;
    }

}