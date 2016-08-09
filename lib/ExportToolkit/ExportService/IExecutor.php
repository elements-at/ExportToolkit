<?php

namespace ExportToolkit\ExportService;

interface IExecutor {

    public static function execute($workerName, $options = array());

    public static function getCli($workerName);

}
