<?php

namespace OroB2B\Bundle\TaxBundle\Tests\Unit\EventListener;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\Persistence\ObjectManager;

use OroB2B\Bundle\TaxBundle\Entity\TaxValue;
use OroB2B\Bundle\TaxBundle\EventListener\TaxValueListener;
use OroB2B\Bundle\TaxBundle\Manager\TaxValueManager;

class TaxValueListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var TaxValueManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $taxValueManager;

    /** @var TaxValueListener */
    protected $listener;

    protected function setUp()
    {
        $this->taxValueManager = $this->getMockBuilder('OroB2B\Bundle\TaxBundle\Manager\TaxValueManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new TaxValueListener($this->taxValueManager);
    }

    protected function tearDown()
    {
        unset($this->listener, $this->taxValueManager);
    }

    public function testPostRemove()
    {
        $this->taxValueManager->expects($this->once())
            ->method('clear');

        $taxValue = new TaxValue();

        /** @var ObjectManager $objectManager */
        $objectManager = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $event = new LifecycleEventArgs($taxValue, $objectManager);

        $this->listener->postRemove($taxValue, $event);
    }
}
