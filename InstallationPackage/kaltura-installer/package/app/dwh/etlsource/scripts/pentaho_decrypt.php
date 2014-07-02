<?php

require_once dirname(__FILE__).'/BigInteger.php';

# reverses Pentaho's password obfuscation
function decrypt($val)
{
  # Support for both obfuscated and unobfuscated passwords
	$encryptionstr = 'Encrypted ';
	if ( strstr($val,$encryptionstr) )
		$val = substr( $val,strlen($encryptionstr) );
	else
		return $val;
	
	# decryption logic
	$decconst = new Math_BigInteger('933910847463829827159347601486730416058');
	$decrparam = new Math_BigInteger($val,16);
	$decryptedval = $decrparam->bitwise_xor($decconst)->toBytes();
	$result = "";
	for($i=0;$i<strlen($decryptedval);$i=$i+1)
	{
		$result.=$decryptedval[$i];
	}
	return $result;
}

?>
