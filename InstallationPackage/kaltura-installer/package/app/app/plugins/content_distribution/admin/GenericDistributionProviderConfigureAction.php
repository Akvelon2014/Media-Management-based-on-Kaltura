<?php
/**
 * @package plugins.contentDistribution 
 * @subpackage admin
 */
class GenericDistributionProviderConfigureAction extends KalturaAdminConsolePlugin
{
	protected $client;
	
	public function __construct()
	{
		$this->action = 'configGenericDistributionProvider';
	}
	
	/**
	 * @return string - absolute file path of the phtml template
	 */
	public function getTemplatePath()
	{
		return realpath(dirname(__FILE__));
	}
	
	public function getRequiredPermissions()
	{
		return array(Kaltura_Client_Enum_PermissionName::SYSTEM_ADMIN_CONTENT_DISTRIBUTION_MODIFY);
	}
	
	public function saveProviderActions($providerId, Form_GenericProviderConfiguration $form)
	{
		$this->saveProviderAction($providerId, $form, 'submit', Kaltura_Client_ContentDistribution_Enum_DistributionAction::SUBMIT);
		$this->saveProviderAction($providerId, $form, 'update', Kaltura_Client_ContentDistribution_Enum_DistributionAction::UPDATE);
		$this->saveProviderAction($providerId, $form, 'delete', Kaltura_Client_ContentDistribution_Enum_DistributionAction::DELETE);
		$this->saveProviderAction($providerId, $form, 'fetchReport', Kaltura_Client_ContentDistribution_Enum_DistributionAction::FETCH_REPORT);
	}
	
	public function saveProviderAction($providerId, Form_GenericProviderConfiguration $form, $action, $actionType)
	{
		$actionObject = null;
		$contentDistributionPlugin = Kaltura_Client_ContentDistribution_Plugin::get($this->client);
		try
		{
			$actionObject = $contentDistributionPlugin->genericDistributionProviderAction->getByProviderId($providerId, $actionType);
		}
		catch(Exception $e){}
		
		$isNew = true;
		if($actionObject)
		{
			$isNew = false;
		}
		else
		{
			$actionObject = new Kaltura_Client_ContentDistribution_Type_GenericDistributionProviderAction();
			$actionObject->genericDistributionProviderId = $providerId;
			$actionObject->action = $actionType;
		}
			
		$actionObject = $form->getActionObject($actionObject, $action, $actionType);
		
		if(!$actionObject)
		{
			if(!$isNew)
				$contentDistributionPlugin->genericDistributionProviderAction->deleteByProviderId($providerId, $actionType);
				
			return;
		}
		
		$genericDistributionProviderAction = null;
		if($isNew)
		{
			$genericDistributionProviderAction = $contentDistributionPlugin->genericDistributionProviderAction->add($actionObject);
		}
		else 
		{
			// reset all readonly fields
			$actionObject->id = null;
			$actionObject->createdAt = null;
			$actionObject->updatedAt = null;
			$actionObject->genericDistributionProviderId = null;
			$actionObject->action = null;
			$actionObject->status = null;
			$actionObject->mrssTransformer = null;
			$actionObject->mrssValidator = null;
			$actionObject->resultsTransformer = null;
			
			$genericDistributionProviderAction = $contentDistributionPlugin->genericDistributionProviderAction->updateByProviderId($providerId, $actionType, $actionObject);
		}
		
		$genericDistributionProviderActionId = $genericDistributionProviderAction->id;
	
		$upload = new Zend_File_Transfer_Adapter_Http();
		$files = $upload->getFileInfo();
		
		if(count($files))
		{
			if(isset($files["mrssTransformer{$action}"]) && $files["mrssTransformer{$action}"]['size'])
			{
				$file = $files["mrssTransformer{$action}"];
				$contentDistributionPlugin->genericDistributionProviderAction->addMrssTransformFromFile($genericDistributionProviderActionId, $file['tmp_name']);
			}
		
			if(isset($files["mrssValidator{$action}"]) && $files["mrssValidator{$action}"]['size'])
			{
				$file = $files["mrssValidator{$action}"];
				$contentDistributionPlugin->genericDistributionProviderAction->addMrssValidateFromFile($genericDistributionProviderActionId, $file['tmp_name']);
			}
		
			if(isset($files["resultsTransformer{$action}"]) && $files["resultsTransformer{$action}"]['size'])
			{
				$file = $files["resultsTransformer{$action}"];
				$contentDistributionPlugin->genericDistributionProviderAction->addResultsTransformFromFile($genericDistributionProviderActionId, $file['tmp_name']);
			}
		}
	}
	
