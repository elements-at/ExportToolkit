<?php

namespace Elements\Bundle\ExportToolkitBundle\ExportService;

interface IFilter {

    public function doExport($object, $config = null);

}
