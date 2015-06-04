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
     * @var \Crunch\Salesforce\AccessToken
     */
    private $accessToken;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @param ClientConfigInterface $clientConfigInterface
     * @internal param string $salesforceLoginUrl
     * @internal param string $clientId
     * @internal param string $clientSecret
     */
    function __construct(ClientConfigInterface $clientConfigInterface)
    {
        $this->salesforceLoginUrl = $clientConfigInterface->getLoginUrl();
        $this->clientId           = $clientConfigInterface->getClientId();
        $this->clientSecret       = $clientConfigInterface->getClientSecret();
    }


    /**
     * Update a specific record
     *
     * @param string $object The type of object to update
     * @param string $recordId The record to update
     * @param array  $updateData The data to update
     * @return bool
     * @throws \Exception
     */
    public function updateRecord($object, $recordId, array $updateData)
    {
        return $this->update($object, $recordId, $updateData);
    }


    /**
     * Fetch a specific object
     *
     * @param $sfId
     * @return string
     * @throws \Exception
     */
    public function getRecord($sfId)
    {
        return $this->makeCurlCall('get', $this->baseUrl . '/' . $sfId);
    }


    /**
     * Create a new object
     *
     * @param string $object
     * @param array  $data
     * @return bool
     * @throws \Exception
     */
    public function createRecord($object, $data)
    {
        return $this->create($object, $data);
    }


    /**
     * Execute an SOQL query and return the result set
     *
     * @param null $query
     * @param bool $next_url
     * @return array
     * @throws \Exception
     */
    private function search($query = null, $next_url = false)
    {
        if ($next_url) {
            $url = $this->baseUrl . '/' . $next_url;
        } else {
            $url = $this->baseUrl . '/services/data/v24.0/query/?q=' . urlencode($query);
        }
        $data = $this->makeCurlCall('get', $url);

        $results = $data['records'];
        if (!$data['done']) {
            $more_results = $this->search(null, substr($data['nextRecordsUrl'], 1));
            if ($more_results) {
                $results = array_merge($results, $more_results);
            }
            //$data['totalSize'];		    //Total records
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
    private function update($object, $id, $data)
    {
        $url = $this->baseUrl . '/services/data/v20.0/sobjects/' . $object . '/' . $id;

        $data = json_encode($data);

        $response = $this->makeCurlCall('patch', $url, $data);

        if ($response['success']) {
            return $response['id'];
        }
        throw new \Exception('Error updating the record');
    }


    /**
     * Create a new object in salesforce
     *
     * @param string $object
     * @param string $data
     * @return bool
     * @throws \Exception
     */
    private function create($object, $data)
    {
        $url = $this->baseUrl . '/services/data/v20.0/sobjects/' . $object . '/';

        $data = json_encode($data);

        $response = $this->makeCurlCall('post', $url, $data);

        if ($response['success']) {
            return $response['id'];
        }

        throw new \Exception('Error creating the record');
    }


    /**
     * Delete an object with th specified id
     *
     * @param $object
     * @param $id
     * @return bool
     * @throws \Exception
     */
    private function delete($object, $id)
    {
        $url = $this->baseUrl . '/services/data/v20.0/sobjects/' . $object . '/' . $id;

        $response = $this->makeCurlCall('delete', $url);

        if ($response['success']) {
            return $response['id'];
        }

        return false;
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

        $post_data = array(
            'grant_type'    => 'authorization_code',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code'          => $code,
            'redirect_uri'  => $redirect_url
        );

        $return = $this->makeCurlCall('post', $url, $post_data, false);

        return $return;
    }

    /**
     * Get the url to redirect users to when setting up a salesforce access token
     *
     * @param $redirectUrl
     * @return string
     */
    public function getLoginUrl($redirectUrl)
    {
        $params = array(
            'client_id'     => $this->clientId,
            'redirect_uri'  => $redirectUrl,
            'response_type' => 'code',
            'grant_type'    => 'authorization_code'
        );

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

        $post_data = array(
            'grant_type'    => 'refresh_token',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $accessToken->getRefreshToken()
        );

        // create a new cURL resource
        $data = $this->makeCurlCall('post', $url, $post_data, false);

        return $data;
    }

    /**
     * @param \Crunch\Salesforce\AccessToken $accessToken
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
        $this->baseUrl     = $accessToken->getApiUrl();
    }

    /**
     * @return array
     */
    private function getRequestHeaders()
    {
        $headers = array(
            "Content-Type: application/json",
            "Authorization: Bearer " . $this->accessToken->getAccessToken()
        );

        return $headers;
    }

    /**
     * Create a curl instance and set it up
     *
     * @param        $method
     * @param        $url
     * @param string $data
     * @param bool   $includeHttpHeaders
     * @return array
     * @throws \Exception
     */
    private function makeCurlCall($method, $url, $data = null, $includeHttpHeaders = true)
    {
        $method = strtolower($method);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        if ($includeHttpHeaders) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getRequestHeaders());
        }
        if (in_array($method, ['patch', 'delete'])) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        }
        if ($method === 'post') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // grab URL and pass it to the browser
        $data = curl_exec($ch);

        //Decode the returned data
        if (is_string($data)) {
            $return = json_decode($data, true);
        } else {
            throw new \Exception("Error decoding response");
        }

        //If there is a global error throw that
        if (isset($return['error'])) {
            throw new \Exception($return['error']);
        }

        //We may have an array of errors, if so extract and throw the first message
        if (isset($return['errors']) && !empty($return['errors'])) {
            if (isset($return['errors']['errorCode']) && $return['errors']['errorCode']) {
                throw new \Exception($return['errors']['message']);
            }
        }

        //If there was an error we should have picked it up by know and thrown it
        // Just in case confirm we had a success response and if not throw a generic error
        $statusCode = (integer)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($statusCode < 200 || $statusCode >= 300) {
            throw new \Exception("Error talking to salesforce. " . $statusCode);
        }

        // close cURL resource, and free up system resources
        curl_close($ch);

        return $return;
    }

}