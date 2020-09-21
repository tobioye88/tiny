# Tiny Framework
> Version 1.0.0
   


Tiny framework is a quick starting point for bootstrapping PHP web apps.
Tiny framework was built with Developers in mind. This make it easy for developers to quickly create APIs and wep apps



```php

// Hello World

$app->get('/', function(IRequest $req, IResponse $res){
    $res->json(['response' => 'Hello, World!']);
});

```

### Application Route

Routes are created in the routes file located in the [app/routes/route.php](/app/routes/route.php)

Available Methods are GET, PUT, PATCH, POST, DELETE, OPTION

```php

$app->get('/', function(IRequest $req, IResponse $res){
    $res->json(['response' => 'Hello World']);
});
$app->put('/', function(IRequest $req, IResponse $res){});
$app->patch('/', function(IRequest $req, IResponse $res){});
$app->post('/', function(IRequest $req, IResponse $res){});
$app->delete('/', function(IRequest $req, IResponse $res){});

```

### Application Views
Views are located in the app folder `app/views/index`  
Use the variables in the view file like they were declared in the file;

```php

$app->get('/', function(IRequest $req, IResponse $res){
    $res->view('index');
});

// with data

$app->get('/', function(IRequest $req, IResponse $res){
    $numbers = [1,2,3,4,5];
    $name = 'John Doe';
    $res->view('index', compact('numbers', 'name'));
});

```

### Path Parameter

```php

$group->get('/posts/{id}', function(IRequest $req, IResponse $res){
    $id = $req->getPathParam('id');
    $res->json(['post_id' => $id]);
});

```

### Query Parameter

```php

// Route http://localhost/posts?id=1

$group->get('/posts', function(IRequest $req, IResponse $res){
    $id = $req->getQueryParam('id');
    $res->json(['post_id' => $id]);
});

```


### Group Routes

For grouping routes  
eg:
- /admin/posts
- /admin/posts/comments

```php

$app->group('/admin', function($group){
    $group->get('/posts', function(IRequest $req, IResponse $res){});

    $group->get('/posts/comments', function(IRequest $req, IResponse $res){});
});

```


## Middleware

All middleware must implement the interface `IMiddleware`  
eg:

```php

class AuthorizedMiddleware implements IMiddleware {
    public function handle(IRequest $req, IResponse $res){
        $token = $req->getHeader('token');
        // Check if token is valid
        if(!JWT::verify($token, JWT_SECRET)){
            throw new HttpUnauthorizedException("Unauthorized Request");
        }
    }
}

```

When validation for middleware fails throw an exception to interrupt the flow.

There are three levels at which middle ware can be applied
1. Application level
2. Group level
3. Route level 

### Application Level Middleware

```php

$ipMiddleware = new IPMiddleware();
$app->middleware([$ipMiddleware]);

```

### Group Middleware

```php

$authMiddleware = new AuthorizedMiddleware();

$app->group('/admin', function($group){
    $group->get('', function(IRequest $req, IResponse $res){});
}, [$authMiddleware]);

```


### Route Middleware

```php

$authMiddleware = new AuthorizedMiddleware();

$app->get('admin/users', function(IRequest $req, IResponse $res){}, [$superAuthMiddleware]);

// OR

$authMiddleware = new AuthorizedMiddleware();
$routeMiddleware = new RouteMiddleware();

$app->group('/admin', function($group){

    $group->get('', function(IRequest $req, IResponse $res){}, [$superAuthMiddleware]);

}, [$authMiddleware]);

```

