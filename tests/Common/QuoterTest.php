<?php
namespace Aura\SqlQuery\Common;

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

class QuoterTest extends TestCase
{
    protected $quoter = null;

    public function set_up()
    {
        $this->quoter = new Quoter();
    }

    public function testQuoteName()
    {
        // table AS alias
        $actual = $this->quoter->quoteName('table AS alias');
        $this->assertSame('"table" AS "alias"', $actual);

        // table.col AS alias
        $actual = $this->quoter->quoteName('table.col AS alias');
        $this->assertSame('"table"."col" AS "alias"', $actual);

        // table alias
        $actual = $this->quoter->quoteName('table alias');
        $this->assertSame('"table" "alias"', $actual);

        // table.col alias
        $actual = $this->quoter->quoteName('table.col alias');
        $this->assertSame('"table"."col" "alias"', $actual);

        // plain old identifier
        $actual = $this->quoter->quoteName('table');
        $this->assertSame('"table"', $actual);

        // star
        $actual = $this->quoter->quoteName('*');
        $this->assertSame('*', $actual);

        // star dot star
        $actual = $this->quoter->quoteName('*.*');
        $this->assertSame('*.*', $actual);

        // table dot star
        $actual = $this->quoter->quoteName('table.*');
        $this->assertSame('"table".*', $actual);
    }

    public function testQuoteNamesIn()
    {
        $sql = "*, *.*, f.bar, foo.bar, CONCAT('foo.bar', \"baz.dib\") AS zim";
        $actual = $this->quoter->quoteNamesIn($sql);
        $expect = "*, *.*, \"f\".\"bar\", \"foo\".\"bar\", CONCAT('foo.bar', \"baz.dib\") AS \"zim\"";
        $this->assertSame($expect, $actual);
    }
}
