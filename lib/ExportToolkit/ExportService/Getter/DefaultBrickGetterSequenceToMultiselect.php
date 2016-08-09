<?php

namespace ExportToolkit\ExportService\Getter;

use ExportToolkit\ExportService\IGetter;

class DefaultBrickGetterSequenceToMultiselect implements IGetter {

    public static function get($object, $config = null) {
        $sourceList = $config->source;

        $values = array();

        foreach($sourceList as $source) {
            $brickContainerGetter = "get" . ucfirst($source->brickfield);

            if(method_exists($object, $brickContainerGetter)) {
                $brickContainer = $object->$brickContainerGetter();

                $brickGetter = "get" . ucfirst($source->bricktype);
                $brick = $brickContainer->$brickGetter();
                if($brick) {
                    $fieldGetter = "get" . ucfirst($source->fieldname);
                    $value = $brick->$fieldGetter();

                    if($source->invert == "true") {
                        $value = !$value;
                    }

                    if($value) {
                        if(is_bool($value) || $source->forceBool == "true") {
                            $values[] = $source->fieldname;
                        } else {
                            $values[] = $value;
                        }
                    }
                }
            } else {
                $fieldGetter = "get" . ucfirst($source->fieldname);
                if(method_exists($object, $fieldGetter)) {
                    $value = $object->$fieldGetter();

                    if($source->invert == "true") {
                        $value = !$value;
                    }

                    if($value) {
                        if(is_bool($value) || $source->forceBool == "true") {
                            $values[] = $source->fieldname;
                        } else {
                            $values[] = $value;
                        }
                    }
                }
            }

        }
        if(!empty($values)) {
            return implode(", ", $values);
        } else {
            return null;
        }


    }
}
