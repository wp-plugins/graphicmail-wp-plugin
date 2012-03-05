<?php

class Graphicmail

{

    private $url = null;

	private $user = null;

    private $pass = null;



    public function __construct($url, $user, $pass)

    {

        $this->url = $url;

		$this->user = $user;

        $this->pass = $pass;

    }
	
	public function check_url($url)
	{
		
    	
		$response = @file_get_contents($url);
		
		if(empty($response))
			return false;
		
		if($response[0]!='0')

        {
			libxml_use_internal_errors(true);
            $xml = simplexml_load_string($response);

			if($xml)
				return true;
			else
				return false;
		}
		
		return false;
	}
	
	public function corect_url()
	{
		$url = $this->url."/api.aspx?Username={$this->user}&Password={$this->pass}&Function=get_mailinglists&Principal=FZQ16ZBzEukUKAkPelaY91v1bM5FeT7N";
		
		
		if(strpos($url, 'http') === false and strpos($url, 'https') === false){
			$url = 'https://'.$url;
			$this->url = 'https://'.$this->url;
			$addhtts=true;
		}
		
		if($this->check_url($url))
			return $this->url;
			
		if($addhtts == true )	
		{
			$url = str_replace('https://','http://', $url);
			$this->url = str_replace('https://','http://', $this->url);
			
			if($this->check_url($url))
				return $this->url;
		}
	
		return $this->url;
	}

    public function check_credentials()
    {

        //To check the credentials check if we can get the mailing lists
      	if (!extension_loaded  ('openssl')) {
    		$lists = "The OpenSSL PHP extension is not installed on this server. Please install this extension and try again.";
	}else{
		$lists = $this->get_lists();
	}

        if(is_string($lists)) return array(false,$lists);

        else return array(true,$lists) ;

    }



    public function subscribe($email, $list)

    {
		$this->url = $this->corect_url();
		$url = $this->url;
		
        $url .= "/api.aspx?Username={$this->user}&Password={$this->pass}&Function=post_subscribe&Email={$email}&MailinglistID={$list}&Principal=FZQ16ZBzEukUKAkPelaY91v1bM5FeT7N";

        $rq = curl_init($url);

        curl_setopt($rq, CURLOPT_POST, false);

        curl_setopt($rq, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt($rq, CURLOPT_SSL_VERIFYHOST, 0);

        curl_setopt($rq, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($rq);
		
		
        if($response !== false)

        {

            if($response[0]=='0')

            {

                return false;

            }

            else

            {

                return true;

            }

        }

        else

        {

            return false;

        }

        curl_close($rq);

    }



    public function get_lists()

    {
		$this->url = $this->corect_url();
		$url = $this->url;
		
        $url .= "/api.aspx?Username={$this->user}&Password={$this->pass}&Function=get_mailinglists&Principal=FZQ16ZBzEukUKAkPelaY91v1bM5FeT7N";
		
        $response = @file_get_contents($url);

        if(empty($response)) return "Failed to connect";

        

        if($response[0]!='0')

        {
			libxml_use_internal_errors(true);
            $xml = simplexml_load_string($response);

			if($xml){

				$lists = array();
	
				foreach($xml->mailinglist as $list)
	
				{
	
					$lists[(string)$list->mailinglistid] = (string)$list->description;
	
				}
	
	
	
				return $lists;
			}else{
				return "Failed to connect";
			}

        }

        else

        {
			
            	return substr($response, 2);

        }

    }

}