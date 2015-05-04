<?php

class ExportToolkit_ExportService_Getter_DefaultBrickGetterSequence implements ExportToolkit_ExportService_IGetter {

    public static function get($object, $config = null) {
        $sourceList = $config->source;

        foreach($sourceList as $source) {
            $brickContainerGetter = "get" . ucfirst($source->brickfield);

            if(method_exists($object, $brickContainerGetter)) {
                $brickContainer = $object->$brickContainerGetter();

                $brickGetter = "get" . ucfirst($source->bricktype);
                $brick = $brickContainer->$brickGetter();
                if($brick) {
                    $fieldGetter = "get" . ucfirst($source->fieldname);
                    $value = $brick->$fieldGetter();
                    if($value) {

                        return $value;
                    }
                }
            }
        }
    }
}
