**:warning: PLEASE NOTE: This repository is no longer actively maintained or supported. Please see [airbrake/phpbrake](https://github.com/airbrake/phpbrake) for a maintained airbrake monolog handler.**

---

## Installation

Add the following to your composer.json and run `composer update`

```json
{
    "require": {
        "wirebox/monolog-airbrake": "dev-master"
    }
}
```

## Usage

```php
$monolog->pushHandler(new Idio\MonologHandlers\AirbrakeHandler('AIRBRAKE TOKEN', array('airbrakeconfig' => 'here')));
```

#### Full example
```php
$monolog = new Logger('TestLog');
$monolog->pushHandler(new Idio\MonologHandlers\AirbrakeHandler('AIRBRAKE TOKEN', array('airbrakeconfig' => 'here')));
$monolog->addWarning('This is a warning logging message');
```
