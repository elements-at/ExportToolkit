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
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

ini_set('max_execution_time', 0);
ini_set('memory_limit', '-1');

$workingDirectory = getcwd();

$realPath = realpath(dirname(__FILE__) . '/../../../../../pimcore/config/');
include_once($realPath . '/startup.php');

chdir($workingDirectory);

$lockkey = 'exporttoolkit_' . $argv[1];
\Pimcore\Model\Tool\Lock::acquire($lockkey);

$service = new \Elements\Bundle\ExportToolkitBundle\ExportService();
$service->executeExport($argv[1]);

\Pimcore\Model\Tool\Lock::release($lockkey);

echo "done\n";
