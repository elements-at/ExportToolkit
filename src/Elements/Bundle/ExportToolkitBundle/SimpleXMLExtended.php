<?php

namespace Elements\Bundle\ExportToolkitBundle;

class SimpleXMLExtended extends \SimpleXMLElement
{

    /**
     * @param $cdata_text
     */
    public function addCData($cdata_text)
    {
        $node = dom_import_simplexml($this);
        $no   = $node->ownerDocument;
        $node->appendChild($no->createCDATASection($cdata_text));
    }
}
