package com.kaltura.kdpfl.view.media
{
	import com.kaltura.kdpfl.component.IComponent;
	import com.kaltura.kdpfl.util.URLUtils;
	import com.kaltura.kdpfl.view.controls.BufferAnimation;
	import com.yahoo.astra.fl.containers.layoutClasses.AdvancedLayoutPane;
	
	import flash.display.Sprite;
	
	import org.osmf.events.DisplayObjectEvent;
	import org.osmf.events.MediaPlayerStateChangeEvent;
	import org.osmf.media.MediaPlayer;
	import org.osmf.media.MediaPlayerState;
	
	public dynamic class KMediaPlayer extends AdvancedLayoutPane implements IComponent
	{
		/**
		 * Stretch the thumbnail to the video player dimentions even if the player 
		 * keeps its aspect ratio.  
		 */		
		public var stretchThumbnail:Boolean = false;
		private var _player:MediaPlayer;
		private var _thumbnail : KThumbnail = new KThumbnail(); 
		private var _bgSprite : Sprite = new Sprite();		
		private var _bgColor : uint;
		private var _bgAlpha : Number;
		private var _volume : Number = 1;
		private var _bufferAnim : BufferAnimation;
		
		public var isFileSystemMode : Boolean;
		public function get volume() : Number{ return _volume; }
		[Bindable]
		public function set volume( value : Number ) : void 
		{ 
			_volume = value; 
			
			if(player) 
				player.volume = _volume;
		}
		
		private var _keepAspectRatio:Boolean= true;
		
		[Bindable]
		public function get keepAspectRatio() : String { return _keepAspectRatio.toString(); }
		public function set keepAspectRatio(value:String):void
		{
			if(value=="true")
				_keepAspectRatio=true;			
			else
				_keepAspectRatio=false;
			
			
			//If thumbnail is visible and the aspect ratio is changed change the thumb size
			////////////
			onMediaSizeChange(null);
			this.width = _width;
			this.height = _height;
			///////////
		}
		
		private var _mediaWidth:Number;
		private var _mediaHeight:Number;
		
	//	private var _contentWidth:Number;
	//	private var _contentHeight : Number;
		[Bindable]
		/**
		 * width of the movie display object 
		 * 
		 */		
		public var movieWidth:Number;
		[Bindable]
		/**
		 * height of the movie display object 
		 * 
		 */		
		public var movieHeight : Number;
		[Bindable]
		/**
		 * x position of the movie
		 * 
		 * */
		public var movieX : Number;
		[Bindable]
		/**
		 * y position of the movie
		 * 
		 * */
		public var movieY : Number;
		
		public var bytesLoadedUpdateInterval : Number = 1000;
		public var currentTimeUpdateInterval : Number = 500;
		
		public function get player():MediaPlayer{ return _player; } //read only
		
		public function set player( p : MediaPlayer ) : void { _player = p; }
		
		public function set bufferSprite( bufferAnim : BufferAnimation ) : void
		{
			_bufferAnim = bufferAnim;
			if( ! this.parent.contains( _bufferAnim ) )
				this.parent.addChild( _bufferAnim );
			setBufferContainerSize();
		}
		
		/**
		 * Constructor 
		 * 
		 */
		public function KMediaPlayer(fileSystemMode : Boolean = false)
		{
			super();
			addChild(_bgSprite);
			
			isFileSystemMode = fileSystemMode;
			movieHeight = this.height;
			movieWidth = this.width;
			//TODO: listen and dispatch thumbnail Ready
		}
		
		//public
		////////////////////////////
		
		//initilize the component and set default behaviors
		public function initialize() : void 
		{
			if(_player)
			{
				removeAllListeners();
				_player = null;
			}
			
			_player = new MediaPlayer();
			_player.volume = _volume;
			_player.autoPlay = false;
			_player.loop = false;
			_player.autoRewind = false;
			
			// lower the default 250msec interval as we dont really care about rapid updates
			_player.bytesLoadedUpdateInterval = bytesLoadedUpdateInterval;
			_player.currentTimeUpdateInterval = currentTimeUpdateInterval;
			
			addAllListeners();
		}
		
		
		public function setSkin( styleName : String , setSkinSize : Boolean = false) : void
		{
			
		}
		
		public function loadThumbnail( url : String ,thumbWidth:Number, thumbHeight:Number , ks : String = null, flashvars:Object = null) : void
		{
			if(!url) return; //if someone send null we won't load it
			
			_mediaWidth = thumbWidth > 0 ? thumbWidth : this.width;
			_mediaHeight = thumbHeight > 0 ? thumbHeight : this.height;
			
			_thumbnail.isFileSystemMode = isFileSystemMode;
			
			
			if (!stretchThumbnail && _keepAspectRatio)
			{
				var newDimensions : Object = getAspectratio(_mediaWidth, _mediaHeight );
				thumbWidth = newDimensions.newWidth;
				thumbHeight = newDimensions.newHeight;
			}
			else
			{
				thumbWidth = this.width;
				thumbHeight = this.height;
			}
			

			
			
			_thumbnail.width= thumbWidth;
			_thumbnail.height = thumbHeight;
			centerImages();
			
			addChild(_thumbnail);
			
			var thumbUrl:String = url;	
			if ( url.indexOf( "thumbnail/entry_id" ) != -1 )
			{
				thumbUrl += "/width/" + thumbWidth+"/height/" + thumbHeight + URLUtils.getThumbURLPostfix(flashvars, ks);
			}
			_thumbnail.load(thumbUrl);
			
		}
		
		public function unloadThumbnail() : void
		{
			_thumbnail.unload();
		}
		
		public function hideThumbnail() : void
		{
			//didn't used the vidible because there is a bug that in Flex application
			//the FocusManager dispatch Flex event to astra UIComponent
			if (_thumbnail)
				_thumbnail.alpha = 0;
		}
		
		public function showThumbnail() : void
		{
			//didn't used the vidible because there is a bug that in Flex application
			//the FocusManager dispatch Flex event to astra UIComponent
			if (_thumbnail)
				_thumbnail.alpha = 1;
		}
		
		public function drawBg( color : uint = 0x000000, alpha : Number = 1) : void
		{	
			_bgColor = color;
			_bgAlpha = alpha;
			_bgSprite.graphics.clear();
			_bgSprite.graphics.beginFill(_bgColor,_bgAlpha);
			_bgSprite.graphics.drawRect(0,0,this.width,this.height);
			_bgSprite.graphics.endFill(); 
		}
		
		//private
		////////////////////////////
		private function setBufferContainerSize() : void
		{
			_bufferAnim.width = this.width;
			_bufferAnim.height = this.height;
		}
		
		private function addAllListeners() : void
		{
			_player.addEventListener( DisplayObjectEvent.DISPLAY_OBJECT_CHANGE,  onDisplayObjectChange );
			
		}
		
		private function removeAllListeners() : void
		{
			_player.removeEventListener( DisplayObjectEvent.DISPLAY_OBJECT_CHANGE,  onDisplayObjectChange );
			_player.removeEventListener(DisplayObjectEvent.MEDIA_SIZE_CHANGE, onMediaSizeChange );
		}
		/**
		 * Dispatched when a MediaPlayer's ability to expose its media as a DisplayObject has changed. 
		 * @param event
		 * 
		 */		
		private function onDisplayObjectChange( event : DisplayObjectEvent ) : void
		{
			
			if( _player && event.newDisplayObject)
			{
				
				addChild(event.newDisplayObject);
				
			}
			
			_player.addEventListener(DisplayObjectEvent.MEDIA_SIZE_CHANGE, onMediaSizeChange );
			_player.addEventListener( MediaPlayerStateChangeEvent.MEDIA_PLAYER_STATE_CHANGE, onMediaPlayerStateChange );
		}
		
		private function onMediaPlayerStateChange (e : MediaPlayerStateChangeEvent) : void
		{
			if ( e.state == MediaPlayerState.PLAYING )
			{
				_player.removeEventListener( MediaPlayerStateChangeEvent.MEDIA_PLAYER_STATE_CHANGE , onMediaPlayerStateChange );
				for (var i:int = 0; i < this.numChildren-1; i++ )
				{
					if (this.getChildAt(i) != this._bufferAnim && this.getChildAt(i) != this._thumbnail && this.getChildAt(i) != this._bgSprite)
					{
						this.removeChildAt(i);
					}
				}
			}
		}
		
		
		private function onMediaSizeChange (e : DisplayObjectEvent) : void
		{
			if (!_player)
				return;
			
			if (e)
			{
				_player.removeEventListener(DisplayObjectEvent.MEDIA_SIZE_CHANGE, onMediaSizeChange );
				_mediaHeight = e.newHeight;
				_mediaWidth = e.newWidth;
			}
			else if (_player.displayObject)
			{
				//if for some resone binding havn't occured on time of the event we will get here
				_mediaHeight = _player.displayObject.height;
				_mediaWidth = _player.displayObject.width;	
			}
			else
			{
				return;
			}
			
			if(_mediaHeight && _mediaWidth && !isNaN(_mediaHeight) && !isNaN(_mediaWidth) )
			{
				if (_keepAspectRatio)
				{
					var newDimensions : Object = getAspectratio(_mediaWidth, _mediaHeight);
					updateMovieDimensions (newDimensions["newWidth"], newDimensions["newHeight"]);
				}
				else
				{
					updateMovieDimensions (this.width, this.height);
				}
				
				centerImages();
			}
		}
		
		/**
		 * DEPRECATED 
		 * @param w
		 * @param h
		 * 
		 */		
		public function setContentDimension(w:Number, h:Number):void
		{
			//_contentWidth = w;
			//_contentHeight = h;	
		}
		
		
		/**
		 * this override gives the media player view to dynamiclly set
		 * it's size to the container size of his wrapper
		 */		
		override protected function updateChildren():void
		{	 
			//if we flashvars passed to draw backgrond
			if( _bgColor != -1 ) drawBg( _bgColor , _bgAlpha);
		}
		
		override public function set width(value:Number):void
		{
			if (value )
			{
				//var changeRatio : Number  = value/this.width;
				
				super.width = value;
				if(_bufferAnim)
				{
					_bufferAnim.width = value;
				}
				if(_thumbnail)
				{
					if (!stretchThumbnail &&_keepAspectRatio)
					{
						var newThumbnailDimensions : Object = getAspectratio(_mediaWidth, _mediaHeight);
						_thumbnail.width = newThumbnailDimensions["newWidth"];
						_thumbnail.height = newThumbnailDimensions["newHeight"];
					}
					else
					{
						_thumbnail.width = this.width;
						_thumbnail.height = this.height;
					}
				}
				if (_player && _player.displayObject)
				{
					
					if (_keepAspectRatio)
					{
						var newPlayerDimensions : Object = getAspectratio(_mediaWidth, _mediaHeight);
						updateMovieDimensions(newPlayerDimensions["newWidth"],newPlayerDimensions["newHeight"]); 
					}
					else
					{
						updateMovieDimensions(this.width, this.height);
					}
					
				}
				if (_bgSprite) _bgSprite.width = this.width;
				centerImages();
			}
		}
		
		override public function set height(value:Number):void
		{
			if (value)
			{
				super.height = value;
				if(_bufferAnim)
				{
					_bufferAnim.height  = value;
				}
				if(_thumbnail)
				{
					if (!stretchThumbnail &&_keepAspectRatio)
					{
						var newThumbnailDimensions : Object = getAspectratio(_mediaWidth, _mediaHeight);
						_thumbnail.width = newThumbnailDimensions["newWidth"];
						_thumbnail.height = newThumbnailDimensions["newHeight"];
					}
					else
					{
						_thumbnail.width = this.width;
						_thumbnail.height = this.height;
					}
				}
				if (_player && _player.displayObject)
				{
					if (_keepAspectRatio)
					{
						var newPlayerDimensions : Object = getAspectratio(_mediaWidth, _mediaHeight);
						updateMovieDimensions(newPlayerDimensions["newWidth"], newPlayerDimensions["newHeight"]);
					}
					else
					{
						updateMovieDimensions(this.width, this.height);
					}

				}
				if (_bgSprite) _bgSprite.width = this.width;
				centerImages();
			}
		}
		
		private function centerImages():void
		{
			if(_player.displayObject)
			{
				_player.displayObject.x=(this.width-_player.displayObject.width)/2;
				_player.displayObject.y=(this.height-_player.displayObject.height)/2;
				movieX = _player.displayObject.x;
				movieY = _player.displayObject.y;
			}
			if(_thumbnail)
			{
				_thumbnail.x=(this.width-_thumbnail.width)/2;
				_thumbnail.y=(this.height-_thumbnail.height)/2;
			}
		}
		
		private function getAspectratio(mediaWidth:Number,mediaHeight:Number):Object
		{
			var dimensions:Object=new Object;
			if (mediaWidth > mediaHeight)
			{
				dimensions.newWidth = this.width;
				dimensions.newHeight = this.width * mediaHeight/mediaWidth;
				
				if ( dimensions.newHeight > this.height)
				{
					//trace("KMediaPlayer: 1")
					dimensions.newHeight = this.height;
					dimensions.newWidth = this.height *mediaWidth/mediaHeight;
				}
			}
			else
			{
				dimensions.newHeight = this.height;
				dimensions.newWidth = this.height * mediaWidth/mediaHeight;
				
				if ( dimensions.newWidth > this.width)
				{
					dimensions.newWidth = this.width;
					dimensions.height = this.width *mediaHeight/mediaWidth;
				}
			}
			
			return dimensions;  		
		}
		
		
		/**
		 * This function searches for the flavor with the preferedBitrate value bitrate among the flavors belonging to the media.
		 * @param preferedBitrate The value of the prefered bitrate to search for among the stream items of the media.
		 * @return The function returns the index of the streamItem with the prefered bitrate
		 * 
		 */		
		public function findStreamByBitrate (preferedBitrate : int) : int
		{
			var foundStreamIndex:int = -1;
			
			if (_player.numDynamicStreams > 0)
			{
				for(var i:int = 0; i < _player.numDynamicStreams; i++)
				{
					var lastb:Number;
					if(i!=0)
						lastb = _player.getBitrateForDynamicStreamIndex(i-1);
					
					var b:Number = _player.getBitrateForDynamicStreamIndex(i);
					b = Math.round(b/100) * 100;
					
					if (b == preferedBitrate)
					{
						//if we found it set it and leave
						foundStreamIndex = i;
						return foundStreamIndex;
					}
					else if(i == 0 && preferedBitrate < b)
					{
						//if the first is bigger then the prefered bitrate set it and leave
						foundStreamIndex = i;
						return foundStreamIndex;
					}
					else if( lastb && preferedBitrate < b  && preferedBitrate > lastb )
					{
						//if the prefered bit rate is between the last index and the current choose the closer one
						var topDelta : int = b - preferedBitrate;
						var bottomDelta : int = preferedBitrate - lastb;
						if(topDelta<=bottomDelta)
						{
							foundStreamIndex = i;
							return foundStreamIndex;
						}
						else
						{
							foundStreamIndex = i-1;
							return foundStreamIndex;
						}
					}
					else if(i == _player.numDynamicStreams-1 && preferedBitrate >= b)
					{
						//if this is the last index and the prefered bitrate is still bigger then the last one
						foundStreamIndex = i;
						return foundStreamIndex;
					}
				}
				
				// if a stream was found set it as the new prefered bitrate 
				//[BOAZ] - i have disabled the line below due to this function finding stream and not setting it
				// if you want to set stream set it after you call findStreamByBitrate from outside.
				//(ApplicationFacade.getInstance().retrieveProxy(MediaProxy.NAME) as MediaProxy).vo.preferedFlavorBR = preferedBitrate;
			}
			
			return foundStreamIndex;
		}

		/**
		 * update the movie display object dimensions and movieWidth & movieHeight params 
		 * @param newWidth
		 * @param newHeight
		 * 
		 */		
		private function updateMovieDimensions(newWidth:Number, newHeight:Number):void {
			_player.displayObject.width= newWidth;
			_player.displayObject.height = newHeight;
			movieWidth = newWidth;
			movieHeight = newHeight;
		}
	}
	
}