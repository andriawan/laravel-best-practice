# Laravel Best Practice

This is a minimal Laravel application built with standard best practices. It includes JWT bearer token authentication via [Firebase JWT PHP](https://github.com/firebase/php-jwt) , supports auto-reloading .env configuration, and provides clean, self-documented APIs using Swagger/OpenAPI. Designed as a lightweight foundation for scalable, secure RESTful services.

## Coverage Status

[![codecov](https://codecov.io/gh/andriawan/laravel-best-practice/graph/badge.svg?token=DPY2JOC50V)](https://codecov.io/gh/andriawan/laravel-best-practice)

## Prerequisites

This project strongly recommend using JWTs signed with **asymmetric encryption** (e.g., RSA).  
You need to generate a **public/private key pair** and place them in the default dir

```bash
storage/app/private/private.key
```

or you can custom the dir through `.env`

```bash
# JWT
JWT_PRIVATE_KEY=/var/www/html/app/private/private.key
JWT_PUBLIC_KEY=/var/www/html/app/private/public.key
```

### Required Files

- 🔐 `private.key` — used to **sign** the JWT
- 🔓 `public.key` — used to **verify** the JWT

### 🔧 Generate Keys with OpenSSL

If you don't already have the keys, you can generate them using the following commands:

```bash
# Generate private key (2048-bit RSA)
openssl genrsa -out private.key 2048

# Extract the corresponding public key
openssl rsa -in private.key -pubout -out public.key
```

## Features

### ✅ JWT Authentication using Firebase JWT PHP

Implements stateless authentication with JSON Web Tokens for robust and secure REST API access control. By fully leveraging Spring Security and the OAuth2 Resource Server capabilities, this approach minimizes complexity and avoids reinventing boilerplate code—ensuring a clean, maintainable, and production-ready security setup.

### ✅ Token issuance follows RFC 7617 using HTTP Basic Authentication

Implements token issuance by authenticating clients via HTTP Basic Authentication as defined in RFC 7617. This standard method securely transmits client credentials to the token endpoint, ensuring trusted clients can obtain tokens safely and efficiently.

### ✅ Secure refresh token handling — blacklists tokens after use or on logout  

Implements refresh token revocation by blacklisting used or explicitly revoked tokens to prevent reuse and enhance security.

### ✅ API Documentation via Open API and Swagger

Integrates Swagger for API Documentation using lib [wotz/laravel-swagger-ui](https://github.com/wotzebra/laravel-swagger-ui). 
Run command bellow for generating JSON scheme openapi file.

```
./vendor/bin/openapi app -f json -o ./resources/swagger/openapi.json
```

this command will generate json file named `openapi.json` under folder `resources/swagger`. 
You can customize the name and folder path based on swagger config located on `config/swagger-ui.php`

```
/*
* The versions of the swagger file. The key is the version name and the value is the path to the file.
*/
'versions' => [
    'v1' => resource_path('swagger/openapi.json'),
],

```

### ✅ Versioning with Semantic Release

This repository follow commit convention to respect semantic release. All commit merged to main branch will trigger release workflow.
This will generate auto commit release, changelog, release tag and version bumping.

### ✅ Docker Build Ready for Production

This repository provide docker for deploying app seamlessly. it uses base image from [frankenphp](https://frankenphp.dev/)

### ✅ Laravel Pint for code formatting  

Integrates Laravel Pint to enforce consistent code style and automatically format your code to standard conventions, improving readability and maintainability.

### ✅ Rate Limiting via `.env` properties

By default rate limiting always implemented via middleware with default 60 request per minutes. You can config using env key `RATE_LIMIT_PER_MINUTE`

### ✅ Dependency Updates

Regularly updates project dependencies to ensure security, performance, and compatibility with the latest versions.

Perfect as a starter template for any Java backend project!
Check it out and feel free to ⭐ or fork!