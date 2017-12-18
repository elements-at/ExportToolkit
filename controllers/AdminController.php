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

class ExportToolkit_AdminController extends \Pimcore\Controller\Action\Admin
{
    protected $config;

    public function init()
    {
        parent::init();
    }

    public function settingsAction()
    {
        if ($this->getRequest()->isPost()) {
            $configData = [];

            $regex = '/\r\n|[\r\n]/';

            if (!($blacklist = preg_split($regex, $this->getParam('blacklist')))) {
                $blacklist = [];
            }

            if (!($classlist = preg_split($regex, $this->getParam('classlist')))) {
                $classlist = [];
            }

            $config = [
                'blacklist' => array_filter($blacklist),
                'classlist' => array_filter($classlist),
                'override' => (bool) $this->getParam('override')
            ];

            $configData['classes'] = $config;

            \Pimcore\File::putPhpFile(\ExportToolkit\Helper::getConfigFilePath(), to_php_data_file_format($configData));

            $this->view->saved = true;
        }

        $this->view->config = \ExportToolkit\Helper::getPluginConfig()->toArray();
    }
}
