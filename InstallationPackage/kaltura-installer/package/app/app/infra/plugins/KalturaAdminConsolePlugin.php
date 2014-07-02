<?php
/**
 * @package infra
 * @subpackage Plugins
 */
abstract class KalturaAdminConsolePlugin
{
	/**
	 * @var string - keep null for top level
	 */
	protected $rootLabel = null;
	
	/**
	 * @var string - the action name
	 */
	protected $action = null;
	
	/**
	 * @var string - menu label
	 */
	protected $label = null;
	
	/**
	 * @var Zend_Controller_Action - the executed action
	 */
	protected $currentAction = null;
	
	/**
	 * @return string - absolute file path of the phtml template
	 */
	abstract public function getTemplatePath();
	
	abstract public function doAction(Zend_Controller_Action $action);
	
	abstract public function getRequiredPermissions();
	
	public function accessCheck($currentPermissions)
	{
		$requiredPermissions = $this->getRequiredPermissions();

		$legalAccess = true;
		
		foreach ($requiredPermissions as $permission)
		{
			if (!in_array($permission, $currentPermissions))
			{
				$legalAccess = false;
				break;
			}
		}
		
		return $legalAccess;
	}
	
	/**
	 * @return string - keep null for top level
	 */
	public function getNavigationRootLabel()
	{
		return $this->rootLabel;
	}
	
	/**
	 * @return string - the action name
	 */
	public function getNavigationActionName()
	{
		return $this->action;
	}
	
	/**
	 * Return null to exclude from navigation
	 * @return string - menu label
	 */
	public function getNavigationActionLabel()
	{
		return $this->label;
	}
	
	public function action(Zend_Controller_Action $action)
	{
		$this->currentAction = $action;
		$action->view->addBasePath($this->getTemplatePath());
		$this->doAction($action);
	}
	
    protected function _getParam($paramName, $default = null)
    {
        $value = $this->currentAction->getRequest()->getParam($paramName);
        if ((null == $value) && (null !== $default)) {
            $value = $default;
        }

        return $value;
    }
}