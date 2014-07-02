package com.kaltura.kmc.modules.content.events {
	import com.adobe.cairngorm.control.CairngormEvent;
	
	import mx.collections.ArrayCollection;

	/**
	 * this class represents an event concerning a group of entries
	 */
	public class EntriesEvent extends CairngormEvent {
		
		/**
		 * remember the given entries on the model 
		 */		
		public static const SET_SELECTED_ENTRIES:String = "content_setSelectedEntries";
		
		/**
		 * remember the selected entries to add them to a new manual playlist after creation 
		 */
		public static const SET_SELECTED_ENTRIES_FOR_PLAYLIST:String = "content_setSelectedEntriesForPlaylist";
		
		/**
		 * remember the selected entries to add them to a new category after creation 
		 */		
		public static const SET_SELECTED_ENTRIES_FOR_CATEGORY:String = "content_setSelectedEntriesForCategory";
		
		public static const UPDATE_ENTRIES:String = "content_updateEntries";
		public static const UPDATE_PLAYLISTS:String = "content_updatePlaylists";
		public static const DELETE_ENTRIES:String = "content_deleteEntries";
		
		/**
		 * reset the value of the attribute on the model that holds selected entries categories
		 */
		public static const RESET_SELECTED_ENTRIES_CATEGORIES:String = "content_resetSelectedEntriesCategories";
		
		/**
		 * get the categories to which the selected entries are assigned 
		 */
		public static const GET_SELECTED_ENTRIES_CATEGORIES:String = "content_getSelectedEntriesCategories";
		
		/**
		 * add the given categories to the selected entries
		 * event.data is categories to add (KalturaCategory objects)
		 */
		public static const ADD_CATEGORIES_ENTRIES:String = "content_addCategoriesEntries";
		
		/**
		 * add the given categories to on the fly entries
		 * event.data is category to add (KalturaCategory objects)
		 */
		public static const ADD_ON_THE_FLY_CATEGORY:String = "content_addOnTheFlyCategory";
		
		/**
		 * remove the given categories from the selected entries
		 * event.data is categories to remove (KalturaCategory objects)
		 */
		public static const REMOVE_CATEGORIES_ENTRIES:String = "content_removeCategoriesEntries";
		
		/**
		 * set the owner of the given entries to the given user
		 * event.data is userId
		 * event.entries are entries to update
		 */
		public static const SET_ENTRIES_OWNER:String = "content_setEntriesOwner";
		
		
		/**
		 * add new entry. event.data is the entry to add
		 * */
		public static const ADD_ENTRY:String = "content_addEntry";

		
		
		
		/**
		 * entries relevant for this event.
		 * each entry is <code>KalturaBaseEntry</code>
		 */
		private var _entries:ArrayCollection;

		/**
		 * whether to close drilldown window after action is complete
		 */
		private var _closeWindow:Boolean;

		/**
		 * whether to display updated entry or next entry
		 */
		private var _displayNextEntry:Boolean;


		/**
		 * Constructor.
		 * @param type		event type
		 * @param entries	entries this event effects
		 * @param closeWindow whether to close drilldown
		 * @param bubbles	should the event bubble
		 * @param cancelable	should the event be cancelable
		 *
		 */
		public function EntriesEvent(type:String, entries:ArrayCollection = null, closeWindow:Boolean = true, 
							displayNextEntry:Boolean = false, bubbles:Boolean = false, cancelable:Boolean = false) {
			super(type, bubbles, cancelable);
			this._entries = entries;
			this._closeWindow = closeWindow;
			this._displayNextEntry = displayNextEntry;
		}


		public function get entries():ArrayCollection {
			return _entries;
		}


		public function get closeWindow():Boolean {
			return _closeWindow;
		}


		public function get displayNextEntry():Boolean {
			return _displayNextEntry;
		}
	}
}