<?php


class ExportToolkit_ExportService_Interpreter_ArrayToString implements ExportToolkit_ExportService_IInterpreter {

    public static function interpret($value, $config = null) {
        if(is_array($value)) {
            $delimiter = $config && $config->delimiter ? $config->delimiter : ",";
            $value = implode($delimiter, $value);

        }
        return $value;
    }

}
