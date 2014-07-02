package com.kaltura.kdpfl.plugin.component
{
	import com.kaltura.kdpfl.model.ConfigProxy;
	import com.kaltura.kdpfl.model.ExternalInterfaceProxy;
	import com.kaltura.kdpfl.model.MediaProxy;
	import com.kaltura.kdpfl.model.SequenceProxy;
	import com.kaltura.kdpfl.model.type.NotificationType;
	import com.kaltura.kdpfl.view.media.KMediaPlayerMediator;
	import com.kaltura.vo.KalturaPlayableEntry;
	import com.omniture.AppMeasurement;
	
	import flash.display.DisplayObject;
	import flash.display.DisplayObjectContainer;
	import flash.events.Event;
	import flash.events.EventDispatcher;
	import flash.external.ExternalInterface;
	
	import mx.utils.ObjectProxy;
	
	import org.puremvc.as3.interfaces.INotification;
	import org.puremvc.as3.patterns.facade.Facade;
	import org.puremvc.as3.patterns.mediator.Mediator;

	public class OmnitureMediator extends Mediator
	{
		/**
		 * mediator name 
		 */		
		public static const NAME:String = "omnitureMediator";
		
		private var _ready:Boolean = false;
		private var _inSeek:Boolean = false;
		private var _inDrag:Boolean = false;
		private var _inFF:Boolean = false;
		private var _p25Once:Boolean = false;
		private var _p50Once:Boolean = false;
		private var _p75Once:Boolean = false;
		private var _p100Once:Boolean = false;
		private var _played:Boolean = false;
		private var _wasBuffering:Boolean = false;
		private var _hasSeeked:Boolean = false;
		private var _isReplay:Boolean = false;
		private var _mediaIsLoaded:Boolean=false;
		private var _fullScreen:Boolean=false;
		private var _normalScreen:Boolean=false;
		private var _lastSeek:Number=0;
		private var _playheadPosition:Number=0;
		private var _lastId : String = "";
		private var _isNewLoad : Boolean = false;
		public var _mediaName : String;

		private var _duration : Number;
		
		public var dynamicConfig:String;
		public var debugMode:String;
		
		public var charSet:String = "UTF-8";
		public var currencyCode:String = "USD";
		public var dc:String = "122";
		public var eventDispatcher : EventDispatcher = new EventDispatcher();
		
		public static const VIDEO_VIEW_EVENT:String = "videoViewEvent";
		public static const VIDEO_AUTOPLAY_EVENT:String = "videoAutoPlayEvent";
		public static const SHARE_EVENT:String = "shareEvent";
		public static const OPEN_FULL_SCREEN_EVENT:String = "openFullscreenEvent";
		public static const CLOSE_FULL_SCREEN_EVENT:String = "closefullscreenEvent";
		public static const SAVE_EVENT:String = "saveEvent";
		public static const REPLAY_EVENT:String = "replayEvent";
		public static const PERCENT_50:String = "percent50";
		public static const SEEK_EVENT:String = "seekEvent";
		public static const CHANGE_MEDIA_EVENT:String = "changeMediaEvent";
		public static const GOTO_CONTRIBUTOR_WINDOW_EVENT:String = "gotoContributorWindowEvent";
		public static const GOTO_EDITOR_WINDOW_EVENT:String = "gotoEditorWindowEvent";
		public static const PLAYER_PLAY_END_EVENT:String = "playerPlayEndEvent";
		public static const MEDIA_READY_EVENT:String = "mediaReadyEvent";
		public static const WATERMARK_CLICK:String = "watermarkClick";
		private	var eip:ExternalInterfaceProxy = Facade.getInstance().retrieveProxy("externalInterfaceProxy") as ExternalInterfaceProxy;
		
		private var _isReady:Boolean = false;
		private var _autoplayed:Boolean = false;
		 
		/**
		 * disable statistics 
		 */		
		public var statsDis : Boolean;
		
		/**
		 * Omniture account 
		 */		
		public var account  :String;
		
		/**
		 * Omniture visitor namespace 
		 */		
		public var visitorNamespace  :String;
		
		/**
		 * Omniture tracking server 
		 */		
		public var trackingServer  :String;
		
		/**
		 * entry percents to track 
		 */		
		public var trackMilestones  :String;
		/**
		 * Custom general events 
		 */
		public var customEvents:Array = new Array();
		
		public var s:AppMeasurement;
		private var cp:Object;
		
		/**
		 * Constructor. 
		 */		
		public function OmnitureMediator(customEvents:Array)
		{
			if (customEvents)
			{
				this.customEvents = customEvents;
			}
			super(NAME);
		}
		
		/**
		* External interface to extract the suit from the page	
		*/
		private function getOmniVar(omnivar:String):String {
			
			//TODO - pass this through the ExternalInterfaceProxy once it will 
			return ExternalInterface.call("function() { return "+omnivar+";}");
		}
		
		/**
		 * After all parameters are set - init the AppMeasurement object
		 */
		public function init():void
		{
			cp = Facade.getInstance().retrieveProxy("configProxy");
			var f:Object = Facade.getInstance();
			s = new AppMeasurement();
			//this feature allows to extract the configuration from the page
			if(dynamicConfig == "true")
			{
				eip.addCallback("omnitureKdpJsReady",omnitureKdpJsReady);
				return;
			}	else
			{
				if(visitorNamespace.indexOf("*")>-1)
					visitorNamespace.split("*").join(".");
				s.visitorNamespace = visitorNamespace;
				s.trackingServer = trackingServer;
				s.account = account;
				s.charSet = charSet;
				s.currencyCode = currencyCode;
			}
			prepareAppMeasurement();
		}
		public function omnitureKdpJsReady():void
		{
			s.visitorNamespace = getOmniVar("com.TI.Metrics.tcNameSpace");
			s.trackingServer = getOmniVar("com.TI.Metrics.tcTrackingServer");
			s.account = getOmniVar("com.TI.Metrics.tcReportSuiteID");
			s.charSet = getOmniVar("com.TI.Metrics.tcCharSet");
			s.currencyCode = getOmniVar("com.TI.Metrics.tcCurrencyCode");
			prepareAppMeasurement(); 
		}
		 
		/**
		 * Prepare the AppMeasurement attributes and turn on the flag that sais that this is ready. 
		 */
		private function prepareAppMeasurement():void
		{
			s.dc = dc;
			s.debugTracking = debugMode =="true" ? true : false ;
			s.trackLocal = true;
			s.Media.trackWhilePlaying = true;
			s.pageName = cp.vo.flashvars.referer;
			s.pageURL = cp.vo.flashvars.referer;
			s.Media.trackMilestones = trackMilestones;
			s.trackClickMap = true;
			if(cp.vo.kuiConf && cp.vo.kuiConf.name)
				s.Media.playerName= cp.vo.kuiConf.name;
			else
				s.Media.playerName= 'localPlayer';
			_isReady = true;
		}
		
		private function onAddedToStage(evt:Event):void
		{
			(viewComponent as DisplayObjectContainer).addChild(s);
		}
		
		/**
		 * Hook to the relevant KDP notifications
		 */
		override public function listNotificationInterests():Array
		{
			
			var notificationsArray:Array =  [
				NotificationType.HAS_OPENED_FULL_SCREEN,
				NotificationType.HAS_CLOSED_FULL_SCREEN,
				NotificationType.PLAYER_UPDATE_PLAYHEAD,
				NotificationType.PLAYER_READY,
				NotificationType.PLAYER_PLAYED,
				NotificationType.MEDIA_READY,
				NotificationType.DURATION_CHANGE,
				NotificationType.PLAYER_SEEK_START,
				NotificationType.PLAYER_SEEK_END,
				NotificationType.SCRUBBER_DRAG_START,
				NotificationType.SCRUBBER_DRAG_END,
				NotificationType.PLAYER_PAUSED,
				NotificationType.PLAYER_PLAY_END,
				NotificationType.CHANGE_MEDIA,
				"doGigya",
				"showAdvancedShare",
				"doDownload",
				"watermarkClick",
				NotificationType.DO_PLAY,
				NotificationType.DO_REPLAY,
				NotificationType.KDP_READY,
				NotificationType.DO_SEEK
												
											];

			notificationsArray = notificationsArray.concat(customEvents);
			return notificationsArray;
		}

		/**
		 * @inheritDocs
		 */		
		override public function handleNotification(note:INotification):void
		{
			if (statsDis) return;
			//trace("in handle notification: ", note.getName());
			var kc: Object =  facade.retrieveProxy("servicesProxy")["kalturaClient"];
			var data:Object = note.getBody();
			var sequenceProxy : SequenceProxy = facade.retrieveProxy(SequenceProxy.NAME) as SequenceProxy;
			switch(note.getName())
			{
				case NotificationType.PLAYER_READY:
				//this is useless since the event happens before the OmniturePlugin is ready for use
				//TOCHECK - do we neeed this ? ANswer - not at the current!  
				//sendGeneralNotification("widgetLoaded");
				break;
				
				case NotificationType.HAS_OPENED_FULL_SCREEN:
				 if(_fullScreen==false)
				 {
					sendGeneralNotification(OPEN_FULL_SCREEN_EVENT);
				 }
				_fullScreen=true;
				_normalScreen=false;	
				break;
				case NotificationType.HAS_CLOSED_FULL_SCREEN:
				if(_normalScreen==false)
				{
					sendGeneralNotification(CLOSE_FULL_SCREEN_EVENT);
				}
				_fullScreen=false;
				_normalScreen=true;	
				break;
				case "watermarkClick":
					sendGeneralNotification(WATERMARK_CLICK);
				break;
				case "playerPlayEnd":
					s.Media.close(cp.vo.kuiConf.name);
					sendGeneralNotification(PLAYER_PLAY_END_EVENT);
				break;
				case NotificationType.PLAYER_PLAYED:
					if (!sequenceProxy.vo.isInSequence && !_played )
					{
						
						s.Media.play(_mediaName,_playheadPosition);
						//seperate in case of autoplay:
						_played = true;
						if(cp.vo.flashvars.hasOwnProperty('autoPlay') && cp.vo.flashvars.autoPlay == "true" && !_autoplayed)
						{
							_autoplayed = true;
							sendGeneralNotification(VIDEO_AUTOPLAY_EVENT);
						} else
						sendGeneralNotification(VIDEO_VIEW_EVENT);
					}
				break; 
				case "doDownload":
					sendGeneralNotification(SAVE_EVENT);
				break; 
				case "doGigya":
				case "showAdvancedShare":
					sendGeneralNotification(SHARE_EVENT);
				break; 
				case NotificationType.MEDIA_READY:
					var bla:Object = (facade.retrieveProxy(MediaProxy.NAME) as MediaProxy).vo.entry;
				    if((facade.retrieveProxy(MediaProxy.NAME) as MediaProxy).vo.entry.id){
				    	if (_lastId != (facade.retrieveProxy(MediaProxy.NAME) as MediaProxy).vo.entry.id)
				    	{
							_mediaName = (facade.retrieveProxy(MediaProxy.NAME) as MediaProxy).vo.entry.name;
							
							var media:KalturaPlayableEntry = (facade.retrieveProxy(MediaProxy.NAME) as MediaProxy).vo.entry as KalturaPlayableEntry;
							if (media)
								_duration = media.duration;
				    		_played = false;
				    		_lastId = (facade.retrieveProxy(MediaProxy.NAME) as MediaProxy).vo.entry.id;
				    		_isNewLoad = true; 
							sendGeneralNotification(MEDIA_READY_EVENT);
							_p50Once = false;
							_autoplayed = false;
				    	}
				    	else
				    	{
				    		_isNewLoad = false;
				    		_lastSeek = 0;
				    	}
				    	_mediaIsLoaded=true;
				    }
					s.movieID = _lastId;
					if(media)
					{
						s.Media.close(cp.vo.kuiConf.name);
						s.Media.open(_mediaName,media.duration, cp.vo.kuiConf.name);
					}
					
					
				break;
							
				case NotificationType.DURATION_CHANGE:
					if(_isNewLoad){
						_hasSeeked = false;
					}
					return;
				break;	
				case NotificationType.PLAYER_SEEK_END:
					_inSeek = false;
					var kmpm:KMediaPlayerMediator = (facade.retrieveMediator(KMediaPlayerMediator.NAME) as KMediaPlayerMediator);
					trace("sent ",kmpm.player.currentTime);
					s.Media.play(_mediaName,kmpm.player.currentTime);
					//to see if we are passed 50% or not
					return;
				break;
						
				case NotificationType.SCRUBBER_DRAG_START:
					_inDrag = true;
					return;
				break;
				 		
				case NotificationType.SCRUBBER_DRAG_END:
					_inDrag = false;
					_inSeek = false;
					return;
				break;
								 		
				case NotificationType.PLAYER_UPDATE_PLAYHEAD:
					_playheadPosition = data as Number;
					// add a 50% notification 
					trace(_playheadPosition , (_duration/2))
					if(!_p50Once && _playheadPosition > (_duration/2))
					{
						_p50Once = true;
						sendGeneralNotification(PERCENT_50);
					}
				break;
				
				case NotificationType.KDP_READY:
				//Ready should not occur more than once
					if (_ready) return;
					_ready = true;
				break;
				case NotificationType.DO_SEEK:
					_lastSeek = Number(note.getBody());
					s.Media.stop(_mediaName,_playheadPosition);
					if(_inDrag && !_inSeek && !_isReplay)
					{
						sendGeneralNotification(SEEK_EVENT);
					}
					_inSeek = true;
					_hasSeeked = true;
					_isReplay = false;
			
				break;
				
				case NotificationType.DO_REPLAY:
					s.Media.open(_mediaName, _duration , cp.vo.kuiConf.name);
					sendGeneralNotification(REPLAY_EVENT); //TODO, fix the seek event being sent after replay. at the current this relies on the replay command happening before the seek  
					_isReplay = true;
				break;
				/*case "doPlay":
					s.Media.play(_mediaName,_playheadPosition);
				break;*/
				case NotificationType.PLAYER_PAUSED:
					var currentTime : Number = (facade.retrieveMediator(KMediaPlayerMediator.NAME) as KMediaPlayerMediator).player.currentTime;
					s.Media.stop(_mediaName,currentTime);
				break;
				
				default:
					//make sure we use the default only to the custom events
					for (var o:Object in customEvents)
					{
						if (note.getName() == customEvents[o].toString())
						{
							 sendGeneralNotification(note.getName())
							break;
						}
					}
				break;
				
			}
		}
		/**
		 * Send a general notification. let the code handle the logic 
		 * 
		 */
		private function sendGeneralNotification(evt:String):void
		{
			eventDispatcher.dispatchEvent(new Event(evt));
		}
		
		/**
		 * view component 
		 */		
		public function get view() : DisplayObject
		{
			return viewComponent as DisplayObject;
		}
		
		
		
		// unique 
		
	}
}