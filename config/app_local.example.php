<?php
/*
 * Local configuration file to provide any overrides to your app.php configuration.
 * Copy and save this file as app_local.php and make changes as required.
 * Note: It is not recommended to commit files with credentials such as app_local.php
 * into source code version control.
 */
return [
    /*
     * Debug Level:
     *
     * Production Mode:
     * false: No error messages, errors, or warnings shown.
     *
     * Development Mode:
     * true: Errors and warnings shown.
     */
    'debug' => filter_var(env('DEBUG', true), FILTER_VALIDATE_BOOLEAN),

    /*
     * Security and encryption configuration
     *
     * - salt - A random string used in security hashing methods.
     *   The salt value is also used as the encryption key.
     *   You should treat it as extremely sensitive data.
     */
    'Security' => [
        'salt' => env('SECURITY_SALT', 'dc9b52ff68cc0d05c0b97a060456f9d8b1f7247058d27e5851f652a39a1b3a51'),
    ],

    /*
     * Connection information used by the ORM to connect
     * to your application's datastores.
     *
     * See app.php for more configuration options.
     */
    // substitua pelo trecho contido no readme --------------------------------------------------------------------
    'Datasources' => [
        'default' => [
            'username' => env('DATABASE_USER', 'postgres'),
            'password' => env('DATABASE_PASS', 'password'),
            'database' => env('DATABASE_DB', 'farm_vet'),
            'host'     => env('DATABASE_HOST', 'localhost'),
            'port'     => env('DATABASE_PORT', 5432),
            'className' => 'Cake\Database\Connection',
            'driver' => 'Cake\Database\Driver\Postgres',
            'persistent' => true,
            'encoding' => 'utf8',
            'timezone' => 'UTC',
            'schema' => env('DATABASE_SCHEMA', 'public'),
            'url' => env('DATABASE_URL', null),
        ],

        /*
         * The test connection is used during the test suite.
         */
        'test' => [
            'className' => 'Cake\Database\Connection',
            'driver' => 'Cake\Database\Driver\Postgres',
            'username' => env('DATABASE_TEST_USERNAME', 'postgres'),
            'password' => env('DATABASE_TEST_PASSWORD', 'password'),
            'database' => env('DATABASE_TEST_NAME', 'farm_vet_test'),
            'host'     => env('DATABASE_TEST_HOST', 'localhost'),
            'port'     => env('DATABASE_TEST_PORT', 5432),
            'encoding' => 'utf8',
            'schema' => env('DATABASE_TEST_SCHEMA', 'public'),
            'url' => env('DATABASE_TEST_URL', null),
        ],
    ],
// --------------------------------------------------------------------------------------------------------------------------------------
    /*
     * Email configuration.
     *
     * Host and credential configuration in case you are using SmtpTransport
     *
     * See app.php for more configuration options.
     */
    'EmailTransport' => [
        'default' => [
            'host' => 'localhost',
            'port' => 25,
            'username' => null,
            'password' => null,
            'client' => null,
            'url' => env('EMAIL_TRANSPORT_DEFAULT_URL', null),
        ],
    ],
];
