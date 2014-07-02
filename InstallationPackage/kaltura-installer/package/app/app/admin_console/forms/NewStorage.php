<?php 
/**
 * @package Admin
 * @subpackage Partners
 */
class Form_NewStorage extends Infra_Form
{
	public function init()
	{
		$this->setAttrib('id', 'addNewStorage');
		$this->setDecorators(array(
			'FormElements', 
			array('HtmlTag', array('tag' => 'fieldset')),
			array('Form', array('class' => 'simple')),
		));
		
		$this->addElement('text', 'newPartnerId', array(
			'label'			=> 'Publisher ID:',
			'filters'		=> array('StringTrim'),
			'value'         => $this->filer_input,
		));
		
		// submit button
		$this->addElement('button', 'newStorage', array(
			'label'		=> 'Create New',
			'onclick'		=> "doAction('newStorage', $('#newPartnerId').val())",
			'decorators'	=> array('ViewHelper'),
		));
	}
}