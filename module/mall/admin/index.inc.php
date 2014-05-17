<?php
defined('IN_DESTOON') or exit('Access Denied');
require MD_ROOT.'/mall.class.php';
$do = new mall($moduleid);
$menus = array (
    array('������Ʒ', '?moduleid='.$moduleid.'&action=add'),
    array('��Ʒ�б�', '?moduleid='.$moduleid),
    array('�����Ʒ', '?moduleid='.$moduleid.'&action=check'),
    array('�¼���Ʒ', '?moduleid='.$moduleid.'&action=expire'),
    array('δͨ����Ʒ', '?moduleid='.$moduleid.'&action=reject'),
    array('����վ', '?moduleid='.$moduleid.'&action=recycle'),
    array('�ƶ�����', '?moduleid='.$moduleid.'&action=move'),
);

if(in_array($action, array('add', 'edit'))) {
	$FD = cache_read('fields-'.substr($table, strlen($DT_PRE)).'.php');
	if($FD) require DT_ROOT.'/include/fields.func.php';
	isset($post_fields) or $post_fields = array();
	$CP = $MOD['cat_property'];
	if($CP) require DT_ROOT.'/include/property.func.php';
	isset($post_ppt) or $post_ppt = array();
}

if($_catids || $_areaids) require DT_ROOT.'/admin/admin_check.inc.php';

