<?php


class ExportToolkit_ExportService_Interpreter_Translations implements ExportToolkit_ExportService_IInterpreter {

    public static function interpret($value, $config = null) {

        if((string)$config->translator == "website") {
            $translate = new Pimcore_Translate_Website("en");
        } else if((string)$config->translator == "admin") {
            $translate = new Pimcore_Translate_Admin("en");
        } else {
            $translate = null;
        }

        $data = array();

        $languages = Pimcore_Tool::getValidLanguages();
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
