<?php
/**
 * @package infra
 * @subpackage Plugins
 */
abstract class KalturaPlugin implements IKalturaPlugin
{
	public function getInstance($interface)
	{
		if($this instanceof $interface)
			return $this;
			
		return null;
	}
}