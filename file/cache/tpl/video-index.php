<?php defined('IN_DESTOON') or exit('Access Denied');?><?php include template('header');?>
<?php if($MOD['page_irec']) { ?>
<div class="m">
<div class="video">
<table cellpadding="0" cellspacing="0" width="100%">
<tr>
<td width="310" valign="top">
<div id="v_player"></div>
<div class="b10"></div>
<div id="v_title" class="f_b px14 t_c"></div>
</td>
<td valign="top">
<?php echo load('player.js');?>
<script type="text/javascript">
function Pl(u, p, a, i) {
Inner('v_player', player(u,300,250,p,a));
Inner('v_title', Dd('v_title_'+i).innerHTML);
}
</script>
<?php $tags=tag("moduleid=$moduleid&condition=status=3 and level=1&areaid=$cityid&order=".$MOD['order']."&length=30&pagesize=".$MOD['page_irec']."&template=null")?>
<div class="thumb">
<table width="100%">
<?php if(is_array($tags)) { foreach($tags as $i => $t) { ?>
<?php if($i%4==0) { ?><tr align="center"><?php } ?>
<td width="25%" valign="top"><img src="<?php echo $t['thumb'];?>" width="120" height="90" title="点击播放" class="c_p" onclick="Pl('<?php echo $t['video'];?>',<?php echo $t['player'];?>,1,<?php echo $i;?>);"/>
<ul>
<li id="v_title_<?php echo $i;?>"><a href="<?php echo $t['linkurl'];?>" target="_blank" title="<?php echo $t['alt'];?>" class="w"><?php echo $t['title'];?></a></li>
</ul>
<?php if($i==0) { ?><script type="text/javascript">Pl('<?php echo $t['video'];?>',<?php echo $t['player'];?>,0,<?php echo $i;?>);</script><?php } ?>
</td>
<?php if($i%4==3) { ?></tr><?php } ?>
<?php } } ?>
</table>
</div>
</td>
</tr>
</table>
</div>
<div class="b10"></div>
</div>
<?php } ?>
<div class="m">
<div class="m_l f_l">
<?php if($MOD['page_icat']) { ?>
<table cellpadding="0" cellspacing="0" width="100%">
<?php if(is_array($maincat)) { foreach($maincat as $i => $c) { ?>
<?php if($i%2==0) { ?><tr><?php } ?>
<td valign="top" width="330">
<div class="box_head"><span class="f_r"><a href="<?php echo $MOD['linkurl'];?><?php echo $c['linkurl'];?>">更多&raquo;</a></span><strong><?php echo $c['catname'];?></strong></div>
<div class="box_body"><div class="thumb"><?php echo tag("moduleid=$moduleid&condition=status=3&catid=".$c['catid']."&areaid=$cityid&order=".$MOD['order']."&length=20&width=120&height=90&pagesize=".$MOD['page_icat']."&cols=2&datetype=3&lazy=$lazy&template=list-video");?></div></div>
<div class="b10"></div>
</td>
<?php if($i%2==0) { ?><td>&nbsp;</td><?php } ?>
<?php if($i%2==1) { ?></tr><?php } ?>
<?php } } ?>
</table>
<?php } ?>
</div>
<div class="m_n f_l">&nbsp;</div>
<div class="m_r f_l">
<div class="box_head"><div><strong>按分类浏览</strong></div></div>
<div class="box_body">
<table width="100%" cellpadding="3">
<?php if(is_array($maincat)) { foreach($maincat as $k => $v) { ?>
<?php if($k%2==0) { ?><tr><?php } ?>
<td><a href="<?php echo $MOD['linkurl'];?><?php echo $v['linkurl'];?>"><?php echo set_style($v['catname'],$v['style']);?></a><?php if(!$cityid) { ?> <span class="f_gray px10">(<?php echo $v['item'];?>)</span><?php } ?>
</td>
<?php if($k%2==1) { ?></tr><?php } ?>
<?php } } ?>
</table>
</div>
<div class="b10 c_b"> </div>
<div class="box_head"><div><strong>点播排行</strong></div></div>
<div class="box_body">
<div class="rank_list"><?php echo tag("moduleid=$moduleid&condition=status=3 and addtime>$today_endtime-60*86400&areaid=$cityid&order=hits desc&pagesize=10");?></div>
</div>
</div>
</div>
<?php include template('footer');?>