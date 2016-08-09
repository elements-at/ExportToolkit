<?php

namespace ExportToolkit\ExportService\Interpreter;

use ExportToolkit\ExportService\IInterpreter;

class ArrayToString implements IInterpreter {

    public static function interpret($value, $config = null) {
        if(is_array($value)) {
            $delimiter = $config && $config->delimiter ? $config->delimiter : ",";
            $value = implode($delimiter, $value);
        }
        return $value;
    }

}
