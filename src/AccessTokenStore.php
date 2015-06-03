<?php namespace Crunch\Salesforce;

use Illuminate\Encryption\Encrypter;

class AccessTokenStore
{

    /**
     * @var AccessToken
     */
    private $accessToken;

    /**
     * @var Encrypter
     */
    private $encrypter;

    function __construct(AccessToken $accessToken)
    {
        $this->accessToken = $accessToken;

        $this->encrypter = new Encrypter(env('ENCRYPTION_KEY'));
    }

    /**
     * @return AccessToken
     * @throws \Exception
     */
    public function fetchAccessToken()
    {
        try {
            $encryptedAccessToken = file_get_contents(wp_content_path('uploads/sf-key'));
        } catch (\ErrorException $e) {
            throw new \Exception('SF access token not set');
        }

        $accessTokenJson = $this->encrypter->decrypt($encryptedAccessToken);
        $accessToken     = $this->accessToken->createFromJson($accessTokenJson);

        return $accessToken;
    }

    /**
     * @param AccessToken $accessToken
     */
    public function saveAccessToken(AccessToken $accessToken)
    {
        //Encrypt the access token
        $encryptedAccessToken = $this->encrypter->encrypt($accessToken->toJson());

        //Save the encrypted access token to the shared filesystem
        file_put_contents(wp_content_path('uploads/sf-key'), $encryptedAccessToken);
    }
}