<?php

interface ExportToolkit_ExportService_IExecutor {

    public static function execute($workerName, $options = array());

    public static function getCli($workerName);

}
