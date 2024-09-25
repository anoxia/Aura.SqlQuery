<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery;

/**
 * Creates query statement objects.
 *
 * @package Aura.SqlQuery
 */
class QueryFactory
{
    /**
     * Use the 'common' driver instead of a database-specific one.
     */
    public const COMMON = 'common';

    /**
     * What database are we building for?
     *
     * @param string
     */
    protected $db;

    /**
     * Build "common" query objects regardless of database type?
     *
     * @param bool
     */
    protected $common = false;

    /**
     * A map of `table.col` names to last-insert-id names.
     *
     * @var array
     */
    protected $last_insert_id_names = [];

    /**
     * A Quoter for identifiers.
     *
     * @param QuoterInterface
     */
    protected $quoter;

    /**
     * Constructor.
     *
     * @param string $db     the database type
     * @param string $common pass the constant self::COMMON to force common
     *                       query objects instead of db-specific ones
     */
    public function __construct(string $db, ?string $common = null)
    {
        $this->db = \ucfirst(\mb_strtolower($db));
        $this->common = (self::COMMON === $common);
    }

    /**
     * Sets the last-insert-id names to be used for Insert queries..
     *
     * @param array $last_insert_id_names A map of `table.col` names to
     *                                    last-insert-id names.
     */
    public function setLastInsertIdNames(array $last_insert_id_names): void
    {
        $this->last_insert_id_names = $last_insert_id_names;
    }

    /**
     * Returns a new SELECT object.
     */
    public function newSelect(): Common\SelectInterface
    {
        return $this->newInstance('Select');
    }

    /**
     * Returns a new INSERT object.
     */
    public function newInsert(): Common\InsertInterface
    {
        $insert = $this->newInstance('Insert');
        $insert->setLastInsertIdNames($this->last_insert_id_names);
        return $insert;
    }

    /**
     * Returns a new UPDATE object.
     */
    public function newUpdate(): Common\UpdateInterface
    {
        return $this->newInstance('Update');
    }

    /**
     * Returns a new DELETE object.
     */
    public function newDelete(): Common\DeleteInterface
    {
        return $this->newInstance('Delete');
    }

    /**
     * Returns a new query object.
     *
     * @param string $query the query object type
     */
    protected function newInstance(string $query): mixed
    {
        if ($this->common) {
            $queryClass = "Aura\SqlQuery\Common\\{$query}";
        } else {
            $queryClass = "Aura\SqlQuery\\{$this->db}\\{$query}";
        }

        /** @var class-string $queryClass */
        return new $queryClass(
            $this->getQuoter(),
            $this->newBuilder($query),
        );
    }

    /**
     * Returns a new Builder for the database driver.
     *
     * @param string $query the query type
     */
    protected function newBuilder(string $query): Common\AbstractBuilder
    {
        $builderClass = "Aura\SqlQuery\\{$this->db}\\{$query}Builder";
        if ($this->common || ! \class_exists($builderClass)) {
            $builderClass = "Aura\SqlQuery\Common\\{$query}Builder";
        }

        return new $builderClass;
    }

    /**
     * Returns the Quoter object for queries; creates one if needed.
     */
    protected function getQuoter(): Common\QuoterInterface
    {
        if (! $this->quoter) {
            $this->quoter = $this->newQuoter();
        }
        return $this->quoter;
    }

    /**
     * Returns a new Quoter for the database driver.
     */
    protected function newQuoter(): Common\QuoterInterface
    {
        $quoterClass = "Aura\SqlQuery\\{$this->db}\Quoter";
        if (! \class_exists($quoterClass)) {
            $quoterClass = Common\Quoter::class;
        }
        return new $quoterClass;
    }
}
