<?php namespace Crunch\Salesforce\Exceptions;

class RequestException extends \Exception {

    /**
     * @var
     */
    private $requestBody;

    /**
     * @param string $message
     * @param        $requestBody
     */
    function __construct($message, $requestBody)
    {
        $this->requestBody = $requestBody;
        parent::__construct($message);
    }

    /**
     * @return mixed
     */
    public function getRequestBody()
    {
        return $this->requestBody;
    }

}