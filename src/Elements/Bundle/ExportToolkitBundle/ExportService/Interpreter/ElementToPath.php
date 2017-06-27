<?php

namespace Elements\Bundle\ExportToolkitBundle\ExportService\Interpreter;

use Elements\Bundle\ExportToolkitBundle\ExportService\IInterpreter;
use Pimcore\Model\Element\ElementInterface;

class ElementToPath implements IInterpreter {

    public static function interpret($value, $config = null) {
        if($value instanceof ElementInterface) {
            return (string) $value;
        }
        return "";
    }

}
