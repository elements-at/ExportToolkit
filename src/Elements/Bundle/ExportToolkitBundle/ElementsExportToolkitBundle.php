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

    public function postAddObject($object) {
        try {
            $this->getExportService()->setUpExport(true);
            $this->getExportService()->updateExport($object, true);
            $this->getExportService()->commitData(true);
        } catch (\Exception $e) {
            Logger::error($e);
        }

    }

    public function postUpdateObject($object) {
        try {
            $this->getExportService()->setUpExport(true);
            $this->getExportService()->updateExport($object, true);
            $this->getExportService()->commitData(true);
        } catch (\Exception $e) {
            Logger::error($e);
        }
    }


    public function postDeleteObject($object) {
        try {
            $this->getExportService()->setUpExport(true, "delete");
            $this->getExportService()->deleteFromExport($object, true);
            $this->getExportService()->commitData(true, "delete");
        } catch (\Exception $e) {
            Logger::error($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAdminIframePath()
    {
        return "/admin/elementsexporttoolkit/admin/settings";
    }

}
