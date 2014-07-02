<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<base href="<?php echo requestUtils::getRequestHost() ?>/" />

<?php echo include_http_metas() ?>
<?php echo include_metas() ?>

<?php echo include_title() ?>

<?php if (@$extraHead) echo $extraHead; ?>

<script type="text/javascript" src="<?php echo requestUtils::getCdnHost( requestUtils::getRequestProtocol() ); ?>/lib/js/jquery_v1.4.2.min.js"></script>
<script type="text/javascript" src="<?php echo requestUtils::getCdnHost( requestUtils::getRequestProtocol() ); ?>/lib/js/swfobject_v2.2.js"></script>

</head>
<body>
 <div id="wrap">
 <?php echo $sf_content ?>
 </div>
<?php if( ! kConf::get('kmc_disable_analytics') ){ ?>
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
try {
var pageTracker = _gat._getTracker("<?php echo kConf::get('ga_account'); ?>");
pageTracker._trackPageview();
} catch(err) {}
</script>
<?php } ?>
</body>
</html>