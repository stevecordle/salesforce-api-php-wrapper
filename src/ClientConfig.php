<?php namespace Crunch\Salesforce;

final class ClientConfig implements ClientConfigInterface {

    /**
     * @var
     */
    private $loginUrl;
    /**
     * @var
     */
    private $clientId;
    /**
     * @var
     */
    private $clientSecret;

    /**
     * @var
     */
    private $apiVersion;

    public function __construct($loginUrl, $clientId, $clientSecret, $apiVersion)
    {
        $this->loginUrl = $loginUrl;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->apiVersion = $apiVersion;
    }

    /**
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->loginUrl;
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @return string
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * @return string
     */
    public function getApiVersion()
    {
        return $this->apiVersion;
    }
}