<?php
/*
	[Destoon B2B System] Copyright (c) 2008-2013 Destoon.COM
	This is NOT a freeware, use is subject to license.txt
*/
@set_time_limit(0);
require 'common.inc.php';
if($DT_BOT) dhttp(403);
$from = isset($from) ? trim($from) : '';
$swfupload = isset($swfupload) ? 1 : 0;
$errmsg = '';
if($swfupload) {//Fix FlashPlayer Bug
	$swf_userid = intval($swf_userid);
	if($swf_userid != $_userid && is_md5($swf_auth)) {
		$swf_groupid = intval($swf_groupid);
		if($swf_auth == md5($swf_userid.$swf_username.$swf_groupid.$swf_company.DT_KEY.$DT_IP)) {
			$_userid = $swf_userid;
			$_username = $swf_username;
			$_groupid = $swf_groupid;
			$_company = convert($swf_company, 'utf-8', DT_CHARSET);
			$MG = cache_read('group-'.$_groupid.'.php');
		} else {
			$errmsg = 'Error(0)'.'SWFUpload Denied';
			if($swfupload) exit(convert($errmsg, DT_CHARSET, 'utf-8'));
			dalert($errmsg);
		}
	}
}
$upload_table = $DT_PRE.'upload_'.($_userid%10);
if(!in_array($from, array('thumb', 'album', 'photo', 'editor', 'attach', 'file'))) {
	$errmsg = 'Error(1)'.'Access Denied';
	if($swfupload) exit(convert($errmsg, DT_CHARSET, 'utf-8'));
	dalert($errmsg);
}
if(!$MG['upload']) {
	$errmsg = 'Error(2)'.lang('message->upload_refuse');
	if($swfupload) exit(convert($errmsg, DT_CHARSET, 'utf-8'));
	dalert($errmsg);
}
$remote = isset($remote) ? trim($remote) : '';
if(!$_FILES && !$remote) {
	$errmsg = 'Error(3)'.lang('message->upload_fail');
	if($swfupload) exit(convert($errmsg, DT_CHARSET, 'utf-8'));
	dalert($errmsg);
}
if($DT['uploadlog'] && $MG['uploadday']) {
	$condition = 'addtime>'.($DT_TIME - 86400);
	$condition .= $_username ? " AND username='$_username'" : " AND ip='$DT_IP'";
	$r = $db->get_one("SELECT COUNT(*) AS num FROM {$upload_table} WHERE $condition");
	if($r['num'] >= $MG['uploadday']) {
		$errmsg = 'Error(4)'.lang('message->upload_limit_day', array($MG['uploadday'], $r['num']));
		if($swfupload) exit(convert($errmsg, DT_CHARSET, 'utf-8'));
		dalert($errmsg);
	}
}
require DT_ROOT.'/include/post.func.php';
$uploaddir = 'file/upload/'.timetodate($DT_TIME, $DT['uploaddir']).'/';
is_dir(DT_ROOT.'/'.$uploaddir) or dir_create(DT_ROOT.'/'.$uploaddir);
if($MG['uploadtype']) $DT['uploadtype'] = $MG['uploadtype'];
if($MG['uploadsize']) $DT['uploadsize'] = $MG['uploadsize'];
if($remote && strlen($remote) > 17 && strpos($remote, '://') !== false) {
	require DT_ROOT.'/include/remote.class.php';
	$do = new remote($remote, $uploaddir);
} else {
	require DT_ROOT.'/include/upload.class.php';
	$do = new upload($_FILES, $uploaddir);
}
$js = $errjs = '';
if($from == 'thumb' || $from == 'album' || $from == 'photo' || $from == 'file') {
	$errjs .= 'window.parent.cDialog();';
} else if($from == 'editor' || $from == 'attach') {
	$errjs .= 'window.parent.GetE("frmUpload").reset();';
}
if($do->save()) {
	$session = new dsession();
	$limit = intval($MG['uploadlimit']);
	$total = isset($_SESSION['uploads']) ? count($_SESSION['uploads']) : 0;
	if($limit && $total > $limit - 1) {
		file_del(DT_ROOT.'/'.$do->saveto);
		$errmsg = 'Error(5)'.lang('message->upload_limit', array($limit));
		if($swfupload) exit(convert($errmsg, DT_CHARSET, 'utf-8'));
		dalert($errmsg, '', $errjs);
	}
	$t = @getimagesize(DT_ROOT.'/'.$do->saveto);
	if(in_array($do->ext, array('jpg', 'jpeg', 'gif', 'png', 'swf', 'bmp')) && !$t) {
		file_del(DT_ROOT.'/'.$do->saveto);
		$errmsg = 'Error(6)'.lang('message->upload_bad');
		if($swfupload) exit(convert($errmsg, DT_CHARSET, 'utf-8'));
		dalert($errmsg, '', $errjs);
	}
	if(in_array($do->ext, array('jpg', 'jpeg')) && $t['channels'] == 4) {
		file_del(DT_ROOT.'/'.$do->saveto);
		$errmsg = 'Error(7)'.lang('message->upload_cmyk');
		if($swfupload) exit(convert($errmsg, DT_CHARSET, 'utf-8'));
		dalert($errmsg, '', $errjs);
	}
	$img_w = $img_h = 0;
	if($do->image) {
		require DT_ROOT.'/include/image.class.php';
		if($do->ext == 'gif' && in_array($from, array('thumb', 'album', 'photo'))) {
			if(!function_exists('imagegif') || !function_exists('imagecreatefromgif')) {
				file_del(DT_ROOT.'/'.$do->saveto);
				$errmsg = 'Error(8)'.lang('message->upload_jpg');
				if($swfupload) exit(convert($errmsg, DT_CHARSET, 'utf-8'));
				dalert($errmsg, '', $errjs);
			}
		}
		if($DT['bmp_jpg'] && $do->ext == 'bmp') {
			require DT_ROOT.'/include/bmp.func.php';
			$bmp_src = DT_ROOT.'/'.$do->saveto;
			$bmp = imagecreatefrombmp($bmp_src);
			if($bmp) {
				$do->saveto = str_replace('.bmp', '.jpg', $do->saveto);
				$do->ext = 'jpg';
				imagejpeg($bmp, DT_ROOT.'/'.$do->saveto);
				file_del($bmp_src);
				if(DT_CHMOD) @chmod(DT_ROOT.'/'.$do->saveto, DT_CHMOD);
			}
		}
		$info = getimagesize(DT_ROOT.'/'.$do->saveto);
		$img_w = $info[0];
		$img_h = $info[1];
		if($DT['max_image'] && in_array($from, array('editor', 'album', 'photo'))) {
			if($img_w > $DT['max_image']) {
				$img_h = intval($DT['max_image']*$img_h/$img_w);
				$img_w = $DT['max_image'];
				$image = new image(DT_ROOT.'/'.$do->saveto);
				$image->thumb($img_w, $img_h);
			}
		}
		if($from == 'thumb') {
			if($width && $height) {
				$image = new image(DT_ROOT.'/'.$do->saveto);
				$image->thumb($width, $height, $DT['thumb_title']);
				$img_w = $width;
				$img_h = $height;
				$do->file_size = filesize(DT_ROOT.'/'.$do->saveto);
			}
		} else if($from == 'album' || $from == 'photo') {
			$saveto = $do->saveto;
			$do->saveto = $do->saveto.'.thumb.'.$do->ext;
			file_copy(DT_ROOT.'/'.$saveto, DT_ROOT.'/'.$do->saveto);
			$middle = $saveto.'.middle.'.$do->ext;
			file_copy(DT_ROOT.'/'.$saveto, DT_ROOT.'/'.$middle);
			if($DT['water_type'] == 2) {
				$image = new image(DT_ROOT.'/'.$saveto);
				$image->waterimage();
			} else if($DT['water_type'] == 1) {
				$image = new image(DT_ROOT.'/'.$saveto);
				$image->watertext();
			}
			if($DT['water_type'] && $DT['water_com'] && $_groupid > 5) {
				$image = new image(DT_ROOT.'/'.$saveto);
				$image->text = $_company;
				$image->pos = 5;
				$image->watertext();
			}
			if($from == 'photo') $DT['thumb_album'] = 0;
			$image = new image(DT_ROOT.'/'.$do->saveto);
			$image->thumb($width, $height, $DT['thumb_album']);
			$image = new image(DT_ROOT.'/'.$middle);
			$image->thumb($DT['middle_w'], $DT['middle_h'], $DT['thumb_album']);
			if($DT['water_middle'] && $DT['water_type']) {
				if($DT['water_type'] == 2) {
					$image = new image(DT_ROOT.'/'.$middle);
					$image->waterimage();
				} else if($DT['water_type'] == 1) {
					$image = new image(DT_ROOT.'/'.$middle);
					$image->watertext();
				}
			}
		} else if($from == 'editor') {
			if($_groupid == 1 && !isset($watermark)) $DT['water_type'] = 0;
			if($DT['water_type']) {
				$image = new image(DT_ROOT.'/'.$do->saveto);
				if($DT['water_type'] == 2) {
					$image->waterimage();
				} else if($DT['water_type'] == 1) {
					$image->watertext();
				}
			}
		}
	}
	$saveto = linkurl($do->saveto);
	if($DT['ftp_remote'] && $DT['remote_url']) {
		require DT_ROOT.'/include/ftp.class.php';
		$ftp = new dftp($DT['ftp_host'], $DT['ftp_user'], $DT['ftp_pass'], $DT['ftp_port'], $DT['ftp_path'], $DT['ftp_pasv'], $DT['ftp_ssl']);
		if($ftp->connected) {
			$exp = explode("file/upload/", $saveto);
			$remote = $exp[1];
			if($ftp->dftp_put($do->saveto, $remote)) {
				$saveto = $DT['remote_url'].$remote;
				file_del(DT_ROOT.'/'.$do->saveto);
				if(strpos($do->saveto, '.thumb.') !== false) {
					$local = str_replace('.thumb.'.$do->ext, '', $do->saveto);
					$remote = str_replace('.thumb.'.$do->ext, '', $exp[1]);
					$ftp->dftp_put($local, $remote);
					file_del(DT_ROOT.'/'.$local);
					$local = str_replace('.thumb.'.$do->ext, '.middle.'.$do->ext, $do->saveto);
					$remote = str_replace('.thumb.'.$do->ext, '.middle.'.$do->ext, $exp[1]);
					$ftp->dftp_put($local, $remote);
					file_del(DT_ROOT.'/'.$local);
				}
			}
		}
	}
	$fid = isset($fid) ? $fid : '';
	if(isset($old) && $old && in_array($from, array('thumb', 'photo'))) delete_upload($old, $_userid);
	$_SESSION['uploads'][] = $swfupload ? str_replace('.thumb.'.$do->ext, '', $saveto) : $saveto;
	if($DT['uploadlog']) $db->query("INSERT INTO {$upload_table} (item,fileurl,filesize,fileext,upfrom,width,height,moduleid,username,ip,addtime,itemid) VALUES ('".md5($saveto)."','$saveto','$do->file_size','$do->ext','$from','$img_w','$img_h','$moduleid','$_username','$DT_IP','$do->uptime','$itemid')");
	if($swfupload) exit('FILEID:'.$saveto);
	$pr = 'parent.document.getElementById';
	if($from == 'thumb') {
		$js .= 'try{'.$pr.'("d'.$fid.'").src="'.$saveto.'";}catch(e){}';
		$js .= $pr.'("'.$fid.'").value="'.$saveto.'";';
		$js .= 'window.parent.cDialog();';
	} else if($from == 'album' || $from == 'photo') {
		$js .= 'window.parent.getAlbum("'.$saveto.'", "'.$fid.'");';
		$js .= $from == 'photo' ? $pr.'("dform").submit();' : 'window.parent.cDialog();';
	} else if($from == 'editor') {
		$js .= 'window.parent.SetUrl("'.$saveto.'");';
		$js .= 'window.parent.GetE("frmUpload").reset();';
	} else if($from == 'attach') {
		$js .= 'window.parent.GetE("txtUrl").value="'.$saveto.'";';
		$js .= 'window.parent.window.parent.Ok();';
	} else if($from == 'file') {
		if($moduleid == 2 && $fid == 'chat') {
			$js .= $pr.'("word").value="'.$saveto.'";';
			$js .= 'window.parent.chat_send();';
		} else {
			$js .= $pr.'("'.$fid.'").value="'.$saveto.'";';
			if($module == 'down') $js .= 'window.parent.initd('.dround($do->file_size/1024/1024, 2).');';
		}
		$js .= 'window.parent.cDialog();';
	}
	dalert('', '', $js);
} else {
	$errmsg = 'Error(9)'.$do->errmsg;
	if($swfupload) exit(convert($errmsg, DT_CHARSET, 'utf-8'));
	dalert($errmsg, '', $errjs);
}
?>