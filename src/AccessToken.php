<?php namespace Crunch\Salesforce;

use Carbon\Carbon;

class AccessToken
{

    /**
     * @var string
     */
    private $id;

    /**
     * @var \Carbon\Carbon
     */
    private $dateIssued;

    /**
     * @var \Carbon\Carbon
     */
    private $dateExpires;

    /**
     * @var array
     */
    private $scope;

    /**
     * @var string
     */
    private $tokenType;

    /**
     * @var string
     */
    private $refreshToken;

    /**
     * @var string
     */
    private $signature;

    /**
     * @var string
     */
    private $accessToken;

    /**
     * @var string
     */
    private $apiUrl;

    /**
     * Create an access token object from the salesforce response data
     *
     * @param array $salesforceToken
     * @return AccessToken
     */
    public static function createFromSalesforceResponse(array $salesforceToken)
    {
        //Create an instance of this class to work with
        static $token = null;
        if ($token === null) {
            $token = new AccessToken();
        }

        $token->id = $salesforceToken['id'];

        $token->dateIssued   = Carbon::createFromTimestamp((int)($salesforceToken['issued_at']/1000));

        $token->dateExpires  = $token->dateIssued->copy()->addHour();

        $token->scope        = explode(' ', $salesforceToken['scope']);

        $token->tokenType    = $salesforceToken['token_type'];

        $token->refreshToken = $salesforceToken['refresh_token'];

        $token->signature    = $salesforceToken['signature'];

        $token->accessToken  = $salesforceToken['access_token'];

        $token->apiUrl       = $salesforceToken['instance_url'];

        return $token;
    }


    public function updateFromSalesforceRefresh(array $salesforceToken)
    {
        $this->dateIssued  = Carbon::createFromTimestamp((int)($salesforceToken['issued_at']/1000));

        $this->dateExpires = $this->dateIssued->copy()->addHour();

        $this->signature   = $salesforceToken['signature'];

        $this->accessToken = $salesforceToken['access_token'];
    }

    public static function createFromJson($text)
    {
        static $token = null;
        if ($token === null) {
            $token = new AccessToken();
        }

        $savedToken = json_decode($text, true);

        $token->id = $savedToken['id'];

        $token->dateIssued = Carbon::parse($savedToken['dateIssued']);

        $token->dateExpires = Carbon::parse($savedToken['dateExpires']);

        $token->scope = $savedToken['scope'];

        $token->tokenType = $savedToken['tokenType'];

        $token->refreshToken = $savedToken['refreshToken'];

        $token->signature = $savedToken['signature'];

        $token->accessToken = $savedToken['accessToken'];

        $token->apiUrl = $savedToken['apiUrl'];

        return $token;
    }

    /**
     * @return bool
     */
    public function needsRefresh()
    {
        return $this->dateExpires->lt(Carbon::now());
    }


    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id'           => $this->id,
            'dateIssued'   => $this->dateIssued->format('Y-m-d H:i:s'),
            'dateExpires'  => $this->dateExpires->format('Y-m-d H:i:s'),
            'scope'        => $this->scope,
            'tokenType'    => $this->tokenType,
            'refreshToken' => $this->refreshToken,
            'signature'    => $this->signature,
            'accessToken'  => $this->accessToken,
            'apiUrl'       => $this->apiUrl,
        ];
    }

    /**
     * @param int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * @return Carbon
     */
    public function getDateExpires()
    {
        return $this->dateExpires;
    }

    /**
     * @return Carbon
     */
    public function getDateIssued()
    {
        return $this->dateIssued;
    }

    /**
     * @return string
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @return array
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return $this->apiUrl;
    }

}