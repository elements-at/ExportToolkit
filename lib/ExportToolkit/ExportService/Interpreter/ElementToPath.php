<?php


class ExportToolkit_ExportService_Interpreter_ElementToPath implements ExportToolkit_ExportService_IInterpreter {

    public static function interpret($value, $config = null) {
        if($value instanceof Element_Interface) {
            return (string) $value;
        }
        return ""; 
    }

}
