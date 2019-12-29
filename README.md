# yii2-splunk-log-target

Allows you to to send logs to your Splunk Enterprise.

## Installation

```
composer require "we-push-it/yii2-splunk-log-target"
```

## Usage

In order to use `SplunkTarget` you should configure your `log` application component like the following:  

```php
return [
    // ...
    'bootstrap' => ['log'],    
    // ...    
    'components' => [
        // ...        
        'log' => [
            'targets' => [
                [
                    'class' => 'wepushit\log\SplunkTarget',
                    
                    // It is optional parameter. The message levels that this target is interested in.
                    // The parameter can be an array.
                    'levels' => ['error', 'warning'],
                ],
                // ...
            ],
        ],
    ],
];
```

additionally you should adjust your params-config:

```php
return [
    // ...
    'splunk' => [
        'host' => isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'example server',
        'token' => 'token from your Splunk HTTP Event Collector',
        'url' => 'https://example.com:8088/services/collector/event',
        'source' => 'example',
        'sourcetype' => 'yii-errorhandler',
    ],
];
```


Standard usage:

```php
Yii::info('Info message');
Yii::error('Error message');
```

