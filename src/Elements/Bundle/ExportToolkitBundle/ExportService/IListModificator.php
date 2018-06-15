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

namespace Elements\Bundle\ExportToolkitBundle\ExportService;

use Pimcore\Model\DataObject\Listing;

interface IListModificator
{
    /**
     * Modify list, e.g. add joins which can be used in condition
     *
     * @param $configName
     * @param Listing $list
     *
     * @return $this
     */
    public static function modifyList($configName, Listing $list);
}
