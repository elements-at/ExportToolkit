<?php

namespace ExportToolkit\ExportService;

interface IFilter {

    public function doExport($object, $config = null);

}