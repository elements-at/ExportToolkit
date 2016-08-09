<?php

namespace ExportToolkit\ExportService\Interpreter;

use ExportToolkit\ExportService\IInterpreter;
use Pimcore\Tool;
use Pimcore\Translate\Admin;
use Pimcore\Translate\Website;

class Translations implements IInterpreter {

    public static function interpret($value, $config = null) {

        if((string)$config->translator == "website") {
            $translate = new Website("en");
        } else if((string)$config->translator == "admin") {
            $translate = new Admin("en");
        } else {
            $translate = null;
        }

        $data = array();

        $languages = Tool::getValidLanguages();
        foreach($languages as $l) {
            if($translate) {
                if(is_array($value)) {
                    $values = array();
                    foreach($value as $v) {
                        $values[] = $translate->translate($v, $l);
                    }
                    $data[$l] = $values;
                } else {
                    $data[$l] = $translate->translate($value, $l);
                }
            } else {
                $data[$l] = $value;
            }
        }

        return $data;
    }

}
