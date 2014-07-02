<?php 
/**
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*
* Modified by Akvelon Inc.
* 2014-06-30
* http://www.akvelon.com/contact-us
*/

/**
 * @package Admin
 * @subpackage Partners
 */
class Form_PartnerCreate extends Infra_Form
{	
	public function init()
	{
		parent::init();
		
		// Set the method for the display form to POST
		$this->setMethod('post');
		$this->setName('new_account'); // form id
		$this->setAttrib('class', 'inline-form');
		
		$this->addElement('text', 'name', array(
			'label' => 'partner-create form name',
			'required' => true,
			'filters'		=> array('StringTrim'),
		));
		
		$this->addElement('text', 'company', array(
			'label' => 'partner-create form company',
			'filters'		=> array('StringTrim'),
		));
		
		$this->addElement('text', 'admin_email', array(
			'label' => 'partner-create form admin email',
			'required' => true,
			'validators' => array('PartnerEmail', 'EmailAddress'),
			'filters'		=> array('StringTrim'),
		));
		
		$this->addElement('text', 'phone', array(
			'label' => 'partner-create form admin phone',
			'required' => true,
			'filters'		=> array('StringTrim'),
		));
		
//		$this->addElement('select', 'partner_package', array(
//			'label'			=> 'partner-create form package',
//			'filters'		=> array('StringTrim'),
//			'required' 		=> true,
//		));
//
//		$this->addElement('select', 'partner_package_class_of_service', array(
//			'label'			=> 'Class of Service:',
//			'filters'		=> array('StringTrim'),
//		));
//
//		$this->addElement('select', 'vertical_clasiffication', array(
//			'label'			=> 'Vertical Clasiffication:',
//			'filters'		=> array('StringTrim'),
//		));
		
		$this->addElement('text', 'website', array(
			'label' => 'partner-create form url',
			'filters'		=> array('StringTrim'),
		));

		$this->addElement('text', 'wams_account_name', array(
			'label'			=> 'partner-create form wams account name',
			'filters'		=> array('StringTrim'),
			'ignore' 		=> true,
		));

		$this->addElement('text', 'wams_account_key', array(
			'label'			=> 'partner-create form wams account key',
			'filters'		=> array('StringTrim'),
			'ignore' 		=> true,
		));
		
		$this->addDisplayGroup(array('name', 'company', 'admin_email', 'phone', 'describe_yourself', 'partner_package', 'partner_package_class_of_service' , 'vertical_clasiffication'), 'partner_info', array(
			'legend' => 'Publisher Info',
			'decorators' => array(
				'Description', 
				'FormElements', 
				array('Fieldset'),
			)
		));


		$this->addDisplayGroup(array('wams_account_name', 'wams_account_key'), 'wams_account_info', array(
			'legend' => 'Microsoft Azure Account Info',
			'decorators' => array(
				'Description',
				'FormElements',
				array('Fieldset'),
			)
		));

		$this->addDisplayGroup(array('website', 'content_categories', 'adult_content'), 'website_info', array(
			'legend' => 'Website Info',
			'decorators' => array(
				'Description', 
				'FormElements', 
				array('Fieldset'),
			)
		));
		
		$this->addElement('button', 'submit', array(
			'label' => 'partner-create form create',
			'type' => 'submit',
			'decorators' => array('ViewHelper')
		));
		
		
		$this->addDisplayGroup(array('submit'), 'buttons1', array(
			'decorators' => array(
				'FormElements', 
				array('HtmlTag', array('tag' => 'div', 'class' => 'buttons')),
			)
		));
	}
}