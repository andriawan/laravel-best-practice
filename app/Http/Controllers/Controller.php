<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    description: 'This is a minimal Laravel application built with standard best practices. It includes JWT bearer token authentication via [Firebase JWT PHP](https://github.com/firebase/php-jwt) , supports auto-reloading .env configuration, and provides clean, self-documented APIs using Swagger/OpenAPI. Designed as a lightweight foundation for scalable, secure RESTful services.',
    title: 'Laravel Best Practice',
    termsOfService: 'http://swagger.io/terms/',
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
#[OA\SecurityScheme(
    securityScheme: 'basicAuth',
    type: 'http',
    scheme: 'basic'
)]
abstract class Controller
{
    //
}
