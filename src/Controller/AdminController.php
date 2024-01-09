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
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace Elements\Bundle\ExportToolkitBundle\Controller;

use Elements\Bundle\ExportToolkitBundle\Helper;
use Pimcore\Bundle\AdminBundle\Security\CsrfProtectionHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Pimcore\Controller\UserAwareController;

#[Route('/admin/elementsexporttoolkit/admin')]
class AdminController extends UserAwareController
{

    #[Route('/settings', methods: ['GET'], name: 'elementsexporttoolkit-admin-settings')]
    public function settingsAction(
        CsrfProtectionHandler $csrfProtectionHandler
    ): Response {

        $config = Helper::getPluginConfig();
        $viewData = [
            'csrfToken' => $csrfProtectionHandler->getCsrfToken(),
            'config' => [
                'classOverride' => $config['classes']['override'] ?? false,
                'classList' => isset($config['classes']['classlist']) ? implode(
                    "\r\n",
                    $config['classes']['classlist']
                ) : '',
                'blackList' => isset($config['classes']['blacklist']) ? implode(
                    "\r\n",
                    $config['classes']['blacklist']
                ) : '',
            ],
        ];

        return $this->render('@ElementsExportToolkit/Admin/settings.html.twig', $viewData);
    }

    #[Route('/settings', methods: ['POST'], name: 'elementsexporttoolkit-admin-update-settings')]
    public function updateSettings(
        Request $request
    ) {

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
            'override' => (bool)$request->get('override'),
        ];

        $configData['classes'] = $config;

        \Pimcore\File::putPhpFile(Helper::getConfigFilePath(), to_php_data_file_format($configData));

        return $this->redirectToRoute('elementsexporttoolkit-admin-settings');
    }
}
