<?php

namespace Crunch\Salesforce;

use Carbon\Carbon;

class AccessTokenGenerator
{

    /**
     * Create an access token from stored json data
     *
     * @param $text
     * @return AccessToken
     */
    public function createFromJson($text)
    {
        $savedToken = json_decode($text, true);

        $id = $savedToken['id'];

        $dateIssued = Carbon::parse($savedToken['dateIssued']);

        $dateExpires = Carbon::parse($savedToken['dateExpires']);

        $scope = $savedToken['scope'];

        $tokenType = $savedToken['tokenType'];

        $refreshToken = $savedToken['refreshToken'];

        $signature = $savedToken['signature'];

        $accessToken = $savedToken['accessToken'];

        $apiUrl = $savedToken['apiUrl'];

        $token = new AccessToken(
            $id,
            $dateIssued,
            $dateExpires,
            $scope,
            $tokenType,
            $refreshToken,
            $signature,
            $accessToken,
            $apiUrl
        );

        return $token;
    }

    /**
     * Create an access token object from the salesforce response data
     *
     * @param array $salesforceToken
     * @return AccessToken
     */
    public function createFromSalesforceResponse(array $salesforceToken)
    {

        $dateIssued = Carbon::createFromTimestamp(((int)$salesforceToken['issued_at']) / 1000);

        $dateExpires = $dateIssued->copy()->addHour()->subMinutes(5);

        $id = $this->getKeyIfSet($salesforceToken, 'id');

        $scope = explode(' ', $this->getKeyIfSet($salesforceToken, 'scope'));

        $refreshToken = $this->getKeyIfSet($salesforceToken, 'refresh_token');

        $signature = $this->getKeyIfSet($salesforceToken, 'signature');

        $tokenType = $this->getKeyIfSet($salesforceToken, 'token_type');

        $accessToken = $salesforceToken['access_token'];

        $apiUrl = $salesforceToken['instance_url'];

        $token = new AccessToken(
            $id,
            $dateIssued,
            $dateExpires,
            $scope,
            $tokenType,
            $refreshToken,
            $signature,
            $accessToken,
            $apiUrl
        );

        return $token;
    }

    /**
     * @param array $array
     * @param mixed $key
     * @return null
     */
    private function getKeyIfSet($array, $key)
    {
        if (isset($array[$key])) {
            return $array[$key];
        }
        return null;
    }
}