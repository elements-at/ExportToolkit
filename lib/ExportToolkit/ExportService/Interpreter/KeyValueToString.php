<?php
/**
 * Created by PhpStorm.
 * User: tmittendorfer
 * Date: 10.04.2015
 * Time: 10:56
 */

class ExportToolkit_ExportService_Interpreter_KeyValueToString implements ExportToolkit_ExportService_IInterpreter {

    public static function interpret($value, $config = null)
    {
        $result = "";

        if($value instanceof Object_Data_KeyValue) {

            foreach($value->getProperties() as $prop) {
                $result.="|id=".$prop['id']."&key=".$prop['key']."&value=".$prop['value']."";
            }

            if($result != "") {
                $result = substr($result, 1);
            }

        }
        return $result;
    }
}