<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * Definition of internal relations for data dictionary tables.
 *
 * @package PhpMyAdmin
 */
if (!defined('PHPMYADMIN')) {
    exit;
}

/**
 *
 */
$GLOBALS['data_dictionary_relations'] = [
    'CHARACTER_SETS'         => [
        'DEFAULT_COLLATE_NAME' => [
            'foreign_db'    => 'data_dictionary',
            'foreign_table' => 'COLLATIONS',
            'foreign_field' => 'COLLATION_NAME',
        ],
    ],
    'COLLATIONS'             => [
        'CHARACTER_SET_NAME' => [
            'foreign_db'    => 'data_dictionary',
            'foreign_table' => 'CHARACTER_SETS',
            'foreign_field' => 'CHARACTER_SET_NAME',
        ],
    ],
    'COLUMNS'                => [
        'TABLE_SCHEMA'   => [
            'foreign_db'    => 'data_dictionary',
            'foreign_table' => 'SCHEMAS',
            'foreign_field' => 'SCHEMA_NAME',
        ],
        'COLLATION_NAME' => [
            'foreign_db'    => 'data_dictionary',
            'foreign_table' => 'COLLATIONS',
            'foreign_field' => 'COLLATION_NAME',
        ],
    ],
    'INDEXES'                => [
        'TABLE_SCHEMA' => [
            'foreign_db'    => 'data_dictionary',
            'foreign_table' => 'SCHEMAS',
            'foreign_field' => 'SCHEMA_NAME',
        ],
    ],
    'INDEX_PARTS'            => [
        'TABLE_SCHEMA' => [
            'foreign_db'    => 'data_dictionary',
            'foreign_table' => 'SCHEMAS',
            'foreign_field' => 'SCHEMA_NAME',
        ],
    ],
    'INNODB_LOCKS'           => [
        'LOCK_TRX_ID' => [
            'foreign_db'    => 'data_dictionary',
            'foreign_table' => 'INNODB_TRX',
            'foreign_field' => 'TRX_ID',
        ],
    ],
    'INNODB_LOCK_WAITS'      => [
        'REQUESTING_TRX_ID' => [
            'foreign_db'    => 'data_dictionary',
            'foreign_table' => 'INNODB_TRX',
            'foreign_field' => 'TRX_ID',
        ],
        'REQUESTED_LOCK_ID' => [
            'foreign_db'    => 'data_dictionary',
            'foreign_table' => 'INNODB_LOCKS',
            'foreign_field' => 'LOCK_ID',
        ],
        'BLOCKING_TRX_ID'   => [
            'foreign_db'    => 'data_dictionary',
            'foreign_table' => 'INNODB_TRX',
            'foreign_field' => 'TRX_ID',
        ],
        'BLOCKING_LOCK_ID'  => [
            'foreign_db'    => 'data_dictionary',
            'foreign_table' => 'INNODB_LOCKS',
            'foreign_field' => 'LOCK_ID',
        ],
    ],
    'INNODB_SYS_COLUMNS'     => [
        'TABLE_ID' => [
            'foreign_db'    => 'data_dictionary',
            'foreign_table' => 'INNODB_SYS_TABLES',
            'foreign_field' => 'TABLE_ID',
        ],
    ],
    'INNODB_SYS_FIELDS'      => [
        'INDEX_ID' => [
            'foreign_db'    => 'data_dictionary',
            'foreign_table' => 'INNODB_SYS_INDEXES',
            'foreign_field' => 'INDEX_ID',
        ],
    ],
    'INNODB_SYS_INDEXES'     => [
        'TABLE_ID' => [
            'foreign_db'    => 'data_dictionary',
            'foreign_table' => 'INNODB_SYS_TABLES',
            'foreign_field' => 'TABLE_ID',
        ],
    ],
    'INNODB_SYS_TABLESTATS'  => [
        'TABLE_ID' => [
            'foreign_db'    => 'data_dictionary',
            'foreign_table' => 'INNODB_SYS_TABLES',
            'foreign_field' => 'TABLE_ID',
        ],
    ],
    'PLUGINS'                => [
        'MODULE_NAME' => [
            'foreign_db'    => 'data_dictionary',
            'foreign_table' => 'MODULES',
            'foreign_field' => 'MODULE_NAME',
        ],
    ],
    'SCHEMAS'                => [
        'DEFAULT_COLLATION_NAME' => [
            'foreign_db'    => 'data_dictionary',
            'foreign_table' => 'COLLATIONS',
            'foreign_field' => 'COLLATION_NAME',
        ],
    ],
    'TABLES'                 => [
        'TABLE_SCHEMA'    => [
            'foreign_db'    => 'data_dictionary',
            'foreign_table' => 'SCHEMAS',
            'foreign_field' => 'SCHEMA_NAME',
        ],
        'TABLE_COLLATION' => [
            'foreign_db'    => 'data_dictionary',
            'foreign_table' => 'COLLATIONS',
            'foreign_field' => 'COLLATION_NAME',
        ],
    ],
    'TABLE_CACHE'            => [
        'TABLE_SCHEMA' => [
            'foreign_db'    => 'data_dictionary',
            'foreign_table' => 'SCHEMAS',
            'foreign_field' => 'SCHEMA_NAME',
        ],
    ],
    'TABLE_CONSTRAINTS'      => [
        'CONSTRAINT_SCHEMA' => [
            'foreign_db'    => 'data_dictionary',
            'foreign_table' => 'SCHEMAS',
            'foreign_field' => 'SCHEMA_NAME',
        ],
        'TABLE_SCHEMA'      => [
            'foreign_db'    => 'data_dictionary',
            'foreign_table' => 'SCHEMAS',
            'foreign_field' => 'SCHEMA_NAME',
        ],
    ],
    'TABLE_DEFINITION_CACHE' => [
        'TABLE_SCHEMA' => [
            'foreign_db'    => 'data_dictionary',
            'foreign_table' => 'SCHEMAS',
            'foreign_field' => 'SCHEMA_NAME',
        ],
    ],
];

?>
