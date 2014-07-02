package com.kaltura.kdpfl.controller.media
{
	import com.kaltura.kdpfl.model.ConfigProxy;
	import com.kaltura.kdpfl.model.MediaProxy;
	import com.kaltura.kdpfl.model.SequenceProxy;
	import com.kaltura.kdpfl.model.type.NotificationType;
	import com.kaltura.kdpfl.view.media.KMediaPlayer;
	import com.kaltura.kdpfl.view.media.KMediaPlayerMediator;
	
	import org.puremvc.as3.interfaces.INotification;
	import org.puremvc.as3.patterns.command.SimpleCommand;

	/**
	 * PostSequenceEndCommand is called when the pre-sequence of the player is complete. 
	 * The "main-event" media is reloaded into the player and begins to play automatically.
	 * All variables which have to do with the pre-sequence are nullified and the sequence is registered as COMPLETE.
	 */
	public class PreSequenceEndCommand extends SimpleCommand
	{
		override public function execute(notification:INotification):void
		{
			var sequenceProxy : SequenceProxy = facade.retrieveProxy( SequenceProxy.NAME ) as SequenceProxy;
			sequenceProxy.vo.isInSequence = false;
			sequenceProxy.vo.preCurrentIndex = -1;
			sequenceProxy.vo.preSequenceComplete = true;
			var flashvars:Object = (facade.retrieveProxy( ConfigProxy.NAME ) as ConfigProxy).vo.flashvars;
			if (!flashvars.pauseAfterPreSequence || flashvars.pauseAfterPreSequence=="false")
			{
				sendNotification(NotificationType.DO_PLAY);				
			}
			else
			{
				sendNotification(NotificationType.DO_PAUSE);	
				var mediaMediator:KMediaPlayerMediator = facade.retrieveMediator(KMediaPlayerMediator.NAME) as KMediaPlayerMediator;
				mediaMediator.cleanMedia();
				mediaMediator.kMediaPlayer.showThumbnail();
			}
			
		}
	}
}