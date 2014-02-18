<?php
namespace HD\Social\OAuth2\Controller;

use Hybrid_Auth;
use Zend\Mvc\Controller\AbstractActionController;
use OAuth2\Request as OAuth2Request;
use OAuth2\Response as OAuth2Response;
use OAuth2\Server as OAuth2Server;
use HD\Social\OAuth2\GrantType\SocialCredentials;

use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;

class AuthController extends AbstractActionController
{
	public function providerAction()
	{
		$services = $this->getServiceLocator()->get('ServiceManager');
        $config   = $services->get('Configuration');

		// Make sure the provider is enabled, else 404
        $provider = $this->params('provider');

        $hybridAuthConfig = $config['social-oauth2'];

        try{
			// initialize Hybrid_Auth with a given file
			$hybridauth = new Hybrid_Auth( $hybridAuthConfig );
			 
			// try to authenticate with the selected provider
			$adapter = $hybridauth->authenticate( $provider );
			 
			// then grab the user profile
			$user_profile = $adapter->getUserProfile();

			// then grab the user profile
			$access_token  = $adapter->getAccessToken();
		} catch( Exception $e ){
			echo "Error: please try again!";
			echo "Original error message: " . $e->getMessage();
		}
		
		//need to save the user
		print_r($user_profile);
        print_r($access_token);


        exit;

        //from here on it is oauth time
        if (!isset($config['zf-oauth2']['storage']) || empty($config['zf-oauth2']['storage'])) {
            throw new Exception\RuntimeException(
                'The storage configuration [\'zf-oauth2\'][\'storage\'] for OAuth2 is missing'
            );
        }

        $storage = $services->get($config['zf-oauth2']['storage']);

        $enforceState  = isset($config['zf-oauth2']['enforce_state'])  ? $config['zf-oauth2']['enforce_state']  : true;
        $allowImplicit = isset($config['zf-oauth2']['allow_implicit']) ? $config['zf-oauth2']['allow_implicit'] : false;

        // Pass a storage object or array of storage objects to the OAuth2 server class
        $server = new OAuth2Server($storage, array('enforce_state' => $enforceState, 'allow_implicit' => $allowImplicit));

        // Add the "Client Credentials" grant type (it is the simplest of the grant types)
        $server->addGrantType(new SocialCredentials($storage));

        $oauth2request = $this->getOAuth2Request();
        
        $response = $server->handleTokenRequest($oauth2request);
        
        if ($response->isClientError()) {
            $parameters = $response->getParameters();

            $errorUri   = isset($parameters['error_uri']) ? $parameters['error_uri'] : null;
            return new ApiProblemResponse(
                new ApiProblem(
                    $response->getStatusCode(),
                    $parameters['error_description'],
                    $errorUri,
                    $parameters['error']
                )
            );
        }
        return $this->setHttpResponse($response);
	}

	public function hybridAction()
	{
		\Hybrid_Endpoint::process();
	}

	 /**
     * Create an OAuth2 request based on the ZF2 request object
     *
     * Marshals:
     *
     * - query string
     * - body parameters, via content negotiation
     * - "server", specifically the request method and content type
     * - raw content
     * - headers
     *
     * This ensures that JSON requests providing credentials for OAuth2
     * verification/validation can be processed.
     *
     * @return OAuth2Request
     */
    protected function getOAuth2Request()
    {
        $zf2Request = $this->getRequest();
        $headers    = $zf2Request->getHeaders();

        // Marshal content type, so we can seed it into the $_SERVER array
        $contentType = '';
        if ($headers->has('Content-Type')) {
            $contentType = $headers->get('Content-Type')->getFieldValue();
        }

        // Get $_SERVER superglobal
        $server = [];
        if ($zf2Request instanceof PhpEnvironmentRequest) {
            $server = $zf2Request->getServer()->toArray();
        } elseif (!empty($_SERVER)) {
            $server = $_SERVER;
        }
        $server['REQUEST_METHOD'] = 'POST';

        // Seed headers with HTTP auth information
        $headers = $headers->toArray();
        if (isset($server['PHP_AUTH_USER'])) {
            $headers['PHP_AUTH_USER'] = $server['PHP_AUTH_USER'];
        }
        if (isset($server['PHP_AUTH_PW'])) {
            $headers['PHP_AUTH_PW'] = $server['PHP_AUTH_PW'];
        }

        $bodyParams['grant_type'] = 'social_login';//HD\Social\OAuth2\GrantType\SocialCredentials
        $bodyParams['user_id'] = 'fakeuserid';
        $bodyParams['provider'] = 'twitter'; //providers used to authenticate 3rd party
        $bodyParams['provider_id'] = 'fake_provider_user_id'; //user_id returned from hybridauth
        $bodyParams['provider_access_toke'] = 'faketoken'; //access token provided by hybridauth

        return new OAuth2Request(
            $zf2Request->getQuery()->toArray(),
            $bodyParams,
            [], // attributes
            [], // cookies
            [], // files
            $server,
            $zf2Request->getContent(),
            $headers
        );
    }

	/**
     * Convert the OAuth2 response to a \Zend\Http\Response
     *
     * @param $response OAuth2Response
     * @return \Zend\Http\Response
     */
    private function setHttpResponse(OAuth2Response $response)
    {
        $httpResponse = $this->getResponse();
        $httpResponse->setStatusCode($response->getStatusCode());

        $headers = $httpResponse->getHeaders();
        $headers->addHeaders($response->getHttpHeaders());
        $headers->addHeaderLine('Content-type', 'application/json');

        $httpResponse->setContent($response->getResponseBody());
        return $httpResponse;
    }
}