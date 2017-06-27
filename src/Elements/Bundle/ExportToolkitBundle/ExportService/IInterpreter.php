<?php

namespace Elements\Bundle\ExportToolkitBundle\ExportService;

interface IInterpreter {

    public static function interpret($value, $config = null);

}
