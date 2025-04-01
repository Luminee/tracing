# tracing

## Installation

```php
composer require luminee/tracing
```

## Setup

### Middleware

```php
\Luminee\Tracing\Middlewares\TraceRequests::class
```

## Configuration

> Optional
```php
php artisan vendor:publish --provider="Luminee\Tracing\TracingServiceProvider"
```
#### .env

`TRACING_ENABLE=true`

`ZIPKIN_ENABLED=true`

`ZIPKIN_HOST=zipkin`

`ZIPKIN_PORT=9411`

`ZIPKIN_SERVICE_NAME=myservice`


## Usage

```php
use Luminee\Tracing\Facades\Tracing;

$uuid = Tracing::startMeasure('func 1', Tracing::getCurrentMeasureUuid());

// do something...

Tracing::stopMeasure($uuid);
```