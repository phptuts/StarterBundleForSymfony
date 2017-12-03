# Serialize Response

## Response Envelope

The important thing to note about the response envelope is that the meta data will always have a type.  This is what should be used to build a parser against.  It will also have a paginated field that will let the client know if it should expect an array or just a single object.

``` 
meta : {
    type: '',
    paginated: boolean,
},
data: ...
```

## Paginated Response

Paginated responses will have data required for the client to be get more less in the list.  

``` 
{
    "meta": {
        "type": "users", // Type of object
        "paginated": true,
        "total": 120, // The total number in database
        "page": 1, // The current page they are on, starts are 1
        "pageSize": 10, // The number of results in the request
        "numberOfPages": 12, // The total number of pages
        "lastPage": false // Whether the request on the last page
    },
    "data": [ ... ]  
}
```

These response are created by creating a [ResponsePageModel](https://github.com/phptuts/StarterBundleForSymfony/blob/master/Model/Response/ResponsePageModel.php)

## Form Errors

Form errors are serialized like they are in the jms serializer bundle.  There is a [function](https://github.com/phptuts/StarterBundleForSymfony/blob/master/Controller/BaseRestController.php#L37) BaseController that allows you to feed the form to it for serialization.

``` 
{
    "meta": {
        "type": "formErrors",
        "paginated": false
    },
    "data": {
        "children": {
            "email": {
                "errors": [
                    "This value should not be blank."
                ]
            },
            "plainPassword": {
                "errors": [
                    "This value should not be blank."
                ]
            }
        }
    }
}
```

## [ViewInterface](https://github.com/phptuts/StarterBundleForSymfony/blob/master/Entity/ViewInterface.php)

From what I have seen the most popular views to create of object are the single view and the list view.  We use these methods implemented in the user service to create the data to display to the frontend.
