<?php

namespace Elements\Bundle\ExportToolkitBundle\ExportService\Interpreter;

use Elements\Bundle\ExportToolkitBundle\ExportService\IInterpreter;

class ArrayToString implements IInterpreter {

    public static function interpret($value, $config = null) {
        if(is_array($value)) {
            $delimiter = $config && $config->delimiter ? $config->delimiter : ",";
            $value = implode($delimiter, $value);
        }
        return $value;
    }

}
