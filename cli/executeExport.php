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
 *  @copyright  Copyright (c) elements.at New Media Solutions GmbH (http://www.elements.at)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

ini_set('max_execution_time', 0);
ini_set('memory_limit', '-1');

$workingDirectory = getcwd();
chdir(__DIR__);
include_once('../../../pimcore/cli/startup.php');
chdir($workingDirectory);

$lockkey = 'exporttoolkit_' . $argv[1];
Tool_Lock::acquire($lockkey);

$service = new \ExportToolkit\ExportService();
$service->executeExport($argv[1]);

Tool_Lock::release($lockkey);

echo "done\n";
