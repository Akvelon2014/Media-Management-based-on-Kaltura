<?php
class kAkamaiHttpUrlTokenizer extends kUrlTokenizer
{
	/**
	 * @var int
	 */
	protected $window;

	/**
	 * @var string
	 */
	protected $param;

	/**
	 * @var string
	 */
	protected $salt;

	/**
	 * @var string
	 */
	protected $root_dir;
	
	/**
	 * @param int $window
	 * @param string $param
	 * @param string $salt
	 * @param string $root_dir
	 */
	public function __construct($window, $param, $salt, $root_dir)
	{
		$this->window = $window;
		$this->param = $param;
		$this->salt = $salt;
		$this->root_dir = $root_dir;
	}
	
	/**
	 * @param string $url
	 * @return string
	 */
	public function tokenizeSingleUrl($url)
	{
		if ($this->root_dir)
			$url = rtrim($this->root_dir, '/') . '/' . ltrim($url, '/');
		return self::urlauth_gen_url($url, $this->param, $this->window, $this->salt, null, null);
	}
	/**
	 * Returns the URL path with the authorization token appended. See the
	 *   README for more details.
	 */
	static function urlauth_gen_url($sUrl, $sParam, $nWindow,
	                         $sSalt, $sExtract, $nTime) {
	
		$sToken = self::urlauth_gen_token($sUrl, $nWindow, $sSalt,
	                                    $sExtract, $nTime);
		if ($sToken == null) {
			return;
		}
	
		if (($sParam == "") || (!is_string($sParam))) {
			$sParam = "__gda__";
		}
	
	    if ((strlen($sParam) < 5) || (strlen($sParam) > 12)) {
	        return;
	    }
	
		if (($nWindow < 0) || (!is_numeric($nWindow))) {
			return;
		}
	
		if (($nTime <= 0) || (!is_numeric($nTime))) {
			$nTime = time();
		}
	
		$nExpires = $nWindow + $nTime;
	
		if (strpos($sUrl, "?") === false) {
			$res = $sUrl . "?" . $sParam . "=" . $nExpires . "_" . $sToken;
		} else {
			$res = $sUrl . "&" . $sParam . "=" . $nExpires . "_" . $sToken;
		}
	
		return $res;
	}
	
	/**
	 * Returns the hash portion of the token. This function should not be
	 *   called directly.
	 */
	static function urlauth_gen_token($sUrl, $nWindow, $sSalt,
	                           $sExtract, $nTime) {
		if (($sUrl == "") || (!is_string($sUrl))) {
			return;
		}
	
		if (($nWindow < 0) || (!is_numeric($nWindow))) {
			return;
		}
	
		if (($sSalt == "") || (!is_string($sSalt))) {
			return;
		}
	
		if (!is_string($sExtract)) {
			$sExtract = "";
		}
	
		if (($nTime <= 0) || (!is_numeric($nTime))) {
			$nTime = time();
		}
	
		$nExpires = $nWindow + $nTime;
		$sExpByte1 = chr($nExpires & 0xff);
		$sExpByte2 = chr(($nExpires >> 8) & 0xff);
		$sExpByte3 = chr(($nExpires >> 16) & 0xff);
		$sExpByte4 = chr(($nExpires >> 24) & 0xff);
	
		$sData = $sExpByte1 . $sExpByte2 . $sExpByte3 . $sExpByte4
	                 . $sUrl . $sExtract . $sSalt;
	
		$sHash = self::_unHex(md5($sData));
	
		$sToken = md5($sSalt . $sHash);
		return $sToken;
	}
	
	/**
	 * Helper function used to translate hex data to binary
	 */
	static function _unHex($str) {
		$res = "";
		for ($i = 0; $i < strlen($str); $i += 2) {
	        $res .= chr(hexdec(substr($str, $i, 2)));
		}
		return $res;
	}
}
