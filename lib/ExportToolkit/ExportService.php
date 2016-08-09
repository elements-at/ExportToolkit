<?php

namespace ExportToolkit;

use ExportToolkit\ExportService\Worker;
use Pimcore\Log\Simple;
use Pimcore\Model\Object\AbstractObject;

class ExportService {

    /**
     * @var Worker[]
     */
    protected $workers;

    public function __construct() {

        $exporters = Configuration\Dao::getList();
        $this->workers = array();
        foreach($exporters as $exporter) {
            $this->workers[$exporter->getName()] = new Worker($exporter);
        }

    }

    public function setUpExport($objectHook = false, $hookType = "save") {
        foreach($this->workers as $workerName => $worker) {
            if($worker->checkIfToConsider($objectHook, $hookType)) {
                $worker->setUpExport();
            }
        }
    }

    public function deleteFromExport(AbstractObject $object, $objectHook = false) {
        foreach($this->workers as $workerName => $worker) {
            if($worker->checkIfToConsider($objectHook, "delete")) {
                if($worker->checkClass($object)) {
                    $worker->deleteFromExport($object);
                } else {
                    \Logger::info("do not delete from export - object " . $object->getId() . " for " . $workerName . ".");
                }
            }
        }
    }

    public function updateExport(AbstractObject $object, $objectHook = false, $hookType = "save") {
        foreach($this->workers as $workerName => $worker) {
            if($worker->checkIfToConsider($objectHook, $hookType)) {
                if($worker->checkClass($object)) {
                    $worker->updateExport($object);
                } else {
                    \Logger::info("do not update export object " . $object->getId() . " for " . $workerName . ".");
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

    protected function doExecuteExport(Worker $worker, $workerName) {

        Simple::log("export-toolkit-" . $workerName, "");

        $limit = (int)$worker->getWorkerConfig()->getConfiguration()->general->limit;

        $page = $i = 0;
        $pageSize = 100;
        $count = $pageSize;

        $this->setUpExport(false);

        while($count > 0) {
            Simple::log("export-toolkit-" . $workerName, "=========================");
            Simple::log("export-toolkit-" . $workerName, "Page $workerName: $page");
            Simple::log("export-toolkit-" . $workerName, "=========================");

            $objects = $worker->getObjectList();
            $objects->setOffset($page * $pageSize);
            $objects->setLimit($pageSize);

            foreach($objects as $object) {
                Simple::log("export-toolkit-" . $workerName, "Updating product " . $object->getId());
                if($worker->checkClass($object)) {
                    $worker->updateExport($object);
                } else {
                    Simple::log("export-toolkit-" . $workerName, "do not update export object " . $object->getId() . " for " . $workerName . ".");
                }
                $i++;
                if($limit && ($i == $limit)){
                    break 2;
                }
            }
            $page++;
            $count = count($objects->getObjects());

            \Pimcore::collectGarbage();
        }

        $worker->commitData();

    }

}

