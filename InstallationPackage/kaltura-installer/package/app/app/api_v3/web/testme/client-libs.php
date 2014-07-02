<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Kaltura - API v3 SDK - Client Libraries</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="Description" content="The Kaltura API SDK is a set of automatically generated API Client Libraries in native programming languages that simplifies development of applications that leverage the Kaltura Platform API" />
	<meta name="Keywords" content="Kaltura,Kaltura API,API,SDK,Client Libraries,client library,code library,library,video,testme" />
	<meta name="author" content="Kaltura Inc." />
	<link rel="stylesheet" type="text/css" href="css/main.css" />
	<link rel="stylesheet" type="text/css" href="css/sdk-page.css" />
	<link rel="stylesheet" type="text/css" href="css/jquery.tweet.css" media="all" />
	<script type="text/javascript" src="js/jquery-1.3.1.min.js"></script>
	<script type="text/javascript" src="js/jquery.tweet.js" type="text/javascript"></script>
	
	<script type='text/javascript'>
		$(document).ready(function(){
			$(".tweet").tweet({
				avatar_size: 8,
				count: 10,
				username: ["kaltura_api"],
				loading_text: "Loading form twitter...",
				refresh_interval: 60,
				template: function(i){return i["text"] + "<br>" + i["time"]}
			});
		});

		function gotoTestMe() {
			var isInIFrame = (window.location != window.parent.location) ? true : false;
			if (isInIFrame) {
				window.top.location = '/admin_console/index.php/index/testme';
			} else {
				window.location = '/api_v3/testme/index.php';
			}
		}
	</script>
</head>
<?php 
	require_once("../../bootstrap.php");
	
	//Get the generated clients summary
	$root = myContentStorage::getFSContentRootPath();
	$summaryData = file_get_contents("$root/content/clientlibs/summary.kinf");
	$summary = unserialize($summaryData);
	$schemaGenDate = $summary['generatedDate'];
	$apiVersion = $summary['apiVersion'];
	
	
	unset($summary['generatedDate']);
	unset($summary['apiVersion']);
?>
	<?php
	if(!isset($_REQUEST['hideMenu']) || !$_REQUEST['hideMenu'])
		{
			?>
			<body class="body-bg">
				<ul id="kmcSubMenu">
					<li><a href="index.php">Test Console</a></li>
					<li><a href="../testmeDoc/index.php">API Documentation</a></li>
					<li class="active"><a href="#">API Client Libraries</a></li>
				</ul>	
			<?php
		}
		else 
		{
			?>
			<body>
			<?php
		}
	?>
			
		<div id="content">
			<div id="header">
				<h1>Kaltura API SDK - Native Client Libraries</h1>
				<p>When developing applications that interact with the Kaltura API, it is best practice to use a native Client Library.</p>
				<p style="margin-bottom:5px;">Please download below the Client Library for the programming language of your choice.</p>
				<p>To learn how to use the client libraries and see example code implementations, use the <a href="#" onclick="gotoTestMe();">API Test Console</a>.<br>The Test Console generates sample code based on the API actions and parameters selected.</p>
			</div>
			<div id="boxs">
				<div id="downloads-box">
					<h2>Download latest client libraries</h2>
					<p class="graytext">API version: <?php echo $apiVersion; ?> | API Schema date: <?php echo $schemaGenDate; ?></p>
					<div id="download-buttons" >
						<div>
							<?php 
							$buttsInLine = 4;
							$current = 0;
							foreach($summary as $clientName)
							{
								?>
									<div class="download-button <?php echo $clientName; ?>-btn">
										<a href="http://<?php echo kConf::get('cdn_host'); ?>/content/clientlibs/<?php echo $clientName.'_'.$schemaGenDate; ?>.tar.gz" target="_blank" >
											<button class="download-btn" title="Single class <?php echo $clientName; ?> client library"></button>
										</a>
									</div>
								<?php 
								
								++$current;
								if(!($current % $buttsInLine))
									echo '<div class="clear"></div>';
							}
							
							?>
						</div>
						<div class="clear"></div>
					</div>
				</div>
				<div class="clear"></div>
			</div>
		</div>
	</body>
</html>