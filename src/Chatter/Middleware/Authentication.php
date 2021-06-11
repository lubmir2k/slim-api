<?php

namespace Chatter\Middleware;

use Chatter\Models\User;

class Authentication
{
	public function __invoke($request, $response, $next)
	{
        // We need the request header - authorization, the type of authorization (bearer token) and the token itself
		$auth = $request->getHeader('Authorization');
		
        // Pull off the token of the data (format is bearer SPACE token) and skip the space
		$_apikey = $auth[0];
		$apikey = substr($_apikey, strpos($_apikey, ' ') + 1);
		
		$user = new User();
		if(!$user->authenticate($apikey))
		{
            // Failed to authenticate
			$response->withStatus(401);
			return $response;
		}
		
		$response = $next($request, $response);
	}
}