# Services

## [s3 Service](https://github.com/phptuts/StarterBundleForSymfony/blob/master/Service/S3Service.php)

This service is used for file uploads.  We have an s3 client that takes in you aws setup information.  The s3 Service
 takes in 3 parameters.  This service also takes into account what environment you are in.  This way you never mix up
  staging environment with production.  For example say you are on dev and you want to store all your profile 
  pictures in a folder called profile_pics.  In your s3 bucket you will need to create this path in your bucket:
  
  -> dev/profile_pics/
 
```
public function uploadFile(UploadedFile $file, $folderPath, $fileName)
{

    $folderPath = !empty($folderPath) ?   $folderPath  . '/' : '';
    $path =   $this->env . '/' . $folderPath . $fileName . '.'. $file->guessClientExtension();
    /** @var Result $result */
    $result = $this->client->putObject([
        'ACL' => 'public-read',
        'Bucket' => $this->bucket,
        'SourceFile' => $file->getRealPath(),
        'Key' => $path
    ]);
    
    return $result->get('ObjectURL');
}
```

## [Auth Response Service](https://github.com/phptuts/StarterBundleForSymfony/blob/master/Service/AuthResponseService.php)

This service is responsible for setting an authentication cookie and producing credentialed responses.  A credentialed 
response will have a serialized version of the user, (jwt/jws) token, and a refresh token.  The authentication cookie
 is just a cookie that stores the jwt token.  It's called auth_cookie and expires when the jwt token expires.  

```
{
	"meta": {
		"type": "authentication",
		"paginated": false
	},
	"data": {
		"user": {
			"id": "9856cf42-d862-11e7-97a4-080027192ca4",
			"displayName": null,
			"roles": [
				"ROLE_USER"
			],
			"imageUrl": null,
			"email": "glaserpower@gmail.com",
			"bio": null
		},
		"tokenModel": {
			"token": "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXUyJ9.eyJ1c2VyX2lkIjoiOTg1NmNmNDItZDg2Mi0xMWU3LTk3YTQtMDgwMDI3MTkyY2E0IiwiZXhwIjoxNTE3NTE0NDM5LCJpYXQiOjE1MTIzMzA0MzksImRpc3BsYXlOYW1lIjpudWxsLCJyb2xlcyI6WyJST0xFX1VTRVIiXSwiaW1hZ2VVcmwiOm51bGwsImVtYWlsIjoiZ2xhc2VycG93ZXJAZ21haWwuY29tIiwiYmlvIjpudWxsfQ.IQcE61WrWzgJcgFcLJLZF9vJLI4I5Zz7s-xfCnzPAUXf1xVWDI_rgzF03qzah0J86MseXpvFNyroz7BgKngbsCSyOFIzuNa8JoCtEmHPVNkAjLv__8ByInpSZN9Sdm063_LHPNSZI5_L75yZSsQHd2T1f5R2259m8ToPSsZGZhZjbJlUB8qkJysBP6FQWdSRbZbNRASFXbstCLTOrzWtiTpX5WvTMvfn70JiV9JsMP-mutADYeNOnigvW9In_o63C5NYOmC55mJyxDnE4OehsDblXbGiu3SunxAcigPBhCiN5QrmL2fH1yVQ1CW7lDJGGNXveQTabDU1pS7-A6vJgGKE9qdGwyyNgxLBqqNfjxWphNweT5Ay48yu5iWRUPUA7braWw2A-pBYeDaNNQrCb3BEN07rzEQRJOpHe-yvAn_xtcCFh9IhmUJr_Ma9MMJehIJInJWJnLUYNC91-SLifQbrSxa0g8Ls-nrdYoS_oTHsXt6VTrgK53hd3PO-bJJR-80G1hU0UD9k8fIpyE6QX-hds0w2r2m2IL0pvt9skyttMs8sWC29lrlVoWOCGkhrraatHgZ2VPYPjhb1A1PsnKPtU5hfA4XpAhfc7NVT3tAPOe4XBI7yRS3hPkB5RKLvfPZ93ZFFfLCN7EFyLm-iBvqfQI2K8zT89FK2Wpt8oH8",
			"expirationTimeStamp": 1517514439
		},
		"refreshTokenModel": {
			"token": "5f964bc41fade25d0be1302ffa63c08d7d30f5d5e2756bf6d506ed7e4c1a0fd3ed9be46aaf199da4e701e69bac9158b6bfaf9b1c73f084ff8f35bfc293be8f560b99c59b11bb89233a06541371faddb5a899893b8bec1ca800d9",
			"expirationTimeStamp": 1522698439
		}
	}
}
```

## [UserService](https://github.com/phptuts/StarterBundleForSymfony/blob/master/Service/UserService.php)

This contains all the logic to save users, register users, as well as wrapping around the user repository.  The way 
this bundle gets around not knowing the concrete user class is by inject the class name into the service.


## [Form Serializer](https://github.com/phptuts/StarterBundleForSymfony/blob/master/Service/FormSerializer.php)

This is a copy and paste job from the jms serializer.  It serializes the the symfony forms like the FOS RestBundle / 
JMS Serializer bundle.


## [JWS Token Service](https://github.com/phptuts/StarterBundleForSymfony/blob/master/Service/JWSTokenService.php)

The purpose of this is to create authentication tokens that are not stored in the database that contain information 
will allow the server to look up the user.  This why every token's payload has the key user_id which is used to 
lookup the user.

A few things to note the BaseUser has a [getJWTPayload](https://github.com/phptuts/StarterBundleForSymfony/blob/master/Entity/BaseUser.php#L712) method.  This is used to populate the jwt token payload without
 events.  The other thing to note is that this service returns an [AuthModel](https://github.com/phptuts/StarterBundleForSymfony/blob/master/Model/Auth/AuthTokenModel.php) which is used to serialize the token 
 with the expiration date. 