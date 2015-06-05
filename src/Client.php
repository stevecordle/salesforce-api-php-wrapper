<?php namespace Crunch\Salesforce;

class Client
{
    /**
     * @var string
     */
    protected $salesforceLoginUrl;

    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $clientSecret;

    /**
     * @var AccessToken
     */
    private $accessToken;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var \GuzzleHttp\Client
     */
    private $guzzleClient;


    /**
     * @param ClientConfigInterface $clientConfigInterface
     * @param \GuzzleHttp\Client    $guzzleClient
     */
    function __construct(ClientConfigInterface $clientConfigInterface, \GuzzleHttp\Client $guzzleClient)
    {
        $this->salesforceLoginUrl = $clientConfigInterface->getLoginUrl();
        $this->clientId           = $clientConfigInterface->getClientId();
        $this->clientSecret       = $clientConfigInterface->getClientSecret();
        $this->guzzleClient       = $guzzleClient;
    }

    /**
     * Fetch a specific object
     *
     * @param string $objectType
     * @param string $sfId
     * @param array  $fields
     * @return string
     */
    public function getRecord($objectType, $sfId, array $fields)
    {
        $url      = $this->baseUrl . '/services/data/v20.0/sobjects/' . $objectType . '/' . $sfId . '?fields=' . implode(',', $fields);
        $response = $this->guzzleClient->get($url, ['headers' => ['Authorization' => $this->getAuthHeader()]]);

        return json_decode($response->getBody(), true);
    }

    /**
     * Execute an SOQL query and return the result set
     * This will loop through large result sets collecting all the data so the query should be limited
     *
     * @param null $query
     * @param bool $next_url
     * @return array
     * @throws \Exception
     */
    public function search($query = null, $next_url = false)
    {
        if ($next_url) {
            $url = $this->baseUrl . '/' . $next_url;
        } else {
            $url = $this->baseUrl . '/services/data/v24.0/query/?q=' . urlencode($query);
        }
        $response = $this->guzzleClient->get($url, ['headers' => ['Authorization' => $this->getAuthHeader()]]);
        $data     = json_decode($response->getBody(), true);

        $results = $data['records'];
        if ( ! $data['done']) {
            $more_results = $this->search(null, substr($data['nextRecordsUrl'], 1));
            if ($more_results) {
                $results = array_merge($results, $more_results);
            }
            //$data['totalSize'];		//Total records
            //count($data['records']);	//Number returned
            //$data['nextRecordsUrl'];	//more records url
        }

        return $results;
    }

    /**
     * Make an update request
     *
     * @param string $object The object type to update
     * @param string $id The ID of the record to update
     * @param array  $data The data to put into the record
     * @return bool
     * @throws \Exception
     */
    public function updateRecord($object, $id, array $data)
    {
        $url = $this->baseUrl . '/services/data/v20.0/sobjects/' . $object . '/' . $id;

        $this->guzzleClient->patch($url, [
            'headers' => ['Content-Type' => 'application/json', 'Authorization' => $this->getAuthHeader()],
            'body'    => json_encode($data)
        ]);

        return true;
    }

    /**
     * Create a new object in salesforce
     *
     * @param string $object
     * @param string $data
     * @return bool
     * @throws \Exception
     */
    public function createRecord($object, $data)
    {
        $url = $this->baseUrl . '/services/data/v20.0/sobjects/' . $object . '/';

        $response     = $this->guzzleClient->post($url, [
            'headers' => ['Content-Type' => 'application/json', 'Authorization' => $this->getAuthHeader()],
            'body'    => json_encode($data)
        ]);
        $responseBody = json_decode($response->getBody(), true);

        return $responseBody['id'];
    }

    /**
     * Delete an object with th specified id
     *
     * @param $object
     * @param $id
     * @return bool
     * @throws \Exception
     */
    public function deleteRecord($object, $id)
    {
        $url = $this->baseUrl . '/services/data/v20.0/sobjects/' . $object . '/' . $id;

        $this->guzzleClient->delete($url, ['headers' => ['Authorization' => $this->getAuthHeader()]]);

        return true;
    }

    /**
     * Complete the oauth process by confirming the code and returning an access token
     *
     * @param $code
     * @param $redirect_url
     * @return array|mixed
     * @throws \Exception
     */
    public function authorizeConfirm($code, $redirect_url)
    {
        $url = $this->salesforceLoginUrl . 'services/oauth2/token';

        $post_data = [
            'grant_type'    => 'authorization_code',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code'          => $code,
            'redirect_uri'  => $redirect_url
        ];

        $response = $this->guzzleClient->post($url, ['form_params' => $post_data]);

        return json_decode($response->getBody(), true);
    }

    /**
     * Get the url to redirect users to when setting up a salesforce access token
     *
     * @param $redirectUrl
     * @return string
     */
    public function getLoginUrl($redirectUrl)
    {
        $params = [
            'client_id'     => $this->clientId,
            'redirect_uri'  => $redirectUrl,
            'response_type' => 'code',
            'grant_type'    => 'authorization_code'
        ];

        return $this->salesforceLoginUrl . 'services/oauth2/authorize?' . http_build_query($params);
    }

    /**
     * Refresh an existing access token
     *
     * @param AccessToken $accessToken
     * @return array|mixed
     * @throws \Exception
     */
    public function refreshToken(AccessToken $accessToken)
    {
        $url = $this->salesforceLoginUrl . 'services/oauth2/token';

        $post_data = [
            'grant_type'    => 'refresh_token',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $accessToken->getRefreshToken()
        ];

        $response = $this->guzzleClient->post($url, ['form_params' => $post_data]);

        return json_decode($response->getBody(), true);
    }

    /**
     * @param AccessToken $accessToken
     */
    public function setAccessToken(AccessToken $accessToken)
    {
        $this->accessToken = $accessToken;
        $this->baseUrl     = $accessToken->getApiUrl();
    }

    /**
     * @return string
     */
    private function getAuthHeader()
    {
        return 'Bearer ' . $this->accessToken->getAccessToken();
    }

}