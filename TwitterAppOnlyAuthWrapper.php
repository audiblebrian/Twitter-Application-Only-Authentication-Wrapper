<?php
/******************************************************************************************************************************
	This is a wrapper class Twitter"s REST API v1.1's Application-Only Authorization and some of its authorized requests.  
	There are wrapper methods provided to further simplify API access.  
	Application-Only Authentication lacks user context, and therefore, many operations like posting, account details, etc
	are not available with this level of access.  Please see the Twitter documentation for implementing other kinds of 
	authorization via OAUTH.
	
	For more information on Application-Only Authorization, see:  https://dev.twitter.com/docs/auth/application-only-auth
	For official Twitter API v1.1 Documenation, see: https://dev.twitter.com/docs/api/1.1
	
	Written by Brian Stewart, 2013
	http://audibleagency.com
	
	This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
	
********************************************************************************************************************************/
class TwitterAppOnlyAuthWrapper
{
	private $consumer_key;
    private $consumer_secret;
    private $oauth_access_token;
    private $oauth_access_token_secret;
	private $bearer_token;
	private $is_authorized = false;
	
	//	Constructor -- Takes application credentials (provided by Twitter development portal).
	public function __construct($oauth_access_token, $oauth_access_token_secret, $consumer_key, $consumer_secret)
    {     
        $this->consumer_key = $consumer_key;
        $this->consumer_secret = $consumer_secret;
        $this->oauth_access_token = $oauth_access_token;
        $this->oauth_access_token_secret = $oauth_access_token_secret;
    }

	//	This function initializes the wrapper class by obtaining and storing the bearer token.
	//	A call to this function is required for all other operations.
	//  IMPORTANT:  Check this function for its return.  False means that the object could not be authorized.  True means that the object was authorized.
	public function authorize()
	{
		try
		{
			$this->bearer_token = $this->getBearerToken();
		}
		catch (Exception $e)
		{
			if ($return_string_errors)
			{
				return $e.getMessage();
			}
			else
			{
				return false;
			}
		}
		$this->is_authorized = true;
		return $this->is_authorized;
	}
	
	//	Helper method to print variables for troubleshooting.
	public function printVars()
	{
		print_r("Consumer token:  " . $this->consumer_key . "<br />");
		print_r("Consumer secret:  " . $this->consumer_secret . "<br />");
		print_r("Consumer OAUTH token:  " . $this->oauth_access_token . "<br />");
		print_r("Consumer OAUTH secret:  " . $this->oauth_access_token_secret . "<br />");
		print_r("Bearer token:  " . $this->bearer_token . "<br />");		
		print_r("Is Authorized: " . $this->is_authorized . "<br />");
	}
		
	//	This function is to retrieve user statuses
	//  Wrapper method for:  /1.1/statuses/user_timeline.json
	//	INPUT: Twitter username, number of statuses (defaulted to 1)
	//	Returns a collection of statuses in JSON.
	public function getUserStatuses($username, $count = 1)
	{
		$url_query = "https://api.twitter.com/1.1/statuses/user_timeline.json?" . "count=" . rawurlencode($count) . "&screen_name=" . rawurlencode($username) . "&include_entities=true";
		return $this->performRequest($url_query);		 
	}
	
	//	This function is to retrieve trends based on a location (defaults to 1 which is: world)
	//  Wrapper method for:  /1.1/trends/place.json
	//	INPUT:  Yahoo WOEID (Where On Earth ID) geo-location integer value
	//	Returns a collection of trends in JSON.
	public function getTrends($WOEID = 1)
	{
		$url_query = "https://api.twitter.com/1.1/trends/place.json?" . "id=" . rawurlencode($WOEID);
		return $this->performRequest($url_query);		 
	}
	
	//	This function will search for the provided query string in all public tweets.  Configurable results types:
	// 		"mixed": Include both popular and real time results in the response.  Default value.
	//		"recent": return only the most recent results in the response
	//		"popular": return only the most popular results in the response.
	//  Wrapper method for: /1.1/search/tweets.json
	//	INPUT:  Search query string, results type, language (ISO 2 Char Standard)
	//	Returns a collection of Tweets in JSON.
	public function searchTweets($query, $result_type = "mixed", $language = "en")
	{
		$url_query = "https://api.twitter.com/1.1/search/tweets.json?" . "q=" . rawurlencode($query) . "&result_type=" . rawurlencode($result_type) . "&lang=" . rawurlencode($language);
		return $this->performRequest($url_query);		 
	}
	
	//	This function will retrieve public user data from one or more provided usernames
	//  Wrapper method for:  /1.1/users/lookup.json
	//	INPUT: Comma-delimeted list of usernames
	//	Returns a collection of users in JSON.
	public function getUsers($usernames)
	{
		$url_query = "https://api.twitter.com/1.1/users/lookup.json?" . "screen_name=" . rawurlencode($usernames);
		return $this->performRequest($url_query);		 
	}	 
	
