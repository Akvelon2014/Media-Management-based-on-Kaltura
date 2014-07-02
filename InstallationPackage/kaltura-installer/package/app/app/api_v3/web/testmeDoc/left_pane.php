<?php 

	function compareTypeNames($obj1, $obj2) 
	{
		return strcmp($obj1->getType(), $obj2->getType());
	}
	
	function compareServiceNames($obj1, $obj2)
	{
		return strcmp($obj1->serviceId, $obj2->serviceId);
	}

	$config = new Zend_Config_Ini("../../config/testme.ini", null, array('allowModifications' => true));
	$config = KalturaPluginManager::mergeConfigs($config, 'testme', false);
	$indexConfig = $config->get('testmedoc');
	
	$include = $indexConfig->get("include");
	$exclude = $indexConfig->get("exclude");
	$excludePaths = $indexConfig->get("excludepaths");
	$additional = $indexConfig->get("additional");
		
	$clientGenerator = new DummyForDocsClientGenerator();
	$clientGenerator->setIncludeOrExcludeList($include, $exclude, $excludePaths);
	echo $include;
	$clientGenerator->setAdditionalList($additional);
	$clientGenerator->load();
	
	$list = array();
	$services = $clientGenerator->getServices();
	foreach($services as $serviceId => $serviceReflector)
	{
	    /* @var $serviceReflector KalturaServiceActionItem */
		$actions = $serviceReflector->actionMap;
		foreach($actions as $actionId=>&$actionCallback) // we need only the keys
			$actionCallback = null;
		$list[$serviceId] = $actions;
	}
	$clientGenerator->setIncludeList($list);
	$enums = $clientGenerator->getEnums();
	$stringEnums = $clientGenerator->getStringEnums();
	$arrays = $clientGenerator->getArrays();
	$filters = $clientGenerator->getFilters();
	$objects = $clientGenerator->getObjects();

	// sort alphabetically	
	usort($services, 'compareServiceNames');
	usort($objects, 'compareTypeNames');
	usort($filters, 'compareTypeNames');
	usort($arrays, 'compareTypeNames');
	usort($enums, 'compareTypeNames');
	usort($stringEnums, 'compareTypeNames');
		
?>
	<div class="left">
		<div class="left-content">
			<div id="general">
				<h2>General</h2>
				<ul>
					<li><a href="?page=overview">Overview</a></li>
					<li><a href="?page=terminology">Terminology</a></li>
					<li><a href="?page=inout">Request/Response structure</a></li>
					<li><a href="?page=multirequest">multiRequest</a></li>
					<li><a href="?page=notifications">Notifications</a></li>
				</ul>
			</div>

			<div id="services">
				<h2>Services</h2>
				<ul class="services">
				<?php foreach($services as $serviceReflector): ?>
					<?php 
					    /* @var $serviceReflector KalturaServiceActionItem */
					    $serviceId = $serviceReflector->serviceId;
						$serviceName = $serviceReflector->serviceInfo->serviceName;
						$actions = $serviceReflector->actionMap;
						$deprecated = $serviceReflector->serviceInfo->deprecated ? " (deprecated)" : "";
					?>
					<li class="service" id="service_<?php echo $serviceId; ?>">
						<a href="?service=<?php echo $serviceId; ?>"><?php echo $serviceName.$deprecated; ?></a>
						<ul class="actions">
						<?php foreach($actions as $actionId => $actionReflector): ?>
							<li class="action"><a href="?service=<?php echo $serviceId; ?>&action=<?php echo $actionId; ?>"><?php echo $actionReflector->getActionName()."Action";?></a></li>
						<?php endforeach; ?>
						</ul>
					</li>
				<?php endforeach; ?>
				</ul>
			</div>
			
			<div id="objects">
				<h2>General Objects</h2>
				<ul>
				<?php foreach($objects as $object): ?>
					<li id="object_<?php echo $object->getType(); ?>">
						<a href="?object=<?php echo $object->getType(); ?>"><?php echo $object->getType(); ?></a>
					</li>
				<?php endforeach; ?>
				</ul>
			</div>
			
			<div id="objects">
				<h2>Filter Objects</h2>
				<ul>
				<?php foreach($filters as $object): ?>
					<li id="object_<?php echo $object->getType(); ?>">
						<a href="?object=<?php echo $object->getType(); ?>"><?php echo $object->getType(); ?></a>
					</li>
				<?php endforeach; ?>
				</ul>
			</div>
			
			<div id="objects">
				<h2>Array Objects</h2>
				<ul>
				<?php foreach($arrays as $object): ?>
					<li id="object_<?php echo $object->getType(); ?>">
						<a href="?object=<?php echo $object->getType(); ?>"><?php echo $object->getType(); ?></a>
					</li>
				<?php endforeach; ?>
				</ul>
			</div>
			
			<div id="enums">
				<h2>Enums</h2>
				<ul>
				<?php foreach($enums as $enum): ?>
					<li id="object_<?php echo $enum->getType(); ?>">
						<a href="?object=<?php echo $enum->getType(); ?>" name="<?php echo $enum->getType(); ?>"><?php echo $enum->getType(); ?></a>
					</li>
				<?php endforeach; ?>
				</ul>
			</div>
			
			<div id="enums">
				<h2>String Enums Constants</h2>
				<ul>
				<?php foreach($stringEnums as $stringEnum): ?>
					<li id="object_<?php echo $stringEnum->getType(); ?>">
						<a href="?object=<?php echo $stringEnum->getType(); ?>" name="<?php echo $stringEnum->getType(); ?>"><?php echo $stringEnum->getType(); ?></a>
					</li>
				<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</div>
