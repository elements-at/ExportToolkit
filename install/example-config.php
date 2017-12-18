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

return [
    'classes' => [
        'blacklist' => [

        ],
        'classlist' => [
            '\\ExportToolkit\\ExportService\\Interpreter\\ArrayToString',
            '\\ExportToolkit\\ExportService\\Interpreter\\Translations',
            '\\ExportToolkit\\ExportService\\Interpreter\\ElementToPath',
            '\\ExportToolkit\\ExportService\\AttributeClusterInterpreter\\DefaultXml',
            '\\ExportToolkit\\ExportService\\AttributeClusterInterpreter\\DefaultCsv',
            '\\ExportToolkit\\ExportService\\AttributeClusterInterpreter\\DefaultJson',
            '\\ExportToolkit\\ExportService\\Getter\\DefaultBrickGetterSequence',
            '\\ExportToolkit\\ExportService\\Getter\\DefaultBrickGetterSequenceToMultiselect',
            '\\ExportToolkit\\ExportService\\Interpreter\\KeyValueToString'
        ],
        'override' => true
    ]
];
