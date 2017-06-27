<?php

namespace Elements\Bundle\ExportToolkitBundle\ExportService\AttributeClusterInterpreter;

use Pimcore\Model\Object\AbstractObject;
use Elements\Bundle\ExportToolkitBundle\SimpleXMLExtended;

class DefaultXml extends AbstractAttributeClusterInterpreter
{

    protected $firstData = true;

    public function __construct($config)
    {
        parent::__construct($config);

        $this->firstData = true;
    }

    protected function getExportFile()
    {
        $file = $this->config->filename ? PIMCORE_PROJECT_ROOT . "/" .$this->config->filename : PIMCORE_SYSTEM_TEMP_DIRECTORY."/ExportToolkit/export.xml";
        $dir = dirname($file);
        if (!is_dir($dir)) {
            \Pimcore\File::mkdir($dir, null, true);
        }

        return $file;
    }


    public function commitDataRow(AbstractObject $object)
    {
    }


    public function commitData()
    {
        $xml = $this->createXml($this->data);
        file_put_contents($this->getExportFile(), $xml);
        die("committed");
    }

    public function deleteFromExport(AbstractObject $object)
    {
        // nothing to do here
    }

    public function setUpExport()
    {
        // nothing to do here
    }

    public function createXml($data, $xml = null)
    {
        $first = $xml;
        if ($xml === null) {
            $xml = new SimpleXMLExtended('<'.($this->config->rootElement ?: 'root').'/>');
        }

        foreach ($data as $k => $v) {


            if (is_array($v)) {
                $rowName = ($this->config->rowElementName ?: 'row');

                //for nested fields
                if (!is_numeric($k)) {
                    $rowName = $k;
                }

                //if childs need neseted rowElementNames they can be defined in the config
                if ($this->config->{'rowElementName'.ucfirst($xml->getName())}) {
                    $rowName = $this->config->{'rowElementName'.ucfirst($xml->getName())};
                }

                $child = $xml->addChild($rowName);
                $this->createXml($v, $child);
            } else {
                $sData = (string)$v;
                $child = @$xml->addChild($k, $sData);
                //need a cdata block
                if ((string)$child != $sData) {
                    $xml->{$k} = null;
                    $xml->{$k}->addCData((string)$v);
                }
            }
        }

        return ($first === null) ? $xml->asXML() : $xml;
    }
}
