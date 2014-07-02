package com.kaltura.kmc.utils
{
	import mx.utils.Base64Decoder;

	public class KSUtil {
		
		
		/**
		 * get the user id from the given ks 
		 * @param ks	ks to decode
		 * @return user id from given KS.
		 */		
		public static function getUserId(ks:String):String {
			var dec:Base64Decoder = new Base64Decoder();
			dec.decode(ks);
			var str:String = dec.toByteArray().toString();
			return str.split(';')[5];
		}
	}
}