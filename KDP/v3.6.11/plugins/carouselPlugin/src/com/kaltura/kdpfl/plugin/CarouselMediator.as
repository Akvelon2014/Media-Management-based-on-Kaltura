package com.kaltura.kdpfl.plugin
{
	import flash.events.Event;
	
	import org.puremvc.as3.interfaces.INotification;
	import org.puremvc.as3.patterns.mediator.Mediator;

	public class CarouselMediator extends Mediator
	{
		public static const NAME:String = "carouselMediator";
		
		public function CarouselMediator(viewComponent:Object = null)
		{
			super(NAME, viewComponent);
			carousel.addEventListener(Event.CHANGE, onCarouselChange);
		}
		
		override public function handleNotification( note:INotification ):void
		{
			super.handleNotification(note);
			switch( note.getName() )
			{
				case "carouselPrev":
					onCarouselPrev(note);
					break;
				case "carouselNext":
					onCarouselNext(note);
					
					if (carousel.selectedIndex + (this.viewComponent as CarouselPluginCode).numOfSlidesPerScreen > carousel.dataProvider.length)
					{
						sendNotification("updateGalleryDataProvider");
					}
					break;
				case "changeVideoPresentation":
					(viewComponent as CarouselPluginCode).carousel.selectedIndex = 0;
					break;
				case "pptWidgetPrevCarouselSlide":
					onCarouselPrev(note);
					break;
				case "pptWidgetNextCarouselSlide":
					onCarouselNext(note);
					break;
			}
		}
		
		override public function listNotificationInterests():Array
		{
			var notifications:Array = ["carouselPrev","carouselNext","pptWidgetNextCarouselSlide","pptWidgetPrevCarouselSlide","changeVideoPresentation"];
			return notifications;
		}
		
		protected function get carousel():KCarousel
		{
			return viewComponent.carousel as KCarousel;
		}
		
		protected function onCarouselPrev(note:INotification):void
		{
			(this.carousel.layoutRenderer as KSlidingCarouselRenderer).prevSlide();
		}
		
		protected function onCarouselNext(note:INotification):void
		{
			(this.carousel.layoutRenderer as KSlidingCarouselRenderer).nextSlide();
		}
		
		protected function onCarouselChange(event:Event):void
		{
			sendNotification("pptWidgetGoToSlide", carousel.selectedItem.frameNum);
//			sendNotification("pptWidgetGoToSlide", carousel.selectedIndex + 1);
			
			if (carousel.selectedItem.offset)
			{
				sendNotification ("doSeek" , carousel.selectedItem.offset/1000);
			}
		}
	}
}