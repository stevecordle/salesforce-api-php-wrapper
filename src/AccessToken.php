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
     * @param string         $id
     * @param \Carbon\Carbon $dateIssued
     * @param \Carbon\Carbon $dateExpires
     * @param array          $scope
     * @param string         $tokenType
     * @param string         $refreshToken
     * @param string         $signature
     * @param string         $accessToken
     * @param string         $apiUrl
     */
    public function __construct(
        $id,
        $dateIssued,
        $dateExpires,
        $scope,
        $tokenType,
        $refreshToken,
        $signature,
        $accessToken,
        $apiUrl
    ) {
        $this->id           = $id;
        $this->dateIssued   = $dateIssued;
        $this->dateExpires  = $dateExpires;
        $this->scope        = $scope;
        $this->tokenType    = $tokenType;
        $this->refreshToken = $refreshToken;
        $this->signature    = $signature;
        $this->accessToken  = $accessToken;
        $this->apiUrl       = $apiUrl;
    }


    public function updateFromSalesforceRefresh(array $salesforceToken)
    {
        $this->dateIssued = Carbon::createFromTimestamp((int)($salesforceToken['issued_at'] / 1000));

        $this->dateExpires = $this->dateIssued->copy()->addHour()->subMinutes(5);

        $this->signature = $salesforceToken['signature'];

        $this->accessToken = $salesforceToken['access_token'];
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