<!DOCTYPE html><html><head><meta content='' name='description'>
<meta charset='UTF-8'>
<meta content='True' name='HandheldFriendly'>
<meta content='width=device-width, initial-scale=1.0' name='viewport'>
<title><?php echo $title?> - <?php echo $settings['site_name']?></title>
<?php $this->load->view ('header-meta');?>
</head>

<body id="startbbs">
<a id="top" name="top"></a>
<?php $this->load->view ('header'); ?>

<div id="wrap">
<div class="container" id="page-main">
<div class="row-fluid">
<div class='span8'>

<div class='box'>
<div class='cell'>
<a href="/" class="startbbs"><?php echo $settings['site_name']?></a> <span class="chevron">&nbsp;›&nbsp;</span> <?php echo $title?>
</div>
<div class='inner'>
<?php if(isset($_GET['p'])){?>
<form accept-charset="UTF-8" action="<?php echo site_url('user/resetpwd?p='.$p);?>" class="new_user" id="new_user" method="post"><div style="margin:0;padding:0;display:inline"><input name="utf8" type="hidden" value="&#x2713;" /><input name="_method" type="hidden" value="put" /><input name="authenticity_token" type="hidden" value="804LreQgz8POSg+5I2N1WCXiOU/uHFHOFj8z2u1mx3E=" /></div>
<input id="user_reset_password_token" name="user[reset_password_token]" type="hidden" value="2QqFMnysGArDfzAP9zLj" />
<table class='form'>
<tr>
<td class='left'>
<label for="user_password">新密码</label>
</td>
<td class='right'>
<input class="sl" id="user_password" name="password" size="30" type="password" />
</td>
</tr>
<tr>
<td class='left'>
<label for="user_password_confirmation">请再输入一次</label>
</td>
<td class='right'>
<input class="sl" id="user_password_confirmation" name="password_c" size="30" type="password" />
</td>
</tr>
<tr>
<td class='left'></td>
<td class='right'>
<input class="btn btn-small" name="commit" type="submit" value="继续" />
</td>
</tr>
</table>
</form>
<?} else {?>
<form accept-charset="UTF-8" action="<?php echo site_url('/user/findpwd');?>" class="simple_form form-horizontal" id="new_user" method="post" novalidate="novalidate"><div style="margin:0;padding:0;display:inline"><input name="utf8" type="hidden" value="&#x2713;" /><input name="authenticity_token" type="hidden" value="8Ar00xN+IjHuI56XmZr2DVRC8A6b46N9e9nD+9HrwZo=" /></div>
<div class="control-group string required"><label class="string required control-label" for="user_nickname">用户名</label><div class="controls"><input autofocus="autofocus" class="string required" id="user_nickname" name="username" size="50" type="text" /></div></div>
<div class="control-group email optional"><label class="email optional control-label" for="user_email">注册邮箱</label><div class="controls"><input class="string email optional" id="user_email" name="email" size="50" type="email" value="" /></div></div>
<div class='form-actions'>
<input class="btn btn-small btn-primary" name="commit" type="submit" value="<?php echo $title;?>" />
<div class='additional'>
24 小时内，至多可以<?php echo $title;?> 2 次。
</div>
</div>
</form>
<?}?>
</div>
</div>

</div>
<div class='span4' id='Rightbar'>
<?php $this->load->view('block/right_login');?>

<?php $this->load->view('block/right_ad');?>




</div>
</div></div></div>
<?php $this->load->view ('footer'); ?>