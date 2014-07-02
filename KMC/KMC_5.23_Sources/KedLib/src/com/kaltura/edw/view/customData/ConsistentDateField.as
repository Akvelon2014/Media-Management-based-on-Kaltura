package com.kaltura.edw.view.customData
{
	import mx.events.CalendarLayoutChangeEvent;
	
	public class ConsistentDateField extends KDateField
	{
		private var _nonChangedDate:Date;
		
		public function ConsistentDateField()
		{
			super();
			addEventListener(CalendarLayoutChangeEvent.CHANGE, onDateChange,false , int.MAX_VALUE);
		}
		
		protected function onDateChange(event:CalendarLayoutChangeEvent):void
		{
			_nonChangedDate = null;
		}		
		
		override public function set selectedDate(value:Date):void{
			super.selectedDate = value;
			if (value.time != super.selectedDate.time){
				_nonChangedDate = value;
			} else {
				_nonChangedDate = null;
			}
		}
		
		override public function get selectedDate():Date{
			if (_nonChangedDate != null){
				return _nonChangedDate;
			} else {
				return super.selectedDate;
			}
		}
	}
}