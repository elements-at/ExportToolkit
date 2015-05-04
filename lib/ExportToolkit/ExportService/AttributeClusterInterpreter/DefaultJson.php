<?php

class ExportToolkit_ExportService_AttributeClusterInterpreter_DefaultJson extends ExportToolkit_ExportService_AttributeClusterInterpreter_Abstract {

    protected $firstData = true;

    public function __construct($config) {
        parent::__construct($config);
        $this->firstData = true;
    }

    protected function getExportFile() {
        return PIMCORE_DOCUMENT_ROOT . "/" . ($this->config->filename ? $this->config->filename  : "website/var/plugins/ExportToolkit/export.json");
    }


    public function commitDataRow(Object_Abstract $object) {
    }


    public function commitData() {
        file_put_contents($this->getExportFile(), json_encode(array_values($this->data), JSON_PRETTY_PRINT));
    }

    public function deleteFromExport(Object_Abstract $object) {
        // nothing to do here
    }

    public function setUpExport() {
        //nothing to do here
    }

}