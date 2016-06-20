<?php

class ExportToolkit_ExportService_Worker {

    /**
     * @var ExportToolkit_Configuration
     */
    protected $workerConfig;

    /**
     * @var ExportToolkit_ExportService_IConfig
     */
    protected $workerConfigClass;

    /**
     * @var ExportToolkit_ExportService_AttributeClusterInterpreter_Abstract[]
     */
    protected $clusterInterpreters;
    protected $clusterInterpreterAttributes;

    /**
     * @var string
     */
    protected $pimcoreClass;

    public function __construct(ExportToolkit_Configuration $workerConfig) {
        $this->workerConfig = $workerConfig;

        $classId = trim($workerConfig->getConfiguration()->general->pimcoreClass);
        if ($classId) {
            $class = Object_Class::getById($classId);
            $this->pimcoreClass = trim("Object_" . ucfirst($class->getName()));
        } else {
            $this->pimcoreClass = "Object_Abstract";
        }

        $workerConfigClassName = trim($workerConfig->getConfiguration()->general->filterClass);
        if(class_exists($workerConfigClassName)) {
            $this->workerConfigClass = new $workerConfigClassName();
        } else {
            $this->workerConfigClass = new ExportToolkit_ExportService_Filter_Default();
        }

        $clusters = $workerConfig->getConfiguration()->attributeClusters;
        if($clusters) {
            foreach($clusters as $attributeCluster) {

                if(class_exists($attributeCluster->clusterInterpreterClass)) {
                    $interpreterClass = trim($attributeCluster->clusterInterpreterClass);
                    $clusterInterpreter = new $interpreterClass($attributeCluster->attributeClusterConfig);

                    $this->clusterInterpreters[] = $clusterInterpreter;
                    $this->clusterInterpreterAttributes[] = $attributeCluster->attributes;

                } else {
                    throw new Exception("Cluster interpreter class " . $attributeCluster->clusterInterpreterClass . " not found.");
                }
            }
        }
    }

    public function checkClass(Object_Abstract $object) {
        return $object instanceof $this->pimcoreClass;
    }

    public function checkIfToConsider($objectHook, $hookType) {
        if($objectHook && !$this->workerConfig->getConfiguration()->general->{"use" . ucfirst($hookType) . "Hook"}) {
            return false;
        }
        return true;
    }

    public function deleteFromExport(Object_Abstract $object) {
        if($this->clusterInterpreters) {
            foreach($this->clusterInterpreters as $clusterInterpreter) {
                $clusterInterpreter->deleteFromExport($object);
            }
        }
    }

    /**
     * @return ExportToolkit_Configuration
     */
    public function getWorkerConfig()
    {
        return $this->workerConfig;
    }

    public function updateExport(Object_Abstract $object) {


        if($this->clusterInterpreters && $this->workerConfigClass->doExport($object, $this->workerConfig) && $this->checkObjectInCondition($object)) {

            $originalInAdmin = Pimcore::inAdmin();
            $originalGetInheritedValues = Object_Abstract::doGetInheritedValues();
            $originalHideUnpublished = Object_Abstract::doHideUnpublished();
            $originalGetFallbackValues = Object_Localizedfield::doGetFallbackValues();

            Pimcore::unsetAdminMode();
            Object_Abstract::setGetInheritedValues(true);
            Object_Abstract::setHideUnpublished(false);
            Object_Localizedfield::setGetFallbackValues(true);



            //foreach interpreter group
            foreach($this->clusterInterpreters as $index => $clusterInterpreter) {
                if (!$clusterInterpreter->isRelevant($object)) {
                    continue;
                }
                $clusterAttributes = $this->clusterInterpreterAttributes[$index];

                if($clusterAttributes) {
                    foreach($clusterAttributes as $attribute) {
                        //  get and interpret values and set data to interpreter group
                        try {
                            $value = null;
                            $name = (string)$attribute->name;
                            if(!empty($attribute->attributeGetterClass)) {
                                $getter = trim($attribute->attributeGetterClass);
                                $value = $getter::get($object, $attribute->attributeConfig);
                            } else {
                                if(!empty($attribute->fieldname)) {
                                    $getter = "get" . ucfirst($attribute->fieldname);
                                } else {
                                    $getter = "get" . ucfirst($name);
                                }

                                if(method_exists($object, $getter)) {
                                    $value = $object->$getter($attribute->locale);
                                }
                            }

                            if(!empty($attribute->attributeInterpreterClass)) {
                                $interpreter = trim($attribute->attributeInterpreterClass);
                                $value = $interpreter::interpret($value, $attribute->attributeConfig);
                            } else {
                            }

                            $clusterInterpreter->setData($object, $name, $value);

                        } catch(Exception $e) {
                            Logger::err("Exception in ExportService: " . $e->getMessage(), $e);
                        }
                    }
                }

                // commit data row of interpreter group
                $clusterInterpreter->commitDataRow($object);
            }

            if($originalInAdmin) {
                Pimcore::setAdminMode();
            }
            Object_Abstract::setGetInheritedValues($originalGetInheritedValues);
            Object_Abstract::setHideUnpublished($originalHideUnpublished);
            Object_Localizedfield::setGetFallbackValues($originalGetFallbackValues);


        } else {
            Logger::info("Don't adding product " . $object->getId() . " to export.");
            $this->deleteFromExport($object);
        }
    }


    public function commitData() {
        if($this->clusterInterpreters) {
            foreach($this->clusterInterpreters as $clusterInterpreter) {
                $clusterInterpreter->commitData();
            }
        }
    }

    public function setUpExport() {
        if($this->clusterInterpreters) {
            foreach($this->clusterInterpreters as $clusterInterpreter) {
                $clusterInterpreter->setUpExport();
            }
        }
    }

    /**
     * @return Object_List_Concrete
     */
    public function getObjectList() {
        if ($this->pimcoreClass == "Object_Abstract") {
            $listClassName = "Object_List";
        } else {
            $listClassName = $this->pimcoreClass . "_List";
        }

        /**
         * @var $objects Object_List_Concrete
         */
        $objects = new $listClassName();
        $objects->setUnpublished(true);
        $objects->setObjectTypes(array("object", "folder", "variant"));
        if($this->workerConfig->getConfiguration()->general->queryLanguage) {
            $objects->setLocale($this->workerConfig->getConfiguration()->general->queryLanguage);
            if ($objects instanceof Object_List_Concrete) {
                $objects->setIgnoreLocalizedFields(false);
            }
        } else {
            if ($objects instanceof Object_List_Concrete) {
                $objects->setIgnoreLocalizedFields(true);
            }
        }
        $general = $this->workerConfig->getConfiguration()->general;
        $orderKey = $general->sqlOrderKey;
        $order =  $general->sqlOrder;
        if ($order && $orderKey) {
            $objects->setOrder($order);
            $objects->setOrderKey($orderKey, false);

        }
        $condition = $this->workerConfig->getConfiguration()->general->sqlCondition;
        if ($this->workerConfig->configuration->general->conditionModificator) {
            $conditionModificator = $this->workerConfig->configuration->general->conditionModificator;
            $condition = $conditionModificator::modify($this->workerConfig->name, $condition);

        }

        $objects->setCondition($condition);

        return $objects;

    }

    public function checkObjectInCondition(Object_Abstract $object) {
        if($this->workerConfig->getConfiguration()->general->sqlCondition) {

            $list = $this->getObjectList();
            $list->addConditionParam("o_id = ?", $object->getId());

            $idList = $list->loadIdList();
            if(empty($idList)) {
                return false;
            }
        }

        return true;
    }

}

