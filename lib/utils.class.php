<?php
class utils {
    const dsn="pgsql:host=127.0.0.1;port=5432;dbname=gw_sanremo;user=postgres;password=postgres";
    static function now(){
        return date('d/m/Y h:i:s', time());
    }
    static function dump($data){
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    }
    static function debug($file,$data,$mode='a+'){
        $now=self::now();
        $f=fopen($file,$mode);
	ob_start();
        echo "------- DEBUG DEL $now -------\n";
	print_r($data);
	$result=ob_get_contents();
	ob_end_clean();
	fwrite($f,$result."\n-------------------------\n");
	fclose($f);
    }
    static function debugParams($file,$st,$mode='a+'){
        $now=self::now();
        $f=fopen($file,$mode);
	ob_start();
        echo "------- DEBUG DEL $now -------\n";
	$st->debugDumpParams();
	$result=ob_get_contents();
	ob_end_clean();
	fwrite($f,$result."\n-------------------------\n");
	fclose($f);
    }
    static function checkValues($arr){
        $res=Array();
        foreach($arr as $key=>$val){ 
            if (strlen($val)>0){
                $res[$key]=$val;
                $encoding[$key]=  mb_detect_encoding($val);
            }
        }
        utils::debug(DEBUG_DIR."encoding.debug",$encoding);
        return $res;
    }
    static function checkValidation($encStr){
        return strlen($encStr)>0;
    }
    static function encrypt($n,$key){
        
    }
    static function decrypt(){
        
    }
    static function constructQueryVals($fields=Array()){
        $result=Array();
        for($i=0;$i<count($fields);$i++){
            $result[$i]=sprintf(":%s",$fields[$i]);
        }
        return $result;
    }
    static function leggiPratica($pratica=""){
        $dbh=new PDO(self::dsn);
        if (!$pratica){
                $pratica=self::randomNPratica();
        }
        $sql="SELECT * FROM pe.avvioproc WHERE pratica=?;";
        $st=$dbh->prepare($sql);
        if(!$st->execute($pratica)){
                die("ERRORE NELLA QUERY : $sql");
        }
        $res=$st->fetch(PDO::FETCH_ASSOC);
        return $res;
    }
}

class userUtils{
	const range = 21600;
	var $data;
	static function rand_str($length = 8, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890'){
        // Length of character list
        $chars_length = (strlen($chars) - 1);

        // Start our string
        $string = $chars{rand(0, $chars_length)};

        // Generate random string
        for ($i = 1; $i < $length; $i = strlen($string))
        {
            // Grab a random character from our list
            $r = $chars{rand(0, $chars_length)};

            // Make sure the same two characters don't appear next to each other
            if ($r != $string{$i - 1}) $string .=  $r;
        }
        // Return the string
        return $string;
    }
	static function addInformation($info=array()){
		$result=$info;
		$keys=array_keys($info);
		for($i=0;$i<5;$i++){
			$k=self::rand_str(rand(10,15));
			if (!in_array($k,$keys)){
				$result[$k]=self::rand_str(rand(10,20));
			}
		}
		return $result;
	}
	static function check_in_range($date_from_user){
		 // Convert to timestamp
		
		$start_ts =  time()-self::range;
		$end_ts = time() + self::range;
		$user_ts = strtotime($date_from_user);
		// Check that user date is between start & end
		return (($user_ts >= $start_ts) && ($user_ts <= $end_ts));
	}
	static function validToken($passPrhase,$encryptedtext){
		$cipher = new Cipher($passPrhase);
		$decryptedtext = $cipher->decrypt($encryptedtext);
		
		$info=json_decode($decryptedtext,true);
		if(!$info) return Array("status"=>-1);
		$keys=array_keys($info);
		
		if (!(in_array("time",$keys) && $info["time"])) return Array("status"=>-10);
		if (!self::check_in_range($info["time"])) return Array("status"=>-9);
		if (!(in_array("validuser",$keys) && $info["validuser"])) return Array("status"=>-8);
		$info["status"]=1;
		return $info;
	}
	
	static function extractData($passPrhase,$encryptedtext){
		$cipher = new Cipher($passPrhase);
		$decryptedtext = $cipher->decrypt($encryptedtext);
		$info=json_decode($decryptedtext,true);
		return $info;
	}
	
	static function setCookie($cookieName='gisweb',$value="",$exp){
		
	}
	
	static function getCookie(){
		if (isset($_COOKIE["gisweb"])){
			self::validToken($pass,$encryptedtext);
		}
	}
	
	static function setSession(){
	
	}
}
class Cipher {
    private $securekey, $iv;
    function __construct($textkey) {
        $this->securekey = hash('sha256',$textkey,TRUE);
        $this->iv = mcrypt_create_iv(32);
    }
    function encrypt($input) {
        return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->securekey, $input, MCRYPT_MODE_ECB, $this->iv));
    }
    function decrypt($input) {
        return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->securekey, base64_decode($input), MCRYPT_MODE_ECB, $this->iv));
    }
}
?>