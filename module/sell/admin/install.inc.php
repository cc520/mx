<?php
defined('IN_DESTOON') or exit('Access Denied');
$setting = include(DT_ROOT.'/file/setting/module-5.php');
update_setting($moduleid, $setting);
$sql = file_get(DT_ROOT.'/file/setting/'.$module.'.sql');
$sql = str_replace('_5', '_'.$moduleid, $sql);
$sql = str_replace('��Ӧ', $modulename, $sql);
sql_execute($sql);
include DT_ROOT.'/module/'.$module.'/admin/remkdir.inc.php';
?>