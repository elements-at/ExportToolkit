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

namespace ExportToolkit\ExportService\Filter;

use ExportToolkit\ExportService\IFilter;
use Pimcore\Model\Object\AbstractObject;

class DefaultFilter implements IFilter
{
    public function doExport($object, $config = null)
    {
        if ($object instanceof AbstractObject) {
            return true;
        }

        return false;
    }
}
