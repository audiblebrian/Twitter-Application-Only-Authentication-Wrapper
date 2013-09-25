#!/usr/local/php53/bin/php -q
<?php
	
	//****************************************************************************************************************************************************
	// Standard usage
	require_once('TwitterAppOnlyAuthWrapper.php');
	
	$oauth_access_token = "1530858998-Whuzkbb510sC6j9YFuznXoESJwS2FqCpSHx4ryi";
	$oauth_access_token_secret = "B2FkYYSKqeWMMSSnpzIMBvQYosbH5a2vGk44EX9ib0";
	$consumer_key = "f0wt8irMOIArm7UAVIRkw";
	$consumer_secret = "u3Vu46Y0wxCR9Sl49r0CZXt1DXLKyiBISWO38Qu7g0";

	$twitter = new TwitterAppOnlyAuthWrapper($oauth_access_token, $oauth_access_token_secret, $consumer_key, $consumer_secret);
	if($twitter->authorize())
	{
		//***************************************************************************

		echo '<h2>Print Vars</h2>';
		$twitter->printVars();
			
			
		//***************************************************************************

		echo '<h2>Tweets</h2>';

		$tweets = json_decode($twitter->getUserStatuses('audibleagency', 3));

		foreach ($tweets as $tweet)
			echo '<p>' . $tweet->text . '</p>';

		//***************************************************************************

		echo '<h2>Trends</h2>';
		
		$trends = json_decode($twitter->getTrends(2358820));
		
		foreach ($trends[0]->trends as $trend)
			echo '<p>' . $trend->name . '</p>';
			
		//***************************************************************************
		
		echo '<h2>Search Tweets</h2>';
		
		$search_results = json_decode($twitter->searchTweets("api"));	

		foreach ($search_results->statuses as $search_result)
			echo '<p>' . $search_result->text . '</p>';
			
			
		//***************************************************************************
		
		echo '<h2>User Lookup</h2>';
		
		$users = json_decode($twitter->getUsers("audibleagency,davecricket"));	

		foreach ($users as $user)
			echo '<p>' . $user->name . ': ' . $user->description . '</p>';
			
		
		//***************************************************************************
		
		echo '<h2>Tweets from List</h2>';
		
		$tweets = json_decode($twitter->getListStatuses("baltimore-food-trucks", "audibleagency", 5));	
		foreach ($tweets as $tweet)
			echo '<p>' . $tweet->user->screen_name . ": " . $tweet->text . '</p>';
			
		
		//***************************************************************************
		
		echo '<h2>Lists</h2>';
		
		$lists = json_decode($twitter->getLists("audibleagency"));	
		foreach ($lists as $list)
			echo '<p>' . $list->name . ': ' . $list->description . '</p>';
			
		
		//***************************************************************************
		
		echo '<h2>Rate Limits</h2>';
		
		$limits = json_decode($twitter->getRateLimits());	
		print_r($limits);   // this is just going to return the json
	}
	else
	{
		echo "<p>Couldn't authorize.</p>";
	}
	
	
	//****************************************************************************************************************************************************
	// Invalidate the bearer token
			
	echo '<h2>Invalidate the token</h2>';
	if ($twitter->deauthorize())
	{
		echo "<p>Token has been invalidated</p>";
	}
	else
	{
		echo "<p>An error has occurred while invalidating token.</p>";
	}
		
		
	//****************************************************************************************************************************************************	
	// Print vars to show invalidation was a success
	
	echo '<h2>Variables after invalidating</h2>';
	$twitter->printVars();
	
	
	//****************************************************************************************************************************************************
	// Show unauthorized error by trying to call a method without having a valid bearer token
	
	echo '<h2>Call a function without authorization</h2>';
	$tweets = json_decode($twitter->getUserStatuses('audibleagency', 3));
	print_r($tweets);
	
	
	//****************************************************************************************************************************************************
	// Re-authorize and show that it works again
	
	echo '<h2>Re-authorize, request tweets</h2>';
	
	if($twitter->authorize())
	{
		$twitter->printVars();
		echo '<h2>Tweets</h2>';

		$tweets = json_decode($twitter->getUserStatuses('audibleagency', 3));

		foreach ($tweets as $tweet)
			echo '<p>' . $tweet->text . '</p>';
	}
?>