<?php

/**
 * Assumptions:
 *
 * - It's OK to use floats for GBP amounts (the consumer of this API can round them all to 2 digits, which we assume to be OK as well)
 * - We assume a user can hold multiple cards.
 * - We assume valid inputs.
 * - For purposes of the coding test it's OK to chock all the classes into one big index.php file.
 * - We don't bother modelling actual Authorizations or Transactions (Authorization Requests will serve that role)
 * - It's OK not to persist any data if the PHP process crashes (no database).
 * - It's OK not to perform any logging.
 * - The only information we're concerned about for the merchants and the card owners is their ID.
 * - All IDs are unique integers.
 * - The card issuer approves authorization requests.
 * - We are the card issuer.
 * - Sending the money to the merchant after a transaction is captured happens outside of this system (we don't know
 *   the amount of money sent to each merchant).
 * - We don't bother modelling the merchant side of a refund (only receiving a refund).
 * - Reversing (part of) an authorization request/transaction can only happen after it has been approved.
 * - We assume "The merchant can capture the amount multiple times" means that the merchant can choose to capture part
 *   of the amount multiple times, but this cannot total more than the initally authorized amount.
 * - Amounts remain earmarked infinitely if they are not captured.
 * - We're not worried about the data store being down and the IDs that are passed in have been checked to be unique.
 *
 * TODO (and assumed to be out of scope for this coding test):
 * - create interfaces
 * - split out into separate files per PSR, add autoloading
 * - add input validation
 * - further unit test coverage
 */

<?php
/**
 * An example of a project-specific implementation.
 *
 * After registering this autoload function with SPL, the following line
 * would cause the function to attempt to load the \Foo\Bar\Baz\Qux class
 * from /path/to/project/src/Baz/Qux.php:
 *
 *      new \Foo\Bar\Baz\Qux;
 *
 * @param string $class The fully-qualified class name.
 * @return void
 */
spl_autoload_register(function ($class) {

    // project-specific namespace prefix
    $prefix = 'PrepaidCard\\';

    // base directory for the namespace prefix
    $base_dir = __DIR__ . '/src/';

    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }

    // get the relative class name
    $relative_class = substr($class, $len);

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});
