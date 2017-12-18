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

namespace ExportToolkit\ExportService\Interpreter;

use ExportToolkit\ExportService\IInterpreter;
use Pimcore\Tool;
use Pimcore\Translate\Admin;
use Pimcore\Translate\Website;

class Translations implements IInterpreter
{
    public static function interpret($value, $config = null)
    {
        if ((string)$config->translator == 'website') {
            $translate = new Website('en');
        } elseif ((string)$config->translator == 'admin') {
            $translate = new Admin('en');
        } else {
            $translate = null;
        }

        $data = [];

        $languages = Tool::getValidLanguages();
        foreach ($languages as $l) {
            if ($translate) {
                if (is_array($value)) {
                    $values = [];
                    foreach ($value as $v) {
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
