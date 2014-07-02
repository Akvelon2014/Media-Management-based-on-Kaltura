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

package com.kaltura.kdpfl.plugin.component
{
	import com.adobe.images.JPGEncoder;
	import com.kaltura.KalturaClient;
	import com.kaltura.commands.baseEntry.BaseEntryUpdateThumbnailJpeg;
	import com.kaltura.commands.media.MediaUpdateThumbnailFromSourceEntry;
	import com.kaltura.commands.thumbAsset.ThumbAssetAddFromImage;
	import com.kaltura.commands.thumbAsset.ThumbAssetGenerate;
	import com.kaltura.commands.thumbAsset.ThumbAssetSetAsDefault;
	import com.kaltura.events.KalturaEvent;
	import com.kaltura.kdpfl.model.type.NotificationType;
	import com.kaltura.kdpfl.view.controls.AlertMediator;
	import com.kaltura.net.KalturaCall;
	import com.kaltura.types.KalturaMediaType;
	import com.kaltura.vo.KalturaEntryContextDataResult;
	import com.kaltura.vo.KalturaMediaEntry;
	import com.kaltura.vo.KalturaMixEntry;
	import com.kaltura.vo.KalturaThumbAsset;
	import com.kaltura.vo.KalturaThumbParams;
	import com.yahoo.astra.fl.managers.AlertManager;
	
	import fl.controls.Button;
	
	import flash.display.BitmapData;
	import flash.display.DisplayObject;
	import flash.geom.Matrix;
	import flash.ui.Mouse;
	import flash.ui.MouseCursor;
	import flash.utils.ByteArray;
	
	import org.puremvc.as3.interfaces.INotification;
	import org.puremvc.as3.patterns.mediator.Mediator;

	public class CaptureThumbnailMediator extends Mediator
	{
		///////////////////////// Variables ////////////////////////
		public static const NAME:String = "CaptureThumbnailMediator";
	
		private var bitmapData:BitmapData;
		/////////////////////////////////////////////
		
		public function CaptureThumbnailMediator(mediatorName:String=null, viewComponent:Object=null)
		{
			super(NAME, viewComponent);
		}
		
		override public function listNotificationInterests():Array
		{
			return [
					 "captureThumbnail"
					];
		} 
		
		override public function handleNotification(note:INotification):void
		{
			switch(note.getName())
			{
				case "captureThumbnail":
					var servicesProxy : Object =  facade.retrieveProxy("servicesProxy");
					var kc : KalturaClient = servicesProxy.kalturaClient;
					var mediaProxy : Object = facade.retrieveProxy("mediaProxy");
					var player : Object = facade.retrieveMediator( "kMediaPlayerMediator" )["player"];
					var playerView : DisplayObject;
					if( mediaProxy.vo.entry is KalturaMediaEntry &&
						((mediaProxy.vo.entry as KalturaMediaEntry).mediaType == KalturaMediaType.IMAGE || 
					    (mediaProxy.vo.entry as KalturaMediaEntry).mediaType == KalturaMediaType.AUDIO ||
						(mediaProxy.vo.entry as KalturaMediaEntry).mediaType == KalturaMediaType.LIVE_STREAM_FLASH))
					{	
						sendNotification("alert",{message:viewComponent.capture_thumbnail_not_supported,title:viewComponent.capture_thumbnail_success_title});
					}
					else
					{
						if( player && player.displayObject)
							 playerView = facade.retrieveMediator( "kMediaPlayerMediator" )["player"].displayObject;
						else 
							return; //can't capture the player if the view is unreachable
						
	
						var videoWidth : Number = playerView["videoWidth"];
						var videoHeight : Number = playerView["videoHeight"]
	
						bitmapData  = new BitmapData( videoWidth  , videoHeight , false , 0x000000);
						var a : Number = videoWidth/(playerView.width/playerView.scaleX); // videoWidth/unscaledWidth
						var d : Number = videoHeight/(playerView.height/playerView.scaleY);// videoHeight/unscaledHeight
						var matrix : Matrix = new Matrix( a , 0 , 0 , d );
						try {
						bitmapData.draw( playerView , matrix , null , null, null , true);
						}
						catch ( e:SecurityError ){
							if (mediaProxy.vo.entry is KalturaMixEntry){
								sendNotification("alert",{message:viewComponent.capture_thumbnail_not_supported,title:viewComponent.capture_thumbnail_success_title});
								return;
							}
						}				        
						var updateThumbnailJpeg : KalturaCall;
						if (mediaProxy.vo.entry is KalturaMixEntry)
						{
							var encoder : JPGEncoder = new JPGEncoder(85);
							var thumbnail : ByteArray = encoder.encode( bitmapData );
							updateThumbnailJpeg  = new ThumbAssetAddFromImage (mediaProxy.vo.entry.id, thumbnail);
						}
						else
						{
							var thumbParams : KalturaThumbParams = new KalturaThumbParams();
							thumbParams.videoOffset = player.currentTime;
							thumbParams.quality = 75;
							updateThumbnailJpeg = new ThumbAssetGenerate(mediaProxy.vo.entry.id, thumbParams);
						}
						updateThumbnailJpeg.addEventListener( "complete" , result );
						updateThumbnailJpeg.addEventListener( "failed" , fault );
						kc.post( updateThumbnailJpeg );
						
						//sendNotification( NotificationType.ENABLE_GUI ,{guiEnabled: false , enableType:'full'} );
						AlertManager.showButtonIfEmpty = false;
						sendNotification( NotificationType.ALERT, {message: "Please wait...", title: "Processing"} );
					}
				break;
			}	
		}

		
		private function result( data : Object ) : void
		{
			onServiceReturn ()
			sendNotification("thumbnailSaved");
			sendNotification( NotificationType.REMOVE_ALERTS );
			AlertManager.showButtonIfEmpty = true;
			bitmapData.dispose();
			if ((viewComponent as captureThumbnailPluginCode).shouldSetAsDefault == "true")
			{
				var servicesProxy : Object =  facade.retrieveProxy("servicesProxy");
				var kc : KalturaClient = servicesProxy.kalturaClient;
				var setThumbnailAsDefault : ThumbAssetSetAsDefault = new ThumbAssetSetAsDefault((data.data as KalturaThumbAsset).id );
				setThumbnailAsDefault.addEventListener(KalturaEvent.COMPLETE, setAsDefaultResult);
				setThumbnailAsDefault.addEventListener( KalturaEvent.FAILED, setAsDefaultFault );
				kc.post(setThumbnailAsDefault);
			}
			else
			{
				sendNotification("alert",{message:viewComponent.capture_thumbnail_success,title:viewComponent.capture_thumbnail_success_title});
			}
		}
		
		private function fault( data : Object ) : void
		{
			onServiceReturn ()
			sendNotification("thumbnailFailed");
			sendNotification("alert",{message:viewComponent.error_capture_thumbnail,title:viewComponent.error_capture_thumbnail_title});
		}
		
		private function setAsDefaultResult (e : KalturaEvent) : void
		{
			onServiceReturn ()
			sendNotification("alert",{message:viewComponent.set_as_default_success,title:viewComponent.capture_thumbnail_success_title});
			trace('set as default success!');
		}
		
		private function setAsDefaultFault (e : KalturaEvent ) : void
		{
			onServiceReturn ()
			trace('set as default failed!');
			switch (e.error.errorCode)
			{
				case "SERVICE_FORBIDDEN":
					sendNotification("alert",{message:(viewComponent as captureThumbnailPluginCode).capture_thumbnail_service_forbidden,title:viewComponent.capture_thumbnail_service_forbidden_title});
					break;
				default:
					sendNotification("alert",{message:(viewComponent as captureThumbnailPluginCode).error_capture_thumbnail,title:viewComponent.error_capture_thumbnail_title});
					break;
			}
		}
		
		private function onServiceReturn () : void
		{
			AlertManager.showButtonIfEmpty = true;
			sendNotification( NotificationType.ENABLE_GUI ,{guiEnabled: true , enableType:'full'} );
			sendNotification( NotificationType.REMOVE_ALERTS );
		}
	}
}