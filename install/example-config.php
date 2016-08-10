<?php 

return [
    "classes" => [
        "blacklist" => [

        ],
        "classlist" => [
            "\\ExportToolkit\\ExportService\\Interpreter\\ArrayToString",
            "\\ExportToolkit\\ExportService\\Interpreter\\Translations",
            "\\ExportToolkit\\ExportService\\Interpreter\\ElementToPath",
            "\\ExportToolkit\\ExportService\\AttributeClusterInterpreter\\DefaultXml",
            "\\ExportToolkit\\ExportService\\AttributeClusterInterpreter\\DefaultCsv",
            "\\ExportToolkit\\ExportService\\AttributeClusterInterpreter\\DefaultJson",
            "\\ExportToolkit\\ExportService\\Getter\\DefaultBrickGetterSequence",
            "\\ExportToolkit\\ExportService\\Getter\\DefaultBrickGetterSequenceToMultiselect",
            "\\ExportToolkit\\ExportService\\Interpreter\\KeyValueToString"
        ],
        "override" => TRUE
    ]
];
