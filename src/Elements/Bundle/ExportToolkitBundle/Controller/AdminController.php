<?php

namespace Elements\Bundle\ExportToolkitBundle\Controller;

use Elements\Bundle\ExportToolkitBundle\Helper;
use Pimcore\Controller\Configuration\TemplatePhp;
use Pimcore\Templating\Model\ViewModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/elementsexporttoolkit/admin")
 */
class AdminController extends \Pimcore\Bundle\AdminBundle\Controller\AdminController {

    protected $config;

    /**
     * @Route("/settings")
     * @TemplatePhp()
     * @param Request $request
     * @return ViewModel
     */
    public function settingsAction(Request $request) {
        $viewData = array();

        if ($request->isMethod("POST")) {
            $configData = [];

            $regex = '/\r\n|[\r\n]/';

            if(!($blacklist = preg_split($regex, $request->get("blacklist")))) {
                $blacklist = [];
            }

            if(!($classlist = preg_split($regex, $request->get("classlist")))) {
                $classlist = [];
            }

            $config = array(
                'blacklist' => array_filter($blacklist),
                'classlist' => array_filter($classlist),
                'override' => (boolean) $request->get("override")
            );

            $configData['classes'] = $config;

            \Pimcore\File::putPhpFile(Helper::getConfigFilePath(), to_php_data_file_format($configData));

            $viewData["saved"] = true;
        }

        $viewData["config"] = Helper::getPluginConfig();
        $viewModel = new ViewModel($viewData);
        return $viewModel;
    }

}