	//	This function returns the most recent statuses of a list of users.
	//  Wrapper method for:  /1.1/lists/statuses.json
	//	INPUT:  List slug, list owner's username, max number of statuses to return
	//	Returns a collection of statuses in JSON.
	public function getListStatuses($slug, $username, $count = 1)
	{
		$url_query = "https://api.twitter.com/1.1/lists/statuses.json?" . "slug=" . rawurlencode($slug) . "&owner_screen_name=" . rawurlencode($username) . "&count=" . rawurlencode($count);
		return $this->performRequest($url_query);		 
	}	
	
	//	This functions returns the lists a given user is subscribed to.
	//  Wrapper method for:  /1.1/lists/list.json
	//	INPUT: Twitter username
	//	Returns a collection of lists in JSON.
	public function getLists($username)
	{
		$url_query = "https://api.twitter.com/1.1/lists/list.json?" . "screen_name=" . rawurlencode($username);
		return $this->performRequest($url_query);		 
	}	
	
	//	This function returns the rate limits and remaining accesses allowed per object
	//  Wrapper method for:  /1.1/application/rate_limit_status.json
	//	INPUT: A comma delimeted list of resources to check -- default includes all objects in this wrapper
	//	Returns a collection of resource rate limits in JSON.
	public function getRateLimits($resources = "users,lists,statuses")
	{
		$url_query = "https://api.twitter.com/1.1/application/rate_limit_status.json?" . "resources=" . rawurlencode($resources);
		return $this->performRequest($url_query);		 
	}
	
	//	Execute request with provided URL.  URL will contain request details.
	private function performRequest($url)
    {
		if (!($this->is_authorized))
		{
			$response = json_encode(array("Error"=>"The wrapper has not been authorized.  Please authorize."));
			return $response;
		}
		$headers = array(
			end(explode("https://api.twitter.com", $url)) . " HTTP/1.1",
			"Host: api.twitter.com",
			"User-Agent: Audible Agency Twitter Application-Only OAUTH",
			"Authorization: Bearer " . $this->bearer_token
		);
        $ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		$response = curl_exec($ch); 
		
		if ($response == false) 
		{
			$response = json_encode(array("Error"=>"Twitter API request error: " . curl_error($ch)));
		} 
		curl_close($ch); 
		return $response;
    }	
	
	//	Retrieve the bearer token.  This token remains valid until explicitly invalidated.  Will be used for all requests.
	private function getBearerToken()
	{
		$conc_64_key = base64_encode(rawurlencode($this->consumer_key) . ":" . rawurlencode($this->consumer_secret));
		
		$headers = array(
			"POST /oauth2/token HTTP/1.1",
			"Host: api.twitter.com",
			"User-Agent: Audible Agency Twitter Application-Only OAUTH",
			"Authorization: Basic " . $conc_64_key,
			"Content-Type: application/x-www-form-urlencoded;charset=UTF-8",
			"Content-Length: 29",
		);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"https://api.twitter.com/oauth2/token"); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POST, 1); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
		$json_bearer = curl_exec($ch);
		
		if ($json_bearer == false)
		{
			throw new Exception("Error obtaining bearer token:  " . curl_error($ch));
		}
		else
		{
			$token_type = json_decode($json_bearer)->{"token_type"};		
			if ( !is_null($token_type) && $token_type == "bearer")
			{
				$bearer_token = json_decode($json_bearer)->{"access_token"};
				return $bearer_token;
			}
			else
			{
				throw new Exception("Error obtaining bearer token.  Missing nodes in response object.");
			}
		}
		curl_close($ch);		
	}	
	
	//	Invalidates the bearer token.  This should be used in a case where there are security issues or concern.  A new bearer token must be obtained, so a call must be made to the authorize() function again.
	public function deauthorize()
	{
		$conc_64_key = base64_encode(rawurlencode($this->consumer_key) . ":" . rawurlencode($this->consumer_secret));
		
			$headers = array(
				"POST /oauth2/invalidate_token HTTP/1.1",
				"Host: api.twitter.com",
				"User-Agent: Audible Agency Twitter Application-Only OAUTH",
				"Authorization: Basic ".$conc_64_key."",
				"Accept: */*",
				"Content-Type: application/x-www-form-urlencoded",
				"Content-Length: ".(strlen($this->bearer_token)+13).""
			); 
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"https://api.twitter.com/oauth2/invalidate_token"); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POST, 1); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "access_token=" . $this->bearer_token);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		$response = curl_exec($ch);

		if ($response == false)
		{
			throw new Exception("Error invalidation bearer token:  " . curl_error($ch));
			return false;
		}
		else
		{
			$this->is_authorized = false;
			$this->bearer_token = null;
		}
		curl_close($ch);	
		return true;
	}	
}