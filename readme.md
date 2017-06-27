# Export Toolkit
The Export Toolkit provides functionality to configure exports of pimcore objects. It is possible to define exports for different output channels and destination systems. The flexible and easy to extend architecture of the export toolkit provides many hooks for custom functionality and therefore should be able to fulfill any export needs. 
Base of each export is an export configuration which is described below.

![export-toolkit](doc/images/export-toolkit.png)

## Migration notes from Pimcore 4 to 5

* migrate the config format to version 2.0 (see migrate-2.0.php)
* move the plugin's configuration file from website/var/plugins/ExportToolkit to 
            var/config/ExportToolkit/config.php
* Please note that the namespaces have been changed!!!
** Change ExportToolkit\* to Elements\Bundle\ExportToolkitBundle\*
** This also affects your export configurations. Check export-toolkit-configurations.php
* Be aware that this also affects your custom implementations (interpreters, getters and so so on). So you might have to change code. 
* adapt your crontab 
* The default output directory also has changed (used every time you don't specify a output file) to var/tmp/ExportToolkit

### Export Configuration

An export configuration is a container to configure a specific export of pimcore objects.

- Name: The name of an export configuration needs to be unique
- Description: The description can be some additional information about the export configuration
- Pimcore Object Class: An export configuration is always defined for one pimcore object class
- SQL Condition: Optional condition which is applied to the objects while exporting
- Query Language: Query language for the condition above
- Filter Class: An additional option to filter the objects before export. The select class must implement the interface ExportToolkit_ExportService_IFilter which has a method doExport that need to return true or false.
- Use Object Save Hook: Specify if the export for one object should be executed, when this object is saved.
- Condition Modificator Class: A class which can modify the SQL Condition at runtime. Must implement the interface ExportToolkit_ExportService_IConditionModificator
- Executor Class: A class which executes the worker configuration. Can be used to provide an alternative CLI script. Must implement the the interface ExportToolkit_ExportService_IExecutor.



### Attribute Cluster

Each Export Configuration has one or more Attribute Clusters. Attribute Clusters are a container for attributes, which should be exported in a specific way and to a specific destination.
* **Name:** Name of an attribute cluster. Does not have any further functionality.
* **Cluster Interpreter Class:** The selected class must extend the abstract class ExportToolkit_ExportService_AttributeClusterInterpreter_Abstract and contains all the logic for exporting the data to a specific destination and format. Following methods must be implemented
  * **setUpExport:** This method is executed before the export is launched.
    For example it can be used to clean up old export files, start a database transaction, etc.
    If not needed, just leave the method empty.
  * **commitDataRow:** This method is executed after all defined attributes of an object are exported. The to-export data is stored in the array $this->data[OBJECT_ID].
    For example it can be used to write each exported row to a destination database, write the exported entries to a file, etc.
    If not needed, just leave the method empty.
  * **commitData:** This method is executed after all objects are exported. If not cleaned up in the commitDataRow-method, all exported data is stored in the array $this->data.
    For example it can be used to write all data to a xml file or commit a database transaction, etc.
  * **deleteFromExport:** This method is executed of an object is not exported (anymore).
    For example it can be used to remove the entries from a destination database, etc.
  * **Configuration:** A json-String which is delivered to the Cluster Interpreter Class as a configuration object and is available via $this->config. This can be for example used to specify an output file path, table name, …

Different attribute clusters can be used to export data of objects to different destinations. For example specific object data should be exported as xml and json files.
Another use case would be to export product data to some output channel. So one attribute cluster could export product base data to a specific product table, a second attribute cluster could export all product relations to a relations table and a third attribute cluster could copy all product images to a specific file system location.


### Attributes

Each attribute cluster has one or more attributes. An attribute is one data entry in the export format, in the simplest case it also is one attribute in the pimcore object class (e.g. the name):
* **Name:** Defines the name of the attribute in the export format. If no fieldname of getter class is specified, it is also used as the name of the getter to retrieve the data from the pimcore object.
* **Fieldname:** Defines the fieldname of the pimcore object attribute. Is used as the name of the getter to retrieve the data from the pimcore object.
* **Locale:** If specified, this locale is delivered to the getter for retrieving the data.
* **Getter Class:** Specifying a getter class is a way integrate individual data retrieving. That could include some calculations, getting data from field collections or object bricks, getting data from related elements or whatever is needed.
   The selected getter class needs to implement the interface ExportToolkit_ExportService_IGetter which has a method get.
* **Interpreter Class:** Specifying an interpreter class is a way to integrate individual data transformation of the retrieved value. That could include some text transformations, number transformations, getting ID or path from an element or whatever is needed.
   The selected interpreter class needs to implement the interface ExportToolkit_ExportService_IInterpreter which has a method interpret.
* **Configuration:** A json-String which is delivered to the getter and interpreter class. So the getter and interpreter can be configured with custom options.



## Available Implementations for

### Cluster Interpreter Class

##### ExportToolkit_ExportService_AttributeClusterInterpreter_DefaultCsv:
* Exports data to a csv file.
* Available configuration options:
  * filename: filename of exported file, relative to document root
  * deleteFile: deletes file on startup

##### ExportToolkit_ExportService_AttributeClusterInterpreter_DefaultJson:
* Exports data to a json file.
* Available configuration options:
  * filename: filename of exported file, relative to document root

##### ExportToolkit_ExportService_AttributeClusterInterpreter_DefaultXml:
* Exports data to a xml file.
* Available configuration options:
  * filename: filename of exported file, relative to document root
  * rootElement: name for the root element in the xml file
  * rowElementName: name for the row element in the xml file


### Getter Class

##### ExportToolkit_ExportService_Getter_DefaultBrickGetterSequence:
* Retrieves data from object bricks. You can configure multiple sources and the getter returns the first data it finds.
* Available configuration options:
  * _source: _array of objects with following elements:
    * brickfield: fieldname of brick in object
    * bricktype: type of the brick
    * fieldname: fieldname of the attribute in the brick

##### ExportToolkit_ExportService_Getter_DefaultBrickGetterSequenceToMultiselect:
* Retrieves data from object bricks. You can configure multiple sources and the getter returns an array of all data it finds.
* Available configuration options:
  * source: array of objects with following elements:
      * brickfield: fieldname of brick in object
      * bricktype: type of the brick
      * fieldname: fieldname of the attribute in the brick


### Interpreter Class

##### ExportToolkit_ExportService_Interpreter_ArrayToString:
* converts an array to a comma separated string.

##### ExportToolkit_ExportService_Interpreter_ElementToPath:
* converts an object of Element_Interface to its path. If the data does not implement Element_Interface it returns an empty string.

##### ExportToolkit_ExportService_Interpreter_Translations:
* translates the value using the pimcore translations.
* Available configuration options:
   * translator: possible values are admin or website for using admin or website translations to translate the value


> ###To be able to select available classes a class map has to be created with the pimcore classmap generator.


# Development Instance 
> https://objecttools.elements.zone/admin/ 

