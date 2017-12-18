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

namespace ExportToolkit;

use Pimcore\Model\AbstractModel;

class Configuration extends AbstractModel
{
    public $path;
    public $name;
    public $configuration;

    public function __construct($path, $name = null, $configuration = null)
    {
        $this->path = $path;
        $this->name = $name;
        $this->configuration = $configuration;
    }

    /**
     * @param mixed $configuration
     */
    public function setConfiguration($configuration)
    {
        if (is_array($configuration)) {
            $configuration = json_decode(json_encode($configuration));
        }
        $this->configuration = $configuration;
    }

    /**
     * @return mixed
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    public function save()
    {
        if (empty($this->configuration)) {
            $this->configuration = new \stdClass();
            $this->configuration->general = new \stdClass();
        }

        if (empty($this->getPath())) {
            $this->setPath(null);
        }

        $this->configuration->general->path = $this->path;
        $this->configuration->general->name = $this->name;
        $this->getDao()->save();
    }

    public function delete()
    {
        $this->getDao()->delete();
    }
}
