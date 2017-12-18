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

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Installer\InstallerInterface;

class ElementsExportToolkitBundle extends AbstractPimcoreBundle
{
    /**
     * @return array
     */
    public function getCssPaths()
    {
        return [
            '/bundles/elementsexporttoolkit/css/example.css'
        ];
    }

    /**
     * @return array
     */
    public function getJsPaths()
    {
        return [
            '/bundles/elementsexporttoolkit/js/exporttoolkit/startup.js',
            '/bundles/elementsexporttoolkit/js/exporttoolkit/Plugin.js',
            '/bundles/elementsexporttoolkit/js/exporttoolkit/config/configpanel.js',
            '/bundles/elementsexporttoolkit/js/exporttoolkit/config/item.js',
            '/bundles/elementsexporttoolkit/js/exporttoolkit/config/attributeconfig.js'
        ];
    }

    /**
     * If the bundle has an installation routine, an installer is responsible of handling installation related tasks
     *
     * @return InstallerInterface|null
     */
    public function getInstaller()
    {
        return new Installer();
    }

    /**
     * {@inheritdoc}
     */
    public function getAdminIframePath()
    {
        return '/admin/elementsexporttoolkit/admin/settings';
    }
}
