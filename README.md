# Salesforce PHP Library

A simple library for interacting with the Salesforce REST API.

Methods for setting up a connection, requesting an access token, refreshing the access token, saving the access token, and making calls against the API.

##Setting up the Salesforce client

The configuration data for the client is passed in through a config file which must implement `\Crunch\Salesforce\ClientConfigInterface`

For example

```
class SalesforceConfig implements \Crunch\Salesforce\ClientConfigInterface {

    /**
     * @return string
     */
    public function getLoginUrl()
    {
        return 'https://test.salesforce.com/';
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return 'clientid';
    }

    /**
     * @return string
     */
    public function getClientSecret()
    {
        return 'clientsecret';
    }
}
```

The Salesforce client can then be instantiated with the config object and an instance of Guzzle client.

```
$sfConfig = new SalesforceConfig();
$sfClient = new \Crunch\Salesforce\Client($sfConfig, new GuzzleHttp\Client());
```

##Authentication
Authentication happens via oauth2 and the login url can be generated using the `getLoginUrl` method, you should pass this your return url for the send stage of the oauth process.

```
$url = $sfClient->getLoginUrl('http://exmaple.com/sf-login');
```

You should redirect the user to this returned url, on completion they will be redirected back with a code in the query string.

The second stage of the authentication can then be completed.

```
$token = $sfClient->authorizeConfirm($_GET['code'], 'http://exmaple.com/sf-login');
```
The token returned from here is the raw data and can be passed to the access token generator to make an `AccessToken`.


```
$tokenGenerator = new \Crunch\Salesforce\AccessTokenGenerator();
$accessToken = $tokenGenerator->createFromSalesforceResponse($token);
```

###Storing the access token
This access token should be stored. A method to store this on the file system is provided but this isn't required.

The `LocalFileStore` object needs to be instantiated with access to the token generator and a config class which implements `\Crunch\Salesforce\TokenStore\LocalFileConfigInterface`

```
class SFLocalFileStoreConfig implements \Crunch\Salesforce\TokenStore\LocalFileConfigInterface {

    /**
     * The path where the file will be stored, no trailing slash, must be writable
     *
     * @return string
     */
    public function getFilePath()
    {
        return __DIR__;
    }
}

```
The token store can then be created and used to save the access token to the local file system as well as fetching a previously saved token.

```
$tokenStore = new \Crunch\Salesforce\TokenStore\LocalFile(new \Crunch\Salesforce\AccessTokenGenerator, new SFLocalFileStoreConfig);

//Save a token
$tokenStore->saveAccessToken($accessToken);

//Fetch a token
$accessToken = $tokenStore->fetchAccessToken();
```

###Refreshing the token
The access token only lasts 1 hour before expiring so you should regularly check its status and refresh it accordingly.

```
$accessToken = $tokenStore->fetchAccessToken();

if ($accessToken->needsRefresh()) {

	$update = $sfClient->refreshToken($token);
	
    $accessToken->updateFromSalesforceRefresh($update);

    $tokenStore->saveAccessToken($accessToken);
}

```

##Making requests

Before making a request you should instantiate the client as above and then assign the access token to it.

```
$sfConfig = new SalesforceConfig();
$sfClient = new \Crunch\Salesforce\Client($sfConfig, new \GuzzleHttp\Client());

$sfClient->setAccessToken($accessToken);

```

###Performing an SOQL Query
This is a powerful option for performing general queries against your salesforce data.
Simply pass a valid query to the search method and the resulting data will be returned.

```
$data = $sfClient->search('SELECT Email, Name FROM Lead LIMIT 10');
```

###Fetching a single record
If you know the id and type of a record you can fetch a set of fields from it.

```
$data = $sfClient->getRecord('Lead', '00WL0000008wVl1MDE', ['name', 'email', 'phone']);
```

###Creating and updating records
The process for creating and updating records is very similar and can be performed as follows.
The createRecord method will return the id of the newly created record.

```
$data = $sfClient->createRecord('Lead', ['email' => 'foo@example.com', 'Company' => 'New test', 'lastName' => 'John Doe']);

$sfClient->updateRecord('Lead', '00WL0000008wVl1MDE', ['lastName' => 'Steve Jobs']);

```

###Deleting records
Records can be deleted based on their id and type.

```
$sfClient->deleteRecord('Lead', '00WL0000008wVl1MDE');
```

##Errors
If something goes wrong guzzle will throw an exception, if this happens you should take a look at the response body for further information and correct the issue.

```
try {

	$sfClient->updateRecord('Lead', '00WL0000008wVl1MDE', ['lastName' => 'Steve Jobs']);

} catch (\GuzzleHttp\Exception\RequestException $e) {

    $e->getResponse()->getBody(); //Salesforce error message
    
}

```