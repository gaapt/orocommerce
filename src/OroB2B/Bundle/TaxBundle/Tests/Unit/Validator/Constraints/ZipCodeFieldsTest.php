<?php

namespace OroB2B\Bundle\TaxBundle\Tests\Unit\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

use OroB2B\Bundle\TaxBundle\Validator\Constraints\ZipCodeFields;
use OroB2B\Bundle\TaxBundle\Validator\Constraints\ZipCodeFieldsValidator;

class ZipCodeFieldsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ZipCodeFields
     */
    protected $constraint;

    protected function setUp()
    {
        $this->constraint = new ZipCodeFields();
    }

    protected function tearDown()
    {
        unset($this->constraint);
    }

    public function testGetTargets()
    {
        $this->assertEquals(Constraint::CLASS_CONSTRAINT, $this->constraint->getTargets());
    }

    public function testValidatedBy()
    {
        $this->assertEquals(ZipCodeFieldsValidator::ALIAS, $this->constraint->validatedBy());
    }
}