	public function doAction(Zend_Controller_Action $action)
	{
		$action->getHelper('layout')->disableLayout();
		
		$providerId = $this->_getParam('provider_id');
		$this->client = Infra_ClientHelper::getClient();
		$contentDistributionPlugin = Kaltura_Client_ContentDistribution_Plugin::get($this->client);
		$form = new Form_GenericProviderConfiguration();
		$form->setAction($action->view->url(array('controller' => 'plugin', 'action' => 'GenericDistributionProviderConfigureAction')));
		
		$request = $action->getRequest();
		
		$pager = new Kaltura_Client_Type_FilterPager();
		$pager->pageSize = 100;
		$flavorParamsResponse = $this->client->flavorParams->listAction(null, $pager);
			
		$action->view->errMessage = null;
		$action->view->form = '';
		
		try
		{
			if($providerId)
			{
				if ($request->isPost())
				{
					$form->isValid($request->getPost());
					$form->populate($request->getPost());
					$genericDistributionProvider = $form->getObject("Kaltura_Client_ContentDistribution_Type_GenericDistributionProvider", $request->getPost());
					$genericDistributionProvider->partnerId = null;
					$contentDistributionPlugin->genericDistributionProvider->update($providerId, $genericDistributionProvider);
					$this->saveProviderActions($providerId, $form);
				}
				else
				{
					$genericDistributionProvider = $contentDistributionPlugin->genericDistributionProvider->get($providerId);
					$form->populateFromObject($genericDistributionProvider);
					
					$optionalFlavorParamsIds = array();
					$requiredFlavorParamsIds = array();
					if(!is_null($genericDistributionProvider->optionalFlavorParamsIds) && strlen($genericDistributionProvider->optionalFlavorParamsIds))
						$optionalFlavorParamsIds = explode(',', $genericDistributionProvider->optionalFlavorParamsIds);
					if(!is_null($genericDistributionProvider->requiredFlavorParamsIds) && strlen($genericDistributionProvider->requiredFlavorParamsIds))
						$requiredFlavorParamsIds = explode(',', $genericDistributionProvider->requiredFlavorParamsIds);
						
					$form->addFlavorParamsFields($flavorParamsResponse, $optionalFlavorParamsIds, $requiredFlavorParamsIds);
					
					if(is_array($genericDistributionProvider->requiredThumbDimensions))
						foreach($genericDistributionProvider->requiredThumbDimensions as $dimensions)
							$form->addThumbDimensions($dimensions, true);
							
					if(is_array($genericDistributionProvider->optionalThumbDimensions))
						foreach($genericDistributionProvider->optionalThumbDimensions as $dimensions)
							$form->addThumbDimensions($dimensions, false);
						
					$form->addThumbDimensionsForm();
					$form->addProviderActions();
					$form->populateActions($genericDistributionProvider);
					$action->view->form = $form;
				}
			}
			else
			{
				if ($request->isPost())
				{
					$form->isValid($request->getPost());
					$form->populate($request->getPost());
					$genericDistributionProvider = $form->getObject("Kaltura_Client_ContentDistribution_Type_GenericDistributionProvider", $request->getPost());
					
					if(!$genericDistributionProvider->partnerId)
						$genericDistributionProvider->partnerId = 0;
					Infra_ClientHelper::impersonate($genericDistributionProvider->partnerId);
					$genericDistributionProvider->partnerId = null;
					$genericDistributionProvider = $contentDistributionPlugin->genericDistributionProvider->add($genericDistributionProvider);
					$this->saveProviderActions($genericDistributionProvider->id, $form);
					Infra_ClientHelper::unimpersonate();
				}
				else 
				{
					$form->addFlavorParamsFields($flavorParamsResponse);
					$form->addThumbDimensionsForm();
					$form->addProviderActions();
					$action->view->form = $form;
				}
			}
		}
		catch(Exception $e)
		{
			KalturaLog::err($e->getMessage() . "\n" . $e->getTraceAsString());
			$action->view->errMessage = $e->getMessage();
		}
	}
}