if(in_array($action, array('', 'check', 'expire', 'reject', 'recycle'))) {
	$sfields = array('ģ��',  '��Ʒ����', '��ƷƷ��',  '���', '��˾��', '��ϵ��', '��ϵ�绰', '��ϵ��ַ', '�����ʼ�', '��ϵMSN', '��ϵQQ', '��Ա��', 'IP');
	$dfields = array('keyword', 'title', 'brand', 'introduce', 'company', 'truename', 'telephone', 'address', 'email', 'msn', 'qq','username', 'ip');
	$sorder  = array('�������ʽ', '����ʱ�併��', '����ʱ������', '����ʱ�併��', '����ʱ������', VIP.'������', VIP.'��������', '��Ʒ���۽���', '��Ʒ��������', '������������', '������������', '������������', '������������', '�����������', '�����������', '���۴�������', '���۴�������', '�����������', '�����������', '��ϢID����', '��ϢID����');
	$dorder  = array($MOD['order'], 'edittime DESC', 'edittime ASC', 'addtime DESC', 'addtime ASC', 'vip DESC', 'vip ASC', 'price DESC', 'price DESC', 'orders DESC', 'orders ASC', 'sales DESC', 'sales ASC', 'amount DESC', 'amount ASC', 'comments DESC', 'comments ASC', 'hits DESC', 'hits ASC', 'itemid DESC', 'itemid ASC');

	$level = isset($level) ? intval($level) : 0;
	isset($fields) && isset($dfields[$fields]) or $fields = 0;
	isset($order) && isset($dorder[$order]) or $order = 0;
	$elite = isset($elite) ? intval($elite) : 0;
	$price = isset($price) ? intval($price) : 0;

	isset($datetype) && in_array($datetype, array('edittime', 'addtime')) or $datetype = 'edittime';
	$fromdate = isset($fromdate) && is_date($fromdate) ? $fromdate : '';
	$fromtime = $fromdate ? strtotime($fromdate.' 0:0:0') : 0;
	$todate = isset($todate) && is_date($todate) ? $todate : '';
	$totime = $todate ? strtotime($todate.' 23:59:59') : 0;
	
	$minprice = isset($minprice) ? dround($minprice) : '';
	$minprice or $minprice = '';
	$maxprice = isset($maxprice) ? dround($maxprice) : '';
	$maxprice or $maxprice = '';
	$minorders = isset($minorders) ? intval($minorders) : '';
	$minorders or $minorders = '';
	$maxorders = isset($maxorders) ? intval($maxorders) : '';
	$maxorders or $maxorders = '';
	$minsales = isset($minsales) ? intval($minsales) : '';
	$minsales or $minsales = '';
	$maxsales = isset($maxsales) ? intval($maxsales) : '';
	$maxsales or $maxsales = '';
	$minamount = isset($minamount) ? intval($minamount) : '';
	$minamount or $minamount = '';
	$maxamount = isset($maxamount) ? intval($maxamount) : '';
	$maxamount or $maxamount = '';
	$mincomments = isset($mincomments) ? intval($mincomments) : '';
	$mincomments or $mincomments = '';
	$maxcomments = isset($maxcomments) ? intval($maxcomments) : '';
	$maxcomments or $maxcomments = '';
	$minvip = isset($minvip) ? intval($minvip) : '';
	$minvip or $minvip = '';
	$maxvip = isset($maxvip) ? intval($maxvip) : '';
	$maxvip or $maxvip = '';
	$itemid or $itemid = '';

	$fields_select = dselect($sfields, 'fields', '', $fields);
	$level_select = level_select('level', '����', $level);
	$order_select  = dselect($sorder, 'order', '', $order);

	$condition = '';
	if($_childs) $condition .= " AND catid IN (".$_childs.")";//CATE
	if($_areaids) $condition .= " AND areaid IN (".$_areaids.")";//CITY
	if($keyword) $condition .= " AND $dfields[$fields] LIKE '%$keyword%'";
	if($catid) $condition .= ($CAT['child']) ? " AND catid IN (".$CAT['arrchildid'].")" : " AND catid=$catid";
	if($areaid) $condition .= ($ARE['child']) ? " AND areaid IN (".$ARE['arrchildid'].")" : " AND areaid=$areaid";

	if($level) $condition .= " AND level=$level";
	if($elite) $condition .= " AND elite>0";
	if($price) $condition .= " AND price>0";
	if($minprice)  $condition .= " AND price>=$minprice";
	if($maxprice)  $condition .= " AND price<=$maxprice";
	if($minorders)  $condition .= " AND orders>=$minorders";
	if($maxorders)  $condition .= " AND orders<=$maxorders";
	if($minsales)  $condition .= " AND sales>=$minsales";
	if($maxsales)  $condition .= " AND sales<=$maxsales";
	if($minamount)  $condition .= " AND amount>=$minamount";
	if($maxamount)  $condition .= " AND amount<=$maxamount";
	if($mincomments)  $condition .= " AND comments>=$mincomments";
	if($maxcomments)  $condition .= " AND comments<=$maxcomments";
	if($minvip)  $condition .= " AND vip>=$minvip";
	if($maxvip)  $condition .= " AND vip<=$maxvip";
	if($fromtime) $condition .= " AND `$datetype`>=$fromtime";
	if($totime) $condition .= " AND `$datetype`<=$totime";
	if($itemid) $condition .= " AND itemid=$itemid";

	$timetype = strpos($dorder[$order], 'add') !== false ? 'add' : '';
}
switch($action) {
	case 'add':
		if($submit) {
			if($do->pass($post)) {
				if($FD) fields_check($post_fields);
				if($CP) property_check($post_ppt);
				$do->add($post);
				if($FD) fields_update($post_fields, $table, $do->itemid);
				if($CP) property_update($post_ppt, $moduleid, $post['catid'], $do->itemid);
				if($MOD['show_html'] && $post['status'] > 2) $do->tohtml($do->itemid);
				dmsg('���ӳɹ�', '?moduleid='.$moduleid.'&action='.$action);
			} else {
				msg($do->errmsg);
			}
		} else {
			foreach($do->fields as $v) {
				isset($$v) or $$v = '';
			}
			$content = '';
			$status = 3;
			$addtime = timetodate($DT_TIME);
			$username = $_username;
			$item = array();
			$menuid = 0;
			$tname = $menus[$menuid][0];
			isset($url) or $url = '';
			if($url) {
				$tmp = fetch_url($url);
				if($tmp) extract($tmp);
			}
			$EXP = array();
			$result = $db->query("SELECT * FROM {$DT_PRE}mall_express WHERE username='$username' AND parentid=0 ORDER BY listorder ASC,itemid ASC LIMIT 100");
			while($r = $db->fetch_array($result)) {
				$EXP[] = $r;
			}
			include tpl('edit', $module);
		}
	break;
	case 'edit':
		$itemid or msg();
		$do->itemid = $itemid;
		if($submit) {
			if($do->pass($post)) {
				if($FD) fields_check($post_fields);
				if($CP) property_check($post_ppt);
				if($FD) fields_update($post_fields, $table, $do->itemid);
				if($CP) property_update($post_ppt, $moduleid, $post['catid'], $do->itemid);
				$do->edit($post);
				dmsg('�޸ĳɹ�', $forward);
			} else {
				msg($do->errmsg);
			}
		} else {
			$item = $do->get_one();
			extract($item);
			$EXP = array();
			$result = $db->query("SELECT * FROM {$DT_PRE}mall_express WHERE username='$username' AND parentid=0 ORDER BY listorder ASC,itemid ASC LIMIT 100");
			while($r = $db->fetch_array($result)) {
				$EXP[] = $r;
			}
			$addtime = timetodate($addtime);
			$menuon = array('5', '4', '2', '1', '3');
			$menuid = $menuon[$status];
			$tname = '�޸�'.$MOD['name'];
			include tpl($action, $module);
		}
	break;
	case 'move':
		if($submit) {
			$fromids or msg('����д��ԴID');
			if($tocatid) {
				$db->query("UPDATE {$table} SET catid=$tocatid WHERE `{$fromtype}` IN ($fromids)");
				dmsg('�ƶ��ɹ�', $forward);
			} else {
				msg('��ѡ��Ŀ�����');
			}
		} else {
			$itemid = $itemid ? implode(',', $itemid) : '';
			$menuid = 5;
			include tpl($action, $module);
		}
	break;
	case 'update':
		is_array($itemid) or msg('��ѡ����Ʒ');
		foreach($itemid as $v) {
			$do->update($v);
		}
		dmsg('���³ɹ�', $forward);
	break;
	case 'tohtml':
		is_array($itemid) or msg('��ѡ����Ʒ');
		foreach($itemid as $itemid) {
			tohtml('show', $module);
		}
		dmsg('���³ɹ�', $forward);
	break;
	case 'delete':
		$itemid or msg('��ѡ����Ʒ');
		isset($recycle) ? $do->recycle($itemid) : $do->delete($itemid);
		dmsg('ɾ���ɹ�', $forward);
	break;
	case 'restore':
		$itemid or msg('��ѡ����Ʒ');
		$do->restore($itemid);
		dmsg('��ԭ�ɹ�', $forward);
	break;
	case 'refresh':
		$itemid or msg('��ѡ����Ʒ');
		$do->refresh($itemid);
		dmsg('ˢ�³ɹ�', $forward);
	break;
	case 'clear':
		$do->clear();
		dmsg('��ճɹ�', $forward);
	break;
	case 'level':
		$itemid or msg('��ѡ����Ʒ');
		$level = intval($level);
		$do->level($itemid, $level);
		dmsg('�������óɹ�', $forward);
	break;
	case 'type':
		$itemid or msg('��ѡ����Ʒ');
		$tid = intval($tid);
		array_key_exists($tid, $TYPE) or $tid = 0;
		$do->type($itemid, $tid);
		dmsg('�������óɹ�', $forward);
	break;
	case 'recycle':
		$lists = $do->get_list('status=0'.$condition, $dorder[$order]);
		$menuid = 5;
		include tpl('index', $module);
	break;
	case 'reject':
		if($itemid && !$psize) {
			$do->reject($itemid);
			dmsg('�ܾ��ɹ�', $forward);
		} else {
			$lists = $do->get_list('status=1'.$condition, $dorder[$order]);
			$menuid = 4;
			include tpl('index', $module);
		}
	break;
	case 'expire':
		if($itemid && !$psize) {
			$do->unsale($itemid);
			dmsg('�¼ܳɹ�', $forward);
		} else {
			$lists = $do->get_list('status=4'.$condition);
			$menuid = 3;
			include tpl('index', $module);
		}
	break;
	case 'onsale':
		$itemid or msg('��ѡ����Ʒ');
		$do->onsale($itemid);
		dmsg('�ϼܳɹ�', $forward);
	break;
	case 'relate_del':
		$itemid or msg('��ѡ����Ʒ');
		$do->itemid = $itemid;
		$M = $do->get_one();
		($M && $M['status'] == 3) or msg('��ѡ����Ʒ');
		$id = isset($id) ? intval($id) : 0;
		$id or msg('��ѡ���Ƴ���Ʒ');
		$do->itemid = $id;
		$A = $do->get_one();
		$do->relate_del($M, $A);
		dmsg('�Ƴ��ɹ�', '?moduleid='.$moduleid.'&file='.$file.'&itemid='.$itemid.'&action=relate');
	break;
	case 'relate_add':
		$relate_name = isset($relate_name) ? htmlspecialchars(trim($relate_name)) : '';
		$relate_name or msg('����д��������');
		$itemid or msg('��ѡ����Ʒ');
		$do->itemid = $itemid;
		$M = $do->get_one();
		($M && $M['status'] == 3) or msg('��ѡ����Ʒ');
		$id = isset($id) ? intval($id) : 0;
		$id or msg('��ѡ�������Ʒ');
		$do->itemid = $id;
		$A = $do->get_one();
		($A && $A['status'] == 3 && $A['username'] == $M['username']) or msg('��ѡ�������Ʒ');
		if($itemid == $id) msg('ѡ�����Ʒ�Ѿ�����');
		$do->relate_add($M, $A, $relate_name);
		dmsg('�����ɹ�', '?moduleid='.$moduleid.'&file='.$file.'&itemid='.$itemid.'&action=relate');
	break;
	case 'relate':
		$itemid or msg('��ѡ����Ʒ');
		$do->itemid = $itemid;
		$M = $do->get_one();
		($M && $M['status'] == 3) or msg('��ѡ����Ʒ');
		if($submit) {
			$relate_name = isset($relate_name) ? htmlspecialchars(trim($relate_name)) : '';
			$relate_name or msg('����д��������');
			$do->relate($M, $post, $relate_name);
			dmsg('���³ɹ�', '?moduleid='.$moduleid.'&file='.$file.'&itemid='.$itemid.'&action=relate');
		} else {
			$lists = $do->relate_list($M);
			include tpl('relate', $module);
		}
	break;
	case 'check':
		if($itemid && !$psize) {
			$do->check($itemid);
			dmsg('��˳ɹ�', $forward);
		} else {
			$lists = $do->get_list('status=2'.$condition, $dorder[$order]);
			$menuid = 2;
			include tpl('index', $module);
		}
	break;
	default:
		$lists = $do->get_list('status=3'.$condition, $dorder[$order]);
		$menuid = 1;
		include tpl('index', $module);
	break;
}
?>