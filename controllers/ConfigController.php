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

class ExportToolkit_ConfigController extends \Pimcore\Controller\Action\Admin
{
    public function upgradeAction()
    {
        \ExportToolkit\Helper::upgrade();
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
            'children' => []
        ];
    }

    private function buildItem($configuration)
    {
        if (\Pimcore\Tool\Admin::isExtJS6()) {
            return [
                'id' => $configuration->getName(),
                'text' => $configuration->getName(),
                'type' => 'config',
                'iconCls' => 'pimcore_icon_custom_views',
                'expandable' => false,
                'leaf' => true
            ];
        } else {
            return [
                'id' => $configuration->getName(),
                'text' => $configuration->getName(),
                'type' => 'config'
            ];
        }
    }

    public function listAction()
    {
        $folders = \ExportToolkit\Configuration\Dao::getFolders();
        $list = \ExportToolkit\Configuration\Dao::getList();

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
            } elseif (!empty($folderStructure[$configuration->getPath()])) {
                $folderStructure[$configuration->getPath()]['children'][] = $config;
            }
        }

        $this->_helper->json($tree);
    }

    public function deleteAction()
    {
        try {
            $name = $this->getParam('name');

            $config = \ExportToolkit\Configuration\Dao::getByName($name);
            if (empty($config)) {
                throw new Exception('Name does not exist.');
            }

            $config->delete();

            $this->_helper->json(['success' => true]);
        } catch (Exception $e) {
            $this->_helper->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function addFolderAction()
    {
        $parent = $this->getParam('parent');
        $name = $this->getParam('name');

        try {
            if (!$name) {
                throw new \Exception('Invalid name.');
            }

            \ExportToolkit\Configuration\Dao::addFolder($parent, $name);

            $this->_helper->json(['success' => true]);
        } catch (Exception $exception) {
            $this->_helper->json(['success' => false, 'message' => $exception->getMessage()]);
        }
    }

    public function deleteFolderAction()
    {
        $path = $this->getParam('path');

        if (\ExportToolkit\Configuration\Dao::getFolderByPath($path)) {
            \ExportToolkit\Configuration\Dao::deleteFolder($path);
            $this->_helper->json(['success' => true]);
        } else {
            $this->_helper->json(['success' => false]);
        }
    }

    public function moveAction()
    {
        $who = $this->getParam('who');
        $to = $this->getParam('to');

        \ExportToolkit\Configuration\Dao::moveConfiguration($who, $to);

        exit();
    }

    public function moveFolderAction()
    {
        $who = $this->getParam('who');
        $to = $this->getParam('to');

        \ExportToolkit\Configuration\Dao::moveFolder($who, $to);

        exit();
    }

    public function addAction()
    {
        try {
            $path = $this->getParam('path');
            $name = $this->getParam('name');

            $config = \ExportToolkit\Configuration\Dao::getByName($name);

            if (!empty($config)) {
                throw new Exception('Name already exists.');
            }

            $config = new \ExportToolkit\Configuration($path, $name);
            $config->save();

            $this->_helper->json(['success' => true, 'name' => $name]);
        } catch (Exception $e) {
            $this->_helper->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function cloneAction()
    {
        try {
            $name = $this->getParam('name');

            $config = \ExportToolkit\Configuration\Dao::getByName($name);
            if (!empty($config)) {
                throw new Exception('Name already exists.');
            }

            $originalName = $this->getParam('originalName');
            $originalConfig = \ExportToolkit\Configuration\Dao::getByName($originalName);
            if (!$originalConfig) {
                throw new Exception('Configuration not found');
            }

            $originalConfig->setName($name);
            $originalConfig->save($name);

            $this->_helper->json(['success' => true, 'name' => $name]);
        } catch (Exception $e) {
            $this->_helper->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getAction()
    {
        $name = $this->getParam('name');

        $configuration = \ExportToolkit\Configuration\Dao::getByName($name);
        if (empty($configuration)) {
            throw new Exception('Name does not exist.');
        }

        $cli = Pimcore_Tool_Console::getPhpCli() . ' ' . realpath(PIMCORE_PLUGINS_PATH . DIRECTORY_SEPARATOR . 'ExportToolkit' . DIRECTORY_SEPARATOR . 'cli' . DIRECTORY_SEPARATOR . 'executeExport.php'). ' ' . $configuration->getName();

        if ($configuration && $configuration->configuration->general->executor) {
            /** @var $className \ExportToolkit\ExportService\IExecutor */
            $className = $configuration->configuration->general->executor;
            $cli = $className::getCli($name, null);
        } else {
            $cli = \Pimcore\Tool\Console::getPhpCli() . ' ' . realpath(PIMCORE_PLUGINS_PATH . DIRECTORY_SEPARATOR . 'ExportToolkit' . DIRECTORY_SEPARATOR . 'cli' . DIRECTORY_SEPARATOR . 'executeExport.php'). ' ' . $configuration->getName();
        }

        $this->_helper->json(
            [
                'name' => $configuration->getName(),
                'execute' => $cli,
                'configuration' => $configuration->getConfiguration()
            ]
        );
    }

    public function saveAction()
    {
        try {
            $data = $this->getParam('data');
            $dataDecoded = json_decode($data, true);

            $name = $dataDecoded['general']['name'];
            $config = \ExportToolkit\Configuration\Dao::getByName($name);
            $config->setConfiguration($dataDecoded);
            $config->save();

            $this->_helper->json(['success' => true]);
        } catch (Exception $e) {
            $this->_helper->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function loadClasses()
    {
        $config = \ExportToolkit\Helper::getPluginConfig()->toArray();

        $classmap = PIMCORE_CONFIGURATION_DIRECTORY . '/autoload-classmap.php';

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
            if (file_exists($classmap)) {
                $classMapAutoLoader = new Zend_Loader_ClassMapAutoloader([$classmap]);
                $classes = array_keys($classMapAutoLoader->getAutoloadMap());
            } else {
                $classes = get_declared_classes();
            }
        }

        $whiteListedClasses = $classes;
        $blackListedClasses = ['/^Whoops/i', '/^Zend_/i', '/^Sabre_/i', '/^PEAR_/i', '/^VersionControl_/i', '/^Google_/i', '/^PHPExcel_/i'];

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

    public function getClassesAction()
    {
        $classes = $this->loadClasses();

        if ($this->getParam('type') == 'attribute-cluster-interpreter') {
            $implementsIConfig = [];
            foreach ($classes as $class) {
                try {
                    $reflect = new ReflectionClass($class);
                    if (is_subclass_of($class, '\\ExportToolkit\\ExportService\\AttributeClusterInterpreter\\AbstractAttributeClusterInterpreter') && $reflect->isInstantiable()) {
                        $implementsIConfig[] = [$class];
                    }
                } catch (Exception $e) {
                }
            }

            $this->_helper->json($implementsIConfig);
        } elseif ($this->getParam('type') == 'export-filter') {
            $implementsIConfig = [];
            foreach ($classes as $class) {
                try {
                    $reflect = new ReflectionClass($class);
                    if ($reflect->implementsInterface('\\ExportToolkit\\ExportService\\IFilter') && $reflect->isInstantiable()) {
                        $implementsIConfig[] = [$class];
                    }
                } catch (Exception $e) {
                }
            }

            $this->_helper->json($implementsIConfig);
        } elseif ($this->getParam('type') == 'export-conditionmodificator') {
            $implementsIConfig = [];
            foreach ($classes as $class) {
                try {
                    $reflect = new ReflectionClass($class);
                    if ($reflect->implementsInterface('\\ExportToolkit\\ExportService\\IConditionModificator') && $reflect->isInstantiable()) {
                        $implementsIConfig[] = [$class];
                    }
                } catch (Exception $e) {
                }
            }

            $this->_helper->json($implementsIConfig);
        } elseif ($this->getParam('type') == 'attribute-getter') {
            $implementsIConfig = [];
            foreach ($classes as $class) {
                try {
                    $reflect = new ReflectionClass($class);
                    if ($reflect->implementsInterface('\\ExportToolkit\\ExportService\\IGetter') && $reflect->isInstantiable()) {
                        $implementsIConfig[] = [$class];
                    }
                } catch (Exception $e) {
                }
            }

            $this->_helper->json($implementsIConfig);
        } elseif ($this->getParam('type') == 'attribute-interpreter') {
            $implementsIConfig = [];
            foreach ($classes as $class) {
                try {
                    $reflect = new ReflectionClass($class);
                    if ($reflect->implementsInterface('\\ExportToolkit\\ExportService\\IInterpreter') && $reflect->isInstantiable()) {
                        $implementsIConfig[] = [$class];
                    }
                } catch (Exception $e) {
                }
            }

            $this->_helper->json($implementsIConfig);
        } else {
            throw new Exception('unknown class type');
        }
    }

    protected function getCliCommand($configName)
    {
        return \Pimcore\Tool\Console::getPhpCli() . ' ' . PIMCORE_DOCUMENT_ROOT.'/pimcore/cli/console.php export-toolkit:export --config-name="' . $configName.'"';
    }

    public function executeExportAction()
    {
        $workername = $this->getParam('name');
        $config = \ExportToolkit\Configuration\Dao::getByName($workername);

        if ($config && $config->configuration->general->executor) {
            /** @var $className \ExportToolkit\ExportService\IExecutor */
            $className = $config->configuration->general->executor;
            try {
                $className::execute($workername, null);
            } catch (Exception $e) {
                $this->_helper->json(['success' => false, 'message' => $e->getMessage()]);
            }
        } else {
            $lockkey = 'exporttoolkit_' . $workername;
            if (\Pimcore\Model\Tool\Lock::isLocked($lockkey, 3 * 60 * 60)) { //lock for 3h
                $this->_helper->json(['success' => false]);
            }

            $cmd = $this->getCliCommand($workername);
            Logger::info($cmd);
            \Pimcore\Tool\Console::execInBackground($cmd, PIMCORE_LOG_DIRECTORY . DIRECTORY_SEPARATOR . 'exporttoolkit-output.log');
        }

        $this->_helper->json(['success' => true]);
    }

    public function isExportRunningAction()
    {
        $workername = $this->getParam('name');
        $lockkey = 'exporttoolkit_' . $workername;

        if (\Pimcore\Model\Tool\Lock::isLocked($lockkey, 3 * 60 * 60)) { //lock for 3h
            $this->_helper->json(['success' => true, 'locked' => true]);
        } else {
            $this->_helper->json(['success' => true, 'locked' => false]);
        }
    }
}
