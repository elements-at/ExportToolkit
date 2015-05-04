<?php

class ExportToolkit_Configuration extends Pimcore_Model_Abstract {


    public $name;
    public $configuration;


    public function __construct($name = null, $configuration = null) {
        $this->name = $name;
        $this->configuration = $configuration;
    }


    /**
     * @param mixed $configuration
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return mixed
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }



    public function save() {
        if(empty($this->configuration)) {
            $this->configuration = new stdClass();
            $this->configuration->general = new stdClass();
        }
        $this->configuration->general->name = $this->name;
        $this->getResource()->save();
    }

    public function delete() {
        $this->getResource()->delete();
    }

    /**
     * @param $name
     * @return ExportToolkit_Configuration
     */
    public static function getByName($name) {
        return ExportToolkit_Configuration_Resource::getByName($name);
    }

    public static function getList() {
        return ExportToolkit_Configuration_Resource::getList();
    }

}