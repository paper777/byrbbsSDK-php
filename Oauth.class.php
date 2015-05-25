<?php

require_once("URL.class.php");

class Oauth{

    const VERSION = "0.9";
	// URL
    const GET_AUTH_CODE_URL = "http://bbs.byr.cn//oauth2/authorize";
    const GET_ACCESS_TOKEN_URL = "http://bbs.byr.cn//oauth2/token";
    const GET_INFO_URL = "http://bbs.byr.cn//open/user/getinfo.json";

	// CLIENT INFO
    const CLIENT_ID = "";
    const CLIENT_SECRET = "";
    const REDIRECT_URI = "";
    const SCOPE = "article,mail,blacklist";

    protected $error;

    public $urlUtils;

    function __construct(){
        $this->error = new ErrorCase();
        $this->urlUtils = new URL();
    }

    public function byr_login(){

        // 生成唯一随机串防CSRF攻击
         $state = md5(uniqid(rand(), TRUE));
		// store the state TODO

        // request list
        $keysArr = array(
            "response_type" => "code",
            "client_id" => self::CLIENT_ID,
            "redirect_uri" => self::REDIRECT_URI,
            "state" => $state,
            "scope" => self::SCOPE
        );

        //$login_url =  $this->combineURL(self::GET_AUTH_CODE_URL, $keysArr);
        $login_url =  $this->urlUtils->combineURL(self::GET_AUTH_CODE_URL, $keysArr);

        header("Location:$login_url");
    }

	/*
	 * get access_token
	 * return array		access params OR error info
	 */
    public function callback(){
        if(!isset($_GET['code'])) {
            return;
        }

        // 验证state防止CSRF攻击
		// TODO
        
        // request list
        $keysArr = array(
            "grant_type" => "authorization_code",
            "client_id" => self::CLIENT_ID,
            "redirect_uri" => urlencode(self::REDIRECT_URI),
            "client_secret" => self::CLIENT_SECRET,
            "code" => $_GET['code']
		);
		$result = $this->urlUtils->post(self::GET_ACCESS_TOKEN_URL, $keysArr);
		$result = json_decode($result, true);
		return $result;
	}

    public function get_info($access_token){

        // request list
        $keysArr = array(
            "oauth_token" => $access_token,
        );

		///$result = $this->urlUtils->post(self::GET_INFO_URL, $keysArr);
		$info_url = $this->urlUtils->combineURL(self::GET_INFO_URL, $keysArr);
        $result = $this->urlUtils->get_contents($info_url);

		$result = json_decode($result, true);
		return $result;
    }

}
