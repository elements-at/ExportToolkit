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

return [
    'classes' => [
        'blacklist' => [

        ],
        'classlist' => [
            '\\Elements\\Bundle\\ExportToolkitBundle\\ExportService\\Interpreter\\ArrayToString',
            '\\Elements\\Bundle\\ExportToolkitBundle\\ExportService\\Interpreter\\Translations',
            '\\Elements\\Bundle\\ExportToolkitBundle\\ExportService\\Interpreter\\ElementToPath',
            '\\Elements\\Bundle\\ExportToolkitBundle\\ExportService\\AttributeClusterInterpreter\\DefaultXml',
            '\\Elements\\Bundle\\ExportToolkitBundle\\ExportService\\AttributeClusterInterpreter\\DefaultCsv',
            '\\Elements\\Bundle\\ExportToolkitBundle\\ExportService\\AttributeClusterInterpreter\\DefaultJson',
            '\\Elements\\Bundle\\ExportToolkitBundle\\ExportService\\Getter\\DefaultBrickGetterSequence',
            '\\Elements\\Bundle\\ExportToolkitBundle\\ExportService\\Getter\\DefaultBrickGetterSequenceToMultiselect'
        ],
        'override' => true
    ]
];
