<?php

namespace Phannp\Tests\Utilities;

use PHPUnit\Framework\TestCase;
use Phannp\Utilities\Countries;

class CountriesTest extends TestCase
{
    public function testValidCountries()
    {
        $this->assertTrue(Countries::isValid('US'));
        $this->assertTrue(Countries::isValid('us'));
        $this->assertTrue(Countries::isValid('GB'));
    }

    public function testInvalidCountry()
    {
        $this->assertFalse(Countries::isValid('XX'));
        $this->assertFalse(Countries::isValid('USA'));
    }
}
