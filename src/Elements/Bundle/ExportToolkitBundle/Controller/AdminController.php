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

namespace Elements\Bundle\ExportToolkitBundle\Controller;

use Elements\Bundle\ExportToolkitBundle\Helper;
use Pimcore\Controller\Configuration\TemplatePhp;
use Pimcore\Templating\Model\ViewModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/elementsexporttoolkit/admin")
 */
class AdminController extends \Pimcore\Bundle\AdminBundle\Controller\AdminController
{
    protected $config;

    /**
     * @Route("/settings")
     * @TemplatePhp()
     *
     * @param Request $request
     *
     * @return ViewModel
     */
    public function settingsAction(Request $request)
    {
        $viewData = [];

        if ($request->isMethod('POST')) {
            $configData = [];

            $regex = '/\r\n|[\r\n]/';

            if (!($blacklist = preg_split($regex, $request->get('blacklist')))) {
                $blacklist = [];
            }

            if (!($classlist = preg_split($regex, $request->get('classlist')))) {
                $classlist = [];
            }

            $config = [
                'blacklist' => array_filter($blacklist),
                'classlist' => array_filter($classlist),
                'override' => (bool) $request->get('override')
            ];

            $configData['classes'] = $config;

            \Pimcore\File::putPhpFile(Helper::getConfigFilePath(), to_php_data_file_format($configData));

            $viewData['saved'] = true;
        }

        $viewData['config'] = Helper::getPluginConfig();
        $viewModel = new ViewModel($viewData);

        return $viewModel;
    }
}
