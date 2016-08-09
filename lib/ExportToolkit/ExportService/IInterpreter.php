<?php

namespace ExportToolkit\ExportService;

interface IInterpreter {

    public static function interpret($value, $config = null);

}
