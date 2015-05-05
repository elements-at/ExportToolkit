<?php

class ExportToolkit_ExportService_Filter_Default implements ExportToolkit_ExportService_IFilter {

    public function doExport($object, $config = null) {

        if($object instanceof Object_Abstract) {
            return true;
        }

        return false;

    }


}