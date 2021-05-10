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
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace Elements\Bundle\ExportToolkitBundle\ExportService\Interpreter;

use Elements\Bundle\ExportToolkitBundle\ExportService\IInterpreter;
use Pimcore\Tool;

class Translations implements IInterpreter
{
    public static function interpret($value, $config = null)
    {
        $translationType = (string)$config->translator;
        $translator = \Pimcore::getContainer()->get('translator');

        $data = [];

        $languages = Tool::getValidLanguages();
        foreach ($languages as $l) {
            if ($translationType == 'admin' || $translationType == 'website') {
                if (is_array($value)) {
                    $values = [];
                    foreach ($value as $v) {
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
