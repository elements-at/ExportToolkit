<?php

class ExportToolkit_ExportService {

    use \ProcessManager\ExecutionTrait;

    /**
     * @var ExportToolkit_ExportService_Worker[]
     */
    protected $workers;

    public function __construct() {

        $exporters = ExportToolkit_Configuration::getList();
        $this->workers = array();
        foreach($exporters as $exporter) {
            $this->workers[$exporter->getName()] = new ExportToolkit_ExportService_Worker($exporter);
        }

    }

    public function setUpExport($objectHook = false, $hookType = "save") {
        foreach($this->workers as $workerName => $worker) {
            if($worker->checkIfToConsider($objectHook, $hookType)) {
                $worker->setUpExport();
            }
        }
    }

    public function deleteFromExport(Object_Abstract $object, $objectHook = false) {
        foreach($this->workers as $workerName => $worker) {
            if($worker->checkIfToConsider($objectHook, "delete")) {
                if($worker->checkClass($object)) {
                    $worker->deleteFromExport($object);
                } else {
                    Logger::info("do not delete from export - object " . $object->getId() . " for " . $workerName . ".");
                }
            }
        }
    }

    public function updateExport(Object_Abstract $object, $objectHook = false, $hookType = "save") {
        foreach($this->workers as $workerName => $worker) {
            if($worker->checkIfToConsider($objectHook, $hookType)) {
                if($worker->checkClass($object)) {
                    $worker->updateExport($object);
                } else {
                    Logger::info("do not update export object " . $object->getId() . " for " . $workerName . ".");
                }
            }
        }
    }

    public function commitData($objectHook = false, $hookType = "save") {
        foreach($this->workers as $workerName => $worker) {
            if($worker->checkIfToConsider($objectHook, $hookType)) {
                $worker->commitData();
            }
        }
    }

    public function executeExport($workerName = null) {

        if($workerName) {
            $worker = $this->workers[$workerName];
            $this->doExecuteExport($worker, $workerName);
        } else {
            foreach($this->workers as $workerName => $worker) {
                $this->doExecuteExport($worker, $workerName);
            }
        }

    }

    protected function doExecuteExport(ExportToolkit_ExportService_Worker $worker, $workerName) {

        $this->initProcessManager(null, ["name" => $workerName, "autoCreate" => true]);

        $monitoringItem = $this->getMonitoringItem();
        $monitoringItem->getLogger()->info("export-toolkit-" . $workerName);

        Pimcore_Log_Simple::log("export-toolkit-" . $workerName, "");

        //step 1 - setting up export
        $monitoringItem->setTotalSteps(3)->setCurrentStep(1)->setMessage("Setting up export $workerName")->save();
        $monitoringItem->getLogger()->info($monitoringItem->getMessage());

        $limit = (int)$worker->getWorkerConfig()->getConfiguration()->general->limit;

        $page = $i = 0;
        $pageSize = 100;
        $count = $pageSize;

        $totalObjectCount = $worker->getObjectList()->count();
        $this->setUpExport(false);


        //step 2 - exporting data
        $monitoringItem->setCurrentStep(2)->setMessage("Starting Exporting Data")->setTotalWorkload($totalObjectCount)->save();
        $monitoringItem->getLogger()->info($monitoringItem->getMessage());

        while($count > 0) {
            Pimcore_Log_Simple::log("export-toolkit-" . $workerName, "=========================");
            Pimcore_Log_Simple::log("export-toolkit-" . $workerName, "Page $workerName: $page");
            Pimcore_Log_Simple::log("export-toolkit-" . $workerName, "=========================");

            $monitoringItem->setDefaultProcessMessage("Exporting Data...")->save();
            $monitoringItem->getLogger()->info("Exporting Data, starting page: $page");

            $objects = $worker->getObjectList();
            $objects->setOffset($page * $pageSize);
            $objects->setLimit($pageSize);

            foreach($objects as $object) {
                Pimcore_Log_Simple::log("export-toolkit-" . $workerName, "Updating product " . $object->getId());

                $monitoringItem->getLogger()->debug("Updating product " . $object->getId());

                if($worker->checkClass($object)) {
                    $worker->updateExport($object);
                } else {
                    $monitoringItem->getLogger()->debug("do not update export object " . $object->getId() . " for " . $workerName . ".");
                    Pimcore_Log_Simple::log("export-toolkit-" . $workerName, "do not update export object " . $object->getId() . " for " . $workerName . ".");
                }
                $i++;
                if($limit && ($i == $limit)){
                    break 2;
                }
            }
            $page++;
            $count = count($objects->getObjects());

            $monitoringItem->setCurrentWorkload($page * $pageSize)->setTotalWorkload($totalObjectCount)->save();
            $monitoringItem->getLogger()->info("Process Export $workerName, finished page: $page");

            Pimcore::collectGarbage();
        }

        $monitoringItem->setWorloadCompleted()->save();


        //step 3 - committing data
        $monitoringItem->setCurrentStep(3)->setMessage("Committing Data")->save();
        $monitoringItem->getLogger()->info($monitoringItem->getMessage());

        $worker->commitData();

        $monitoringItem->setMessage('Job finished')->setCompleted();
    }

}

