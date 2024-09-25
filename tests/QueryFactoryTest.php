<?php

declare(strict_types=1);

namespace Aura\SqlQuery;

use PHPUnit\Framework\TestCase;

class QueryFactoryTest extends TestCase
{
    /**
     * @dataProvider provider
     */
    public function test($db_type, $common, $query_type, $expect): void
    {
        $query_factory = new QueryFactory($db_type, $common);
        $method = 'new' . $query_type;
        $actual = $query_factory->{$method}();
        $this->assertInstanceOf($expect, $actual);
    }

    public function provider()
    {
        return [
            // db-specific
            ['Common', false, 'Select', Common\Select::class],
            ['Common', false, 'Insert', Common\Insert::class],
            ['Common', false, 'Update', Common\Update::class],
            ['Common', false, 'Delete', Common\Delete::class],
            ['Mysql', false, 'Select', Mysql\Select::class],
            ['Mysql', false, 'Insert', Mysql\Insert::class],
            ['Mysql', false, 'Update', Mysql\Update::class],
            ['Mysql', false, 'Delete', Mysql\Delete::class],
            ['Pgsql', false, 'Select', Pgsql\Select::class],
            ['Pgsql', false, 'Insert', Pgsql\Insert::class],
            ['Pgsql', false, 'Update', Pgsql\Update::class],
            ['Pgsql', false, 'Delete', Pgsql\Delete::class],
            ['Sqlite', false, 'Select', Sqlite\Select::class],
            ['Sqlite', false, 'Insert', Sqlite\Insert::class],
            ['Sqlite', false, 'Update', Sqlite\Update::class],
            ['Sqlite', false, 'Delete', Sqlite\Delete::class],
            ['Sqlsrv', false, 'Select', Sqlsrv\Select::class],
            ['Sqlsrv', false, 'Insert', Sqlsrv\Insert::class],
            ['Sqlsrv', false, 'Update', Sqlsrv\Update::class],
            ['Sqlsrv', false, 'Delete', Sqlsrv\Delete::class],

            // force common
            ['Common', QueryFactory::COMMON, 'Select', Common\Select::class],
            ['Common', QueryFactory::COMMON, 'Insert', Common\Insert::class],
            ['Common', QueryFactory::COMMON, 'Update', Common\Update::class],
            ['Common', QueryFactory::COMMON, 'Delete', Common\Delete::class],
            ['Mysql', QueryFactory::COMMON, 'Select', Common\Select::class],
            ['Mysql', QueryFactory::COMMON, 'Insert', Common\Insert::class],
            ['Mysql', QueryFactory::COMMON, 'Update', Common\Update::class],
            ['Mysql', QueryFactory::COMMON, 'Delete', Common\Delete::class],
            ['Pgsql', QueryFactory::COMMON, 'Select', Common\Select::class],
            ['Pgsql', QueryFactory::COMMON, 'Insert', Common\Insert::class],
            ['Pgsql', QueryFactory::COMMON, 'Update', Common\Update::class],
            ['Pgsql', QueryFactory::COMMON, 'Delete', Common\Delete::class],
            ['Sqlite', QueryFactory::COMMON, 'Select', Common\Select::class],
            ['Sqlite', QueryFactory::COMMON, 'Insert', Common\Insert::class],
            ['Sqlite', QueryFactory::COMMON, 'Update', Common\Update::class],
            ['Sqlite', QueryFactory::COMMON, 'Delete', Common\Delete::class],
            ['Sqlsrv', QueryFactory::COMMON, 'Select', Common\Select::class],
            ['Sqlsrv', QueryFactory::COMMON, 'Insert', Common\Insert::class],
            ['Sqlsrv', QueryFactory::COMMON, 'Update', Common\Update::class],
            ['Sqlsrv', QueryFactory::COMMON, 'Delete', Common\Delete::class],
        ];
    }
}
