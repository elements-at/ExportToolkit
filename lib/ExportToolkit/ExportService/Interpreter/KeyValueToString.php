<?php
/**
 * Created by PhpStorm.
 * User: tmittendorfer
 * Date: 10.04.2015
 * Time: 10:56
 */

namespace ExportToolkit\ExportService\Interpreter;

use ExportToolkit\ExportService\IInterpreter;
use Pimcore\Model\Object\Data\KeyValue;

class KeyValueToString implements IInterpreter {

    public static function interpret($value, $config = null)
    {
        $result = "";

        if($value instanceof KeyValue) {

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