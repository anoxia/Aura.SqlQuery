<?php

declare(strict_types=1);

use Aura\SqlQuery\QueryFactory;

$select = (new QueryFactory('mysql'))->newSelect();

$select->where('id = :id', ['id' => 1])->orderBy(['id desc'])->limit(100)->getStatement();
