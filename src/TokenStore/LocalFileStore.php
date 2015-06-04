<?php namespace Crunch\Salesforce\TokenStore;

use Crunch\Salesforce\AccessToken;
use Crunch\Salesforce\AccessTokenGenerator;

class LocalFileStore implements StoreInterface
{

    /**
     * @var AccessTokenGenerator
     */
    private $accessTokenGenerator;

    /**
     * @var string
     */
    private $filePath;

    /**
     * @var string
     */
    private $fileName = 'sf-key';


    /**
     * @param AccessTokenGenerator $accessTokenGenerator
     * @param string               $filePath The path where the file will be stored, no trailing slash, must be writable
     */
    public function __construct(AccessTokenGenerator $accessTokenGenerator, $filePath)
    {
        $this->accessTokenGenerator = $accessTokenGenerator;
        $this->filePath             = $filePath;
    }

    /**
     * @return AccessToken
     * @throws \Exception
     */
    public function fetchAccessToken()
    {
        try {
            $accessTokenJson = file_get_contents($this->filePath . '/' . $this->fileName);
        } catch (\ErrorException $e) {
            throw new \Exception('Salesforce access token not found');
        }

        return $this->accessTokenGenerator->createFromJson($accessTokenJson);
    }

    /**
     * @param AccessToken $accessToken
     */
    public function saveAccessToken(AccessToken $accessToken)
    {
        file_put_contents($this->filePath . '/' . $this->fileName, $accessToken->toJson());
    }
}
