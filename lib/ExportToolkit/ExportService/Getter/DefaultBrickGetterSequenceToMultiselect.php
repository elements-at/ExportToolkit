<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) elements.at New Media Solutions GmbH (http://www.elements.at)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace ExportToolkit\ExportService\Getter;

use ExportToolkit\ExportService\IGetter;

class DefaultBrickGetterSequenceToMultiselect implements IGetter
{
    public static function get($object, $config = null)
    {
        $sourceList = $config->source;

        $values = [];

        foreach ($sourceList as $source) {
            $brickContainerGetter = 'get' . ucfirst($source->brickfield);

            if (method_exists($object, $brickContainerGetter)) {
                $brickContainer = $object->$brickContainerGetter();

                $brickGetter = 'get' . ucfirst($source->bricktype);
                $brick = $brickContainer->$brickGetter();
                if ($brick) {
                    $fieldGetter = 'get' . ucfirst($source->fieldname);
                    $value = $brick->$fieldGetter();

                    if ($source->invert == 'true') {
                        $value = !$value;
                    }

                    if ($value) {
                        if (is_bool($value) || $source->forceBool == 'true') {
                            $values[] = $source->fieldname;
                        } else {
                            $values[] = $value;
                        }
                    }
                }
            } else {
                $fieldGetter = 'get' . ucfirst($source->fieldname);
                if (method_exists($object, $fieldGetter)) {
                    $value = $object->$fieldGetter();

                    if ($source->invert == 'true') {
                        $value = !$value;
                    }

                    if ($value) {
                        if (is_bool($value) || $source->forceBool == 'true') {
                            $values[] = $source->fieldname;
                        } else {
                            $values[] = $value;
                        }
                    }
                }
            }
        }
        if (!empty($values)) {
            return implode(', ', $values);
        } else {
            return null;
        }
    }
}
