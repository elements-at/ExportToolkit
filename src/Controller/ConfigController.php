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

use Elements\Bundle\ExportToolkitBundle\Configuration;
use Elements\Bundle\ExportToolkitBundle\Configuration\Dao;
use Elements\Bundle\ExportToolkitBundle\ExportService\IExecutor;
use Elements\Bundle\ExportToolkitBundle\Helper;
use Pimcore\Cache;
use Pimcore\Bundle\AdminBundle\Controller\AdminAbstractController;
use Pimcore\Logger;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/elementsexporttoolkit/config')]
class ConfigController extends AdminAbstractController
{

    public function upgradeAction()
    {
        Helper::upgrade();
        exit();
    }

    private function buildFolder($path, $name)
    {
        return [
            'id' => $path,
            'text' => $name,
            'type' => 'folder',
            'expanded' => true,
            'iconCls' => 'pimcore_icon_folder',
            'children' => [],
        ];
    }

    private function buildItem($configuration)
    {
        return [
            'id' => $configuration->getName(),
            'text' => $configuration->getName(),
            'type' => 'config',
            'iconCls' => 'pimcore_icon_custom_views',
            'expandable' => false,
            'leaf' => true,
        ];
    }

    #[Route('/list')]
    public function listAction(Request $request):JsonResponse
    {
        $folders = Dao::getFolders();
        $list = Dao::getList();

        $tree = [];
        $folderStructure = [];

        // build a temporary 1 dimensional folder structure
        foreach ($folders as $folder) {
            $folderStructure[$folder['path']] = $this->buildFolder($folder['path'], $folder['name']);

            // root folders, keep a pointer to 1 dimensional array
            // to minimize memory and actually make the nesting work
            if (empty($folder['parent'])) {
                $tree[] = & $folderStructure[$folder['path']];
            }
        }

        // start nesting folders
        foreach ($folders as $folder) {
            $parent = $folder['parent'];
            $path = $folder['path'];

            if (!empty($parent) && !empty($folderStructure[$parent])) {
                $folderStructure[$parent]['children'][] = & $folderStructure[$path];
            }
        }

        // add configurations to their corresponding folder
        foreach ($list as $configuration) {
            $config = $this->buildItem($configuration);

            if (!$configuration->getPath()) {
                $tree[] = $config;
            } else {
                if (!empty($folderStructure[$configuration->getPath()])) {
                    $folderStructure[$configuration->getPath()]['children'][] = $config;
                }
            }
        }

        return $this->adminJson($tree);
    }

