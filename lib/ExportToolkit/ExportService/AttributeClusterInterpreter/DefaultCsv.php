<?php

class ExportToolkit_ExportService_AttributeClusterInterpreter_DefaultCsv extends ExportToolkit_ExportService_AttributeClusterInterpreter_Abstract {

    protected $firstData = true;

    public function __construct($config) {
        parent::__construct($config);
        $this->firstData = true;
    }

    protected function getExportFile() {
        return PIMCORE_DOCUMENT_ROOT . "/" . ($this->config->filename ? $this->config->filename  : "website/var/plugins/ExportToolkit/export.csv");
    }

    protected function doCommitData() {
        if($this->config->filename && $this->data) {
            $fp = fopen($this->getExportFile(), 'a');

            $firstRow = reset($this->data);

            if($this->firstData) {
                fputcsv($fp, array_keys($firstRow));
                $this->firstData = false;
            }



            foreach ($this->data as $row) {
                fputcsv($fp, array_values($row));
            }

            fclose($fp);
        }
        $this->data = array();
    }


    public function commitDataRow(Object_Abstract $object) {
        if(count($this->data) > 500) {
            $this->doCommitData();
        }
    }


    public function commitData() {
        $this->doCommitData();
    }

    public function deleteFromExport(Object_Abstract $object) {
        // nothing to do here
    }

    public function setUpExport() {

        if($this->config->filename && $this->config->deleteFile) {
            @unlink($this->getExportFile());
        }

    }
}