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
use \Pimcore\Extension\Bundle\PimcoreBundleAdminClassicInterface;
use Pimcore\Routing\RouteReferenceInterface;
use Pimcore\Extension\Bundle\Traits\BundleAdminClassicTrait;
use Pimcore\HttpKernel\Bundle\DependentBundleInterface;
use Pimcore\Bundle\AdminBundle\PimcoreAdminBundle;
use Pimcore\HttpKernel\BundleCollection\BundleCollection;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;

class ElementsExportToolkitBundle extends AbstractPimcoreBundle implements PimcoreBundleAdminClassicInterface, DependentBundleInterface
{
    use PackageVersionTrait;
    use BundleAdminClassicTrait;

    const BUNDLE_NAME = 'ElementsExportToolkitBundle';

    public function getCssPaths() :array
    {
        return [
            '/bundles/elementsexporttoolkit/css/example.css'
        ];
    }

    public function getJsPaths() :array
    {
        return [
            '/bundles/elementsexporttoolkit/js/startup.js',
            '/bundles/elementsexporttoolkit/js/Plugin.js',
            '/bundles/elementsexporttoolkit/js/config/configpanel.js',
            '/bundles/elementsexporttoolkit/js/config/item.js',
            '/bundles/elementsexporttoolkit/js/config/attributeconfig.js'
        ];
    }

    public function getEditmodeJsPaths(): array
    {
        return [];
    }


    public function getEditmodeCssPaths(): array
    {
        return [];
    }

    /**
     * If the bundle has an installation routine, an installer is responsible of handling installation related tasks
     *
     * @return InstallerInterface|null
     */
    public function getInstaller(): ?InstallerInterface
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


    protected function getComposerPackageName(): string
    {
        return 'elements/export-toolkit-bundle';
    }

    public function getNiceName(): string
    {
        return self::BUNDLE_NAME;
    }

    public static function registerDependentBundles(BundleCollection $collection): void
    {
        $collection->addBundle(new PimcoreAdminBundle(), 60);
    }
}
