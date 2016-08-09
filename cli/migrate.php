<?php
// todo

//public static function upgrade()
//{
//$files = scandir(self::getFolder());
//
//foreach ($files as $file) {
//if (substr($file, 0, 13) == "exportconfig_") {
//$model = self::loadFile($file);
//$model->save();
//}
//}
//}
//
//private static function getFolder() {
//$folder = PIMCORE_WEBSITE_PATH . "/var/plugins/ExportToolkit/configs";
//if(!is_dir($folder)) {
//mkdir($folder, 0755, true);
//}
//return $folder;
//}
//
//private static function loadFile($filename) {
//$fileData = file_get_contents(self::getFolder() . "/" . $filename);
//$data = json_decode($fileData, true);
//return new Configuration(Dao::ROOT_PATH, $data["general"]["name"], $data);
//}