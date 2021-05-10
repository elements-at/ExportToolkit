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

namespace Elements\Bundle\ExportToolkitBundle;

use Pimcore\Config;
use Symfony\Component\Lock\Key;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;

class Helper
{

    private static array $_pluginConfig = [];

    public static function getConfigFilePath() :string
    {
        return Config::locateConfigFile('ExportToolkit/config.php');
    }


    public static function getPluginConfig() :array
    {
        if (self::$_pluginConfig) {
            return self::$_pluginConfig;
        }

        $file = self::getConfigFilePath();

        if (file_exists($file)) {
            $config = include($file);
        } else {
            $config = [];
        }

        self::$_pluginConfig = $config;

        return self::$_pluginConfig;
    }

    public static function getLock(LockFactory $lockFactory, string $workername): LockInterface
    {
        $lockkey = new Key('exporttoolkit_'.$workername);
        return $lockFactory->createLock($lockkey, 3 * 60 * 60);
    }

}
