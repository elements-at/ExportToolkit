<?php

namespace ExportToolkit\ExportService\Interpreter;

use ExportToolkit\ExportService\IInterpreter;
use Pimcore\Model\Element\ElementInterface;

class ElementToPath implements IInterpreter {

    public static function interpret($value, $config = null) {
        if($value instanceof ElementInterface) {
            return (string) $value;
        }
        return ""; 
    }

}
