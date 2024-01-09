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

use Pimcore\Extension\Bundle\Installer\AbstractInstaller;
use Pimcore\File;

class Installer extends AbstractInstaller
{
    /**
     * {@inheritdoc}
     */
    public function isInstalled(): bool
    {
        return file_exists(Helper::getConfigFilePath());
    }

    public function needsReloadAfterInstall(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function canBeInstalled(): bool
    {
        return !$this->isInstalled();
    }

    /**
     * {@inheritdoc}
     */
    public function install(): void
    {
        // create backend permission
        \Pimcore\Model\User\Permission\Definition::create('plugin_exporttoolkit_config');

        // create default config if non exists yet
        if (!file_exists(Helper::getConfigFilePath())) {
            $defaultConfigFile = dirname(__FILE__).'/install/example-config.php';
            $defaultConfig = include($defaultConfigFile);

            File::putPhpFile(Helper::getConfigFilePath(), to_php_data_file_format($defaultConfig));
        }

        parent::install();
    }
}
