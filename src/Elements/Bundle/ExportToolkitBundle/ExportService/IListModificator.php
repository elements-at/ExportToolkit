<?php

namespace Elements\Bundle\ExportToolkitBundle\ExportService;

use Pimcore\Model\Object\Listing;

interface IListModificator
{
    /**
     * Modify list, e.g. add joins which can be used in condition
     *
     * @param $configName
     * @param Listing $list
     * @return $this
     */
    public static function modifyList($configName, Listing $list);
}