    #[Route('/delete')]
    public function deleteAction(Request $request) :JsonResponse
    {
        try {
            $name = $request->get('name');

            $config = Dao::getByName($name);
            if (empty($config)) {
                throw new Exception('Name does not exist.');
            }

            $config->delete();

            return $this->adminJson(['success' => true]);
        } catch (Exception $e) {
            return $this->adminJson(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * @Route("/add-folder")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function addFolderAction(Request $request)
    {
        $parent = $request->get('parent');
        $name = $request->get('name');

        try {
            if (!$name) {
                throw new \Exception('Invalid name.');
            }

            Dao::addFolder($parent, $name);

            return $this->adminJson(['success' => true]);
        } catch (Exception $exception) {
            return $this->adminJson(['success' => false, 'message' => $exception->getMessage()]);
        }
    }

    /**
     * @Route("/delete-folder")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function deleteFolderAction(Request $request)
    {
        $path = $request->get('path');

        if (Dao::getFolderByPath($path)) {
            Dao::deleteFolder($path);

            return $this->adminJson(['success' => true]);
        } else {
            return $this->adminJson(['success' => false]);
        }
    }

    /**
     * @Route("/move")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function moveAction(Request $request)
    {
        $who = $request->get('who');
        $to = $request->get('to');

        Dao::moveConfiguration($who, $to);

        return new JsonResponse();
    }

    /**
     * @Route("/move-folder")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function moveFolderAction(Request $request)
    {
        $who = $request->get('who');
        $to = $request->get('to');

        Dao::moveFolder($who, $to);

        return new JsonResponse();
    }

    #[Route('/add')]
    public function addAction(Request $request): JsonResponse
    {
        try {
            $path = $request->get('path');
            $name = $request->get('name');

            $config = Dao::getByName($name);

            if (!empty($config)) {
                throw new Exception('Name already exists.');
            }

            $config = new Configuration($path, $name);
            $config->save();

            return $this->adminJson(['success' => true, 'name' => $name]);
        } catch (Exception $e) {
            return $this->adminJson(['success' => false, 'message' => $e->getMessage()]);
        }
    }


    #[Route('/clone')]
    public function cloneAction(Request $request): JsonResponse
    {
        try {
            $name = $request->get('name');

            $config = Dao::getByName($name);
            if (!empty($config)) {
                throw new Exception('Name already exists.');
            }

            $originalName = $request->get('originalName');
            $originalConfig = Dao::getByName($originalName);
            if (!$originalConfig) {
                throw new Exception('Configuration not found');
            }

            $originalConfig->setName($name);
            $originalConfig->save($name);

            return $this->adminJson(['success' => true, 'name' => $name]);
        } catch (Exception $e) {
            return $this->adminJson(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    #[Route('/get')]
    public function getAction(Request $request): JsonResponse
    {
        $name = $request->get('name');

        $configuration = Dao::getByName($name);
        if (empty($configuration)) {
            throw new Exception('Name does not exist.');
        }

        if ($configuration && !empty($configuration->configuration->general->executor)) {
            /** @var $className IExecutor */
            $className = $configuration->configuration->general->executor;
            $cli = $className::getCli($name, null);
        } else {
            $cli = $this->getCliCommand($configuration->getName());
        }

        return $this->adminJson(
            [
                'name' => $configuration->getName(),
                'execute' => $cli,
                'configuration' => $configuration->getConfiguration(),
            ]
        );
    }

    #[Route('/save')]
    public function saveAction(Request $request): JsonResponse
    {
        try {
            $data = $request->get('data');
            $dataDecoded = json_decode($data, true);

            $name = $dataDecoded['general']['name'];
            $config = Dao::getByName($name);
            $config->setConfiguration($dataDecoded);
            $config->save();

            return $this->adminJson(['success' => true]);
        } catch (Exception $e) {
            return $this->adminJson(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function loadClasses(): array|false
    {
        $config = Helper::getPluginConfig();

        if ($config['classes']['override']) {
            $classes = [];
            $classlist = $config['classes']['classlist'];
            if (!empty($classlist)) {
                foreach ($classlist as $line) {
                    if ($line) {
                        $classes[] = $line;
                    }
                }
            }
        } else {
            $classes = get_declared_classes();
        }

        $whiteListedClasses = $classes;
        $blackListedClasses = [
            '/^Whoops/i',
            '/^Zend_/i',
            '/^Sabre_/i',
            '/^PEAR_/i',
            '/^VersionControl_/i',
            '/^Google_/i',
            '/^PHPExcel_/i',
        ];

        $additionalBlacklisted = $config['classes']['blacklist'];
        if (!empty($additionalBlacklisted)) {
            foreach ($additionalBlacklisted as $line) {
                if ($line) {
                    $blackListedClasses[] = $line;
                }
            }
        }
        foreach ($blackListedClasses as $blackList) {
            $whiteListedClasses = preg_grep($blackList, $whiteListedClasses, PREG_GREP_INVERT);
        }

        return $whiteListedClasses;
    }

    #[Route('/get-classes')]
    public function getClassesAction(Request $request): JsonResponse
    {
        $classes = $this->loadClasses();

        if ($request->get('type') == 'attribute-cluster-interpreter') {
            $implementsIConfig = [];
            foreach ($classes as $class) {
                try {
                    $reflect = new \ReflectionClass($class);
                    if (is_subclass_of(
                            $class,
                            '\\Elements\\Bundle\\ExportToolkitBundle\\ExportService\\AttributeClusterInterpreter\\AbstractAttributeClusterInterpreter'
                        ) && $reflect->isInstantiable()
                    ) {
                        $implementsIConfig[] = [$class];
                    }
                } catch (Exception $e) {
                }
            }

            return $this->adminJson($implementsIConfig);
        } else {
            if ($request->get('type') == 'export-filter') {
                $implementsIConfig = [];
                foreach ($classes as $class) {
                    try {
                        $reflect = new \ReflectionClass($class);
                        if ($reflect->implementsInterface(
                                '\\Elements\\Bundle\\ExportToolkitBundle\\ExportService\\IFilter'
                            ) && $reflect->isInstantiable()
                        ) {
                            $implementsIConfig[] = [$class];
                        }
                    } catch (Exception $e) {
                    }
                }

                return $this->adminJson($implementsIConfig);
            } else {
                if ($request->get('type') == 'export-conditionmodificator') {
                    $implementsIConfig = [];
                    foreach ($classes as $class) {
                        try {
                            $reflect = new \ReflectionClass($class);
                            if ($reflect->implementsInterface(
                                    '\\Elements\\Bundle\\ExportToolkitBundle\\ExportService\\IConditionModificator'
                                )
                                ||
                                $reflect->implementsInterface(
                                    '\\Elements\\Bundle\\ExportToolkitBundle\\ExportService\\IListModificator'
                                )
                            ) {
                                $implementsIConfig[] = [$class];
                            }
                        } catch (Exception $e) {
                        }
                    }

                    return $this->adminJson($implementsIConfig);
                } else {
                    if ($request->get('type') == 'attribute-getter') {
                        $implementsIConfig = [];
                        foreach ($classes as $class) {
                            try {
                                $reflect = new \ReflectionClass($class);
                                if ($reflect->implementsInterface(
                                        '\\Elements\\Bundle\\ExportToolkitBundle\\ExportService\\IGetter'
                                    ) && $reflect->isInstantiable()
                                ) {
                                    $implementsIConfig[] = [$class];
                                }
                            } catch (Exception $e) {
                            }
                        }

                        return $this->adminJson($implementsIConfig);
                    } else {
                        if ($request->get('type') == 'attribute-interpreter') {
                            $implementsIConfig = [];
                            foreach ($classes as $class) {
                                try {
                                    $reflect = new \ReflectionClass($class);
                                    if ($reflect->implementsInterface(
                                            '\\Elements\\Bundle\\ExportToolkitBundle\\ExportService\\IInterpreter'
                                        ) && $reflect->isInstantiable()
                                    ) {
                                        $implementsIConfig[] = [$class];
                                    }
                                } catch (Exception $e) {
                                }
                            }

                            return $this->adminJson($implementsIConfig);
                        } else {
                            throw new Exception('unknown class type');
                        }
                    }
                }
            }
        }
    }

    #[Route('/clear-cache')]
    public function clearCacheAction(): Response
    {
        Cache::clearTag('exporttoolkit');

        return new Response();
    }

    protected function getCliCommand($configName)
    {
        return \Pimcore\Tool\Console::getPhpCli(
            ).' '.PIMCORE_PROJECT_ROOT.'/bin/console export-toolkit:export --config-name="'.$configName.'"';
    }

    /**
     * @Route("/execute-export")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function executeExportAction(Request $request,  LockFactory $lockFactory)
    {
        $workername = $request->get('name');
        $config = Dao::getByName($workername);

        if ($config && $config->configuration->general->executor) {
            /** @var $className IExecutor */
            $className = $config->configuration->general->executor;
            try {
                $className::execute($workername, null);
            } catch (Exception $e) {
                return $this->adminJson(['success' => false, 'message' => $e->getMessage()]);
            }
        } else {

            if ($this->isProcessRunning($lockFactory,$workername)) {
                return $this->adminJson(['success' => false]);
            }

            $cmd = $this->getCliCommand($workername);
            Logger::info($cmd);
            \Pimcore\Tool\Console::execInBackground(
                $cmd,
                PIMCORE_LOG_DIRECTORY.DIRECTORY_SEPARATOR.'exporttoolkit-output.log'
            );
        }

        return $this->adminJson(['success' => true]);
    }

    #[Route('/is-export-running')]
    public function isExportRunningAction(Request $request, LockFactory $lockFactory) :JsonResponse
    {
        return $this->adminJson(['success' => true, 'locked' => $this->isProcessRunning($lockFactory,$request->get('name'))]);

    }

    protected function isProcessRunning(LockFactory $lockFactory, string $workername): bool
    {
        $lock = Helper::getLock($lockFactory, $workername);
        $acquired = $lock->acquire();
        $lock->release();

        return !$acquired;
    }
}
