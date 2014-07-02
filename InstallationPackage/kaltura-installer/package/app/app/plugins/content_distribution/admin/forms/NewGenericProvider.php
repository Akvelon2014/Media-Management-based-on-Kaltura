<?php 
/**
 * @package plugins.contentDistribution 
 * @subpackage admin
 */
class Form_NewGenericProvider extends Infra_Form
{
	public function init()
	{
		$this->setAttrib('id', 'addNewGenericProvider');
		$this->setDecorators(array(
			'FormElements', 
			array('HtmlTag', array('tag' => 'fieldset')),
			array('Form', array('class' => 'simple')),
		));
		
				
		// submit button
		$this->addElement('button', 'newGenericProvider', array(
			'label'		=> 'Create New',
			'onclick'		=> "doAction('newGenericProvider')",
			'decorators'	=> array('ViewHelper'),
		));
	}
}