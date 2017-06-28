<?php

namespace Elements\Bundle\ExportToolkitBundle;


use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Installer\InstallerInterface;
use Pimcore\Logger;

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
        return "/admin/elementsexporttoolkit/admin/settings";
    }

}
