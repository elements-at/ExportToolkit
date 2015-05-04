<?php

class ExportToolkit_ExportService_AttributeClusterInterpreter_DefaultXml extends ExportToolkit_ExportService_AttributeClusterInterpreter_Abstract {

    protected $firstData = true;

    public function __construct($config) {
        parent::__construct($config);
        $this->firstData = true;
    }

    protected function getExportFile() {
        return PIMCORE_DOCUMENT_ROOT . "/" . ($this->config->filename ? $this->config->filename  : "website/var/plugins/ExportToolkit/export.xml");
    }


    public function commitDataRow(Object_Abstract $object) {
    }


    public function commitData() {
        $xml = $this->createXml($this->data);
        file_put_contents($this->getExportFile(), $xml);
    }

    public function deleteFromExport(Object_Abstract $object) {
        // nothing to do here
    }

    public function setUpExport() {
        // nothing to do here
    }

    public function createXml($data, $xml = NULL) {
        $first = $xml;
        if($xml === NULL) $xml = new SimpleXMLElement('<' . ($this->config->rootElement ? $this->config->rootElement : 'root') . '/>');
        foreach ($data as $k => $v) {
            is_array($v) ? $this->createXml($v, $xml->addChild(($this->config->rowElementName ? $this->config->rowElementName : 'row'))) : $xml->addChild($k, $v);
        }
        return ($first === NULL) ? $xml->asXML() : $xml;
    }
}