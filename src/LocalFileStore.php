<?php namespace Crunch\Salesforce;

class LocalFileStore implements TokenStoreInterface
{

    /**
     * @var AccessToken
     */
    private $accessToken;

    /**
     * @var string
     */
    private $filePath;

    /**
     * @var string
     */
    private $fileName = 'sf-key';


    /**
     * @param AccessToken $accessToken  An instance of the AccessToken object - needed
     * @param string      $filePath     The path where the file will be stored, no trailing slash, must be writable
     */
    public function __construct(AccessToken $accessToken, $filePath)
    {
        $this->accessToken = $accessToken;
        $this->filePath    = $filePath;
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
            throw new \Exception('SF access token not set');
        }

        $accessToken = $this->accessToken->createFromJson($accessTokenJson);

        return $accessToken;
    }

    /**
     * @param AccessToken $accessToken
     */
    public function saveAccessToken(AccessToken $accessToken)
    {
        //Save the encrypted access token to the shared filesystem
        file_put_contents($this->filePath . '/' . $this->fileName, $accessToken->toJson());
    }
}
