<?php

abstract class ExportToolkit_ExportService_AttributeClusterInterpreter_Abstract {

    protected $data;

    public function __construct($config) {
        $this->data = array();
        $this->config = $config;
    }

    /**
     * This method is executed before the export is launched.
     * For example it can be used to clean up old export files, start a database transaction, etc.
     * If not needed, just leave the method empty.
     *
     */
    public abstract function setUpExport();

    /**
     * used internally to set data to the data array
     *
     * @param Object_Abstract $object
     * @param $key
     * @param $value
     */
    public function setData(Object_Abstract $object, $key, $value) {
        $rowData = $this->data[$object->getId()];
        $rowData[$key] = $value;
        $this->data[$object->getId()] = $rowData;
    }

    /**
     * This method is executed after all defined attributes of an object are exported.
     * The to-export data is stored in the array $this->data[OBJECT_ID].
     * For example it can be used to write each exported row to a destination database,
     * write the exported entries to a file, etc.
     * If not needed, just leave the method empty.
     *
     * @param Object_Abstract $object
     */
    public abstract function commitDataRow(Object_Abstract $object);

    /**
     * This method is executed after all objects are exported.
     * If not cleaned up in the commitDataRow-method, all exported data is stored in the array $this->data.
     * For example it can be used to write all data to a xml file or commit a database transaction, etc.
     *
     */
    public abstract function commitData();

    /**
     * This method is executed of an object is not exported (anymore).
     * For example it can be used to remove the entries from a destination database, etc.
     *
     * @param Object_Abstract $object
     */
    public abstract function deleteFromExport(Object_Abstract $object);


    /** Override point
     * @param Object_Abstract $object
     * @return bool return true if interpreter wants to consume the object
     */
    public function isRelevant(Object_Abstract $object) {
        return true;
    }

}