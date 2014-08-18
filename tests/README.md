[![Build Status](https://travis-ci.org/MarketoMA/marketo_ma.svg?branch=7.x-1.x)](https://travis-ci.org/MarketoMA/marketo_ma)

## Install required components
The Marketo MA test suite uses Behat. Set it up in the regular way.

```
$ curl http://getcomposer.org/installer | php
$ php composer.phar install
```

## Configure Marketo test settings
The Marketo MA module comes with a behat.yml.dist file which must be copied and configured for your specific environment.

```
$ cd path/to/marketo_ma/tests
$ cp behat.yml.dist behat.yml
```

Modify behat.yml populating valid information for:
- marketo_settings
- base_url
- drupal_root
- root

## Execute tests
To execute all tests

```
$ cd path/to/marketo_ma/tests
$ bin/behat
```

Tags have been placed on most scenarios so the scope testing can be controlled

```
$ cd path/to/marketo_ma/tests
$ bin/behat --tags='config&&live'
```