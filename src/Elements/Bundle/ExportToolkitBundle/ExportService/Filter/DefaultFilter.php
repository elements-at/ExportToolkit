<?php

namespace Elements\Bundle\ExportToolkitBundle\ExportService\Filter;

use Elements\Bundle\ExportToolkitBundle\ExportService\IFilter;
use Pimcore\Model\Object\AbstractObject;

class DefaultFilter implements IFilter {

    public function doExport($object, $config = null) {

        if($object instanceof AbstractObject) {
            return true;
        }

        return false;

    }


}
