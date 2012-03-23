<? if(!defined('IN_DC')) exit('Access Denied'); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html><head>
<title>后台管理系统 -- <?php echo $DC[sitename];?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<link rel="stylesheet" type="text/css" href="<?php echo $_G['staticurl'];?>images/admin/admin.css">
<link rel="stylesheet" type="text/css" href="<?php echo $_G['staticurl'];?>images/admin/treeview.css">
<link rel="stylesheet" type="text/css" href="<?php echo $_G['staticurl'];?>images/admin/new/style.css">
<script type="text/javascript" src="<?php echo $_G['staticurl'];?>images/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo $_G['staticurl'];?>images/js/common.js"></script>
<script type="text/javascript" src="<?php echo $_G['staticurl'];?>images/js/treeview.js"></script>
<script type="text/javaScript" src="<?php echo $_G['staticurl'];?>images/js/admin.js"></script>
<script type="text/javascript" src="<?php echo $_G['staticurl'];?>images/js/validator.js"></script>
<script type="text/javascript" src="<?php echo $_G['staticurl'];?>images/js/form.js"></script>
<script type="text/javascript" src="<?php echo $_G['staticurl'];?>images/js/jqModal.js"></script>
<script type="text/javascript" src="<?php echo $_G['staticurl'];?>images/js/jqDnR.js"></script>
</head>
<body <?php echo $bodytag;?>>