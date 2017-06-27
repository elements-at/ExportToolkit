<?php

namespace Elements\Bundle\ExportToolkitBundle\ExportService\Interpreter;

use Elements\Bundle\ExportToolkitBundle\ExportService\IInterpreter;
use Pimcore\Tool;


class Translations implements IInterpreter {

    public static function interpret($value, $config = null) {

        $translationType = (string)$config->translator;
        $translator = \Pimcore::getContainer()->get('translator');

        $data = array();

        $languages = Tool::getValidLanguages();
        foreach($languages as $l) {
            if($translationType == "admin" || $translationType == "website") {
                if(is_array($value)) {
                    $values = array();
                    foreach($value as $v) {
                        $values[] = $translator->trans($v, $l, $translationType);
                    }
                    $data[$l] = $values;
                } else {
                    $data[$l] = $translator->trans($value, $l, $translationType);
                }
            } else {
                $data[$l] = $value;
            }
        }

        return $data;
    }

}
