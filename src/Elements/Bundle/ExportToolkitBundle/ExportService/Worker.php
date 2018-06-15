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

namespace Elements\Bundle\ExportToolkitBundle\ExportService;

use Elements\Bundle\ExportToolkitBundle\Configuration;
use Elements\Bundle\ExportToolkitBundle\ExportService\AttributeClusterInterpreter\AbstractAttributeClusterInterpreter;
use Elements\Bundle\ExportToolkitBundle\ExportService\Filter\DefaultFilter;
use Elements\Bundle\ExportToolkitBundle\Traits\LoggerAwareTrait;
use Pimcore\Log\ApplicationLogger;
use Pimcore\Logger;
use Pimcore\Model\Object\AbstractObject;
use Pimcore\Model\Object\Localizedfield;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Worker
{
    use LoggerAwareTrait;

    /**
     * @var Configuration
     */
    protected $workerConfig;

    protected $workerConfigClass;

    /**
     * @var AbstractAttributeClusterInterpreter[]
     */
    protected $clusterInterpreters;
    protected $clusterInterpreterAttributes;

    /**
     * @var string
     */
    protected $pimcoreClass;

    public function __construct(Configuration $workerConfig)
    {
        $this->workerConfig = $workerConfig;

        // add a default logger implementation so we can rely on a logger being set
        $this->logger = new NullLogger();

        $classId = trim($workerConfig->getConfiguration()->general->pimcoreClass);

        if ($classId) {
            $class = \Pimcore\Model\Object\ClassDefinition::getById($classId);
            $this->pimcoreClass = trim('\\Pimcore\\Model\\Object\\' . ucfirst($class->getName()));
        } else {
            $this->pimcoreClass = '\\Pimcore\\Model\\Object\\AbstractObject';
        }

        $workerConfigClassName = trim($workerConfig->getConfiguration()->general->filterClass);
        if (class_exists($workerConfigClassName)) {
            $this->workerConfigClass = new $workerConfigClassName();
        } else {
            $this->workerConfigClass = new DefaultFilter();
        }

        $clusters = $workerConfig->getConfiguration()->attributeClusters;
        if ($clusters) {
            foreach ($clusters as $attributeCluster) {
                if (class_exists($attributeCluster->clusterInterpreterClass)) {
                    $interpreterClass = trim($attributeCluster->clusterInterpreterClass);
                    $clusterInterpreter = new $interpreterClass($attributeCluster->attributeClusterConfig);

                    $this->clusterInterpreters[] = $clusterInterpreter;
                    $this->clusterInterpreterAttributes[] = $attributeCluster->attributes;
                } else {
                    throw new \Exception('Cluster interpreter class ' . $attributeCluster->clusterInterpreterClass . ' not found.');
                }
            }
        }
    }

    /**
     * @param ApplicationLogger|LoggerInterface $logger
     *
     * @return $this
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;

        // update logger in interpreters
        foreach ($this->clusterInterpreters as $clusterInterpreter) {
            $clusterInterpreter->setLogger($logger);
        }
    }

    public function checkClass(AbstractObject $object)
    {
        return $object instanceof $this->pimcoreClass;
    }

    public function checkIfToConsider($objectHook, $hookType)
    {
        if ($objectHook && !$this->workerConfig->getConfiguration()->general->{'use' . ucfirst($hookType) . 'Hook'}) {
            return false;
        }

        return true;
    }

    public function deleteFromExport(AbstractObject $object)
    {
        if ($this->clusterInterpreters) {
            foreach ($this->clusterInterpreters as $clusterInterpreter) {
                $clusterInterpreter->deleteFromExport($object);
            }
        }
    }

    /**
     * @return Configuration
     */
    public function getWorkerConfig()
    {
        return $this->workerConfig;
    }

    public function updateExport(AbstractObject $object)
    {
        if ($this->clusterInterpreters && $this->workerConfigClass->doExport($object, $this->workerConfig) && $this->checkObjectInCondition($object)) {
            $originalInAdmin = \Pimcore::inAdmin();
            $originalGetInheritedValues = AbstractObject::doGetInheritedValues();
            $originalHideUnpublished = AbstractObject::doHideUnpublished();
            $originalGetFallbackValues = Localizedfield::doGetFallbackValues();

            \Pimcore::unsetAdminMode();
            AbstractObject::setGetInheritedValues(true);
            AbstractObject::setHideUnpublished(false);
            Localizedfield::setGetFallbackValues(true);

            //foreach interpreter group
            foreach ($this->clusterInterpreters as $index => $clusterInterpreter) {
                if (!$clusterInterpreter->isRelevant($object)) {
                    continue;
                }
                $clusterAttributes = $this->clusterInterpreterAttributes[$index];

                if ($clusterAttributes) {
                    foreach ($clusterAttributes as $attribute) {
                        //  get and interpret values and set data to interpreter group
                        try {
                            $value = null;
                            $name = (string)$attribute->name;
                            if (!empty($attribute->attributeGetterClass)) {
                                $getter = trim($attribute->attributeGetterClass);
                                $value = $getter::get($object, $attribute->attributeConfig);
                            } else {
                                if (!empty($attribute->fieldname)) {
                                    $getter = 'get' . ucfirst($attribute->fieldname);
                                } else {
                                    $getter = 'get' . ucfirst($name);
                                }

                                if (method_exists($object, $getter)) {
                                    $value = $object->$getter($attribute->locale);
                                }
                            }

                            if (!empty($attribute->attributeInterpreterClass)) {
                                $interpreter = trim($attribute->attributeInterpreterClass);
                                $value = $interpreter::interpret($value, $attribute->attributeConfig);
                            } else {
                            }

                            $clusterInterpreter->setData($object, $name, $value);
                        } catch (\Exception $e) {
                            Logger::err('Exception in ExportService: ' . $e->getMessage(), $e);
                        }
                    }
                }

                // commit data row of interpreter group
                $clusterInterpreter->commitDataRow($object);
            }

            if ($originalInAdmin) {
                \Pimcore::setAdminMode();
            }
            AbstractObject::setGetInheritedValues($originalGetInheritedValues);
            AbstractObject::setHideUnpublished($originalHideUnpublished);
            Localizedfield::setGetFallbackValues($originalGetFallbackValues);
        } else {
            Logger::info("Don't adding product " . $object->getId() . ' to export.');
            $this->deleteFromExport($object);
        }
    }

    public function commitData()
    {
        if ($this->clusterInterpreters) {
            foreach ($this->clusterInterpreters as $clusterInterpreter) {
                $clusterInterpreter->commitData();
            }
        }
    }

    public function setUpExport()
    {
        if ($this->clusterInterpreters) {
            foreach ($this->clusterInterpreters as $clusterInterpreter) {
                $clusterInterpreter->setUpExport();
            }
        }
    }

    /**
     * @return \Pimcore\Model\Object\Listing\Concrete
     */
    public function getObjectList()
    {
        if ($this->pimcoreClass == 'Object_Abstract' || $this->pimcoreClass == 'AbstractObject'
                    || $this->pimcoreClass == '\\Pimcore\\Model\\Object\\AbstractObject') {
            $listClassName = '\\Pimcore\\Model\\Object\\Listing';
        } else {
            $listClassName = $this->pimcoreClass . '\\Listing';
        }

        /**
         * @var $objects \Pimcore\Model\Object\Listing\Concrete|\Pimcore\Model\Object\Listing\Dao
         */
        $objects = new $listClassName();
        $objects->setUnpublished(true);
        $objects->setObjectTypes(['object', 'folder', 'variant']);
        if ($this->workerConfig->getConfiguration()->general->queryLanguage) {
            $objects->setLocale($this->workerConfig->getConfiguration()->general->queryLanguage);
            if ($objects instanceof \Pimcore\Model\Object\Listing\Concrete) {
                $objects->setIgnoreLocalizedFields(false);
            }
        } else {
            if ($objects instanceof \Pimcore\Model\Object\Listing\Concrete) {
                $objects->setIgnoreLocalizedFields(true);
            }
        }
        $general = $this->workerConfig->getConfiguration()->general;
        $orderKey = $general->sqlOrderKey;
        $order = $general->sqlOrder;
        if ($order && $orderKey) {
            $objects->setOrder($order);
            $objects->setOrderKey($orderKey, false);
        }
        $condition = $this->workerConfig->getConfiguration()->general->sqlCondition;
        if ($this->workerConfig->configuration->general->conditionModificator) {
            $conditionModificator = $this->workerConfig->configuration->general->conditionModificator;

            if (class_exists($conditionModificator)) {
                $conditionModificatorReflector = new \ReflectionClass($conditionModificator);

                // support modifying the list (e.g. add joins)
                if ($conditionModificatorReflector->implementsInterface(IListModificator::class)) {
                    $conditionModificator::modifyList($this->workerConfig->name, $objects);
                }

                // modify condition string
                if ($conditionModificatorReflector->implementsInterface(IConditionModificator::class)) {
                    $condition = $conditionModificator::modify($this->workerConfig->name, $condition);
                }
            }
        }

        $objects->setCondition($condition);

        return $objects;
    }

    public function checkObjectInCondition(AbstractObject $object)
    {
        if ($this->workerConfig->getConfiguration()->general->sqlCondition) {
            $list = $this->getObjectList();
            $list->addConditionParam('o_id = ?', $object->getId());

            $idList = $list->loadIdList();
            if (empty($idList)) {
                return false;
            }
        }

        return true;
    }
}
