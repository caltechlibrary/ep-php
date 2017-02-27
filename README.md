# ep-php

EPrints 3.3 REST API wrapper for PHP. It provides a easy way to retrieve EPrints records
via the REST API as either XML or JSON.

## Requirements

+ php 7
+ A web server supporting PHP (e.g. Apache2, NginX) 

## Running tests

To run the tests you need to copy and edit _config.php-example_ to _config.php_.
Then you the tests against your repository for known EPrint IDs as follows
(E.g. eprint id is 12345).

```shell
    # Running tests with EPrint ID 12345
    php eprint_test.php 12345
```


