<?php
defined('IN_DESTOON') or exit('Access Denied');
$setting = include(DT_ROOT.'/file/setting/module-6.php');
update_setting($moduleid, $setting);
$sql = file_get(DT_ROOT.'/file/setting/'.$module.'.sql');
$sql = str_replace('_6', '_'.$moduleid, $sql);
$sql = str_replace('ว๓นบ', $modulename, $sql);
sql_execute($sql);
include DT_ROOT.'/module/'.$module.'/admin/remkdir.inc.php';
?>