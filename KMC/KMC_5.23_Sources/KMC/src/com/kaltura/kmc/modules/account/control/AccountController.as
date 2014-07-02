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

package com.kaltura.kmc.modules.account.control
{
	import com.adobe.cairngorm.control.FrontController;
	import com.kaltura.kmc.modules.account.command.*;
	import com.kaltura.kmc.modules.account.events.*;
	
	
	public class AccountController extends FrontController
	{
		public function AccountController()
		{
			initializeCommands();
		}
		
		
		/**
		 * add all commands 
		 */		
		public function initializeCommands() : void
		{
			// Partner Events
			addCommand( PartnerEvent.GET_PARTNER_INFO , GetPartnerInfoCommand );
			addCommand( PartnerEvent.UPDATE_PARTNER , UpdatePartnerCommand );
			// user events
			addCommand( UserEvent.LIST_USERS, ListUsersCommand );
			
			// entry event
			addCommand(EntryEvent.GET_DEFAULT_ENTRY, GetDefaultEntryCommand);
			addCommand(EntryEvent.RESET_DEFAULT_ENTRY, ResetDefaultEntryCommand);
			
			
			// Kaltura Events
			 addCommand( ContactEvent.CONTACT_US , ContactSalesForceCommand );
			
			// Access Control Events
			addCommand(AccessControlEvent.ACCOUNT_ADD_NEW_ACCESS_CONTROL_PROFILE, AddNewAccessControlProfileCommand);
			addCommand(AccessControlEvent.ACCOUNT_DELETE_ACCESS_CONTROL_PROFILES, DeleteAccessControlProfilesCommand);
			addCommand(AccessControlEvent.ACCOUNT_LIST_ACCESS_CONTROLS_PROFILES, ListAccessControlsCommand);
			addCommand(AccessControlEvent.ACCOUNT_UPDATE_ACCESS_CONTROL_PROFILE, UpdateAccessControlProfileCommand);
			
			// Conversion Profile Events
			addCommand(ConversionSettingsEvent.ADD_CONVERSION_PROFILE, AddNewConversionProfileCommand);
			addCommand(ConversionSettingsEvent.DELETE_CONVERSION_PROFILE, DeleteConversionProfileCommand);
			addCommand(ConversionSettingsEvent.LIST_CONVERSION_PROFILES_AND_FLAVOR_PARAMS, ListConversionProfilesAndFlavorParamsCommand);
			addCommand(ConversionSettingsEvent.LIST_CONVERSION_PROFILES, ListConversionProfilesCommand);
			addCommand(ConversionSettingsEvent.LIST_FLAVOR_PARAMS, ListFlavorsParamsCommand);
			//addCommand(ConversionSettingsEvent.LIST_STORAGE_PROFILES, ListStorageProfilesCommand);
			addCommand(ConversionSettingsEvent.MARK_FLAVORS, MarkFlavorsCommand);
			addCommand(ConversionSettingsEvent.UPDATE_CONVERSION_PROFILE, UpdateConversionProfileCommand);
			addCommand(ConversionSettingsEvent.SET_AS_DEFAULT_CONVERSION_PROFILE, SetAsDefaultConversionProfileCommand);
			
			//metadata profile events
			addCommand(MetadataProfileEvent.LIST , ListMetadataProfileCommand);
			addCommand(MetadataProfileEvent.ADD , AddMetadataProfileCommand);
			addCommand(MetadataProfileEvent.UPDATE , UpdateMetadataProfileCommand);
			addCommand(MetadataProfileEvent.SELECT , UpdateSelectedMetadataProfileCommand);
			addCommand(MetadataProfileEvent.DELETE , DeleteMetadataProfileCommand);
			//metadata field events
			addCommand(MetadataFieldEvent.ADD , AddMetadataFieldCommand);
			addCommand(MetadataFieldEvent.DELETE , DeleteMetadataFieldCommand);
			addCommand(MetadataFieldEvent.EDIT , EditMetadataFieldCommand);
			addCommand(MetadataFieldEvent.REORDER , ReorderMetadataFieldCommand );
			
			
			// integration
			addCommand(IntegrationEvent.LIST_CATEGORIES_WITH_PRIVACY_CONTEXT , ListCategoriesCommand);
			addCommand(IntegrationEvent.UPDATE_CATEGORY , UpdateCategoryCommand);
			
		}
	}
}