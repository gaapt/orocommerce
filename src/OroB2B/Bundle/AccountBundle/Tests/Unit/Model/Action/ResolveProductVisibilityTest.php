<?php

namespace OroB2B\Bundle\AccountBundle\Tests\Unit\Model\Action;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\WorkflowBundle\Model\ProcessData;

use OroB2B\Bundle\AccountBundle\Entity\Visibility\ProductVisibility;
use OroB2B\Bundle\AccountBundle\Entity\Visibility\VisibilityInterface;
use OroB2B\Bundle\AccountBundle\Model\Action\ResolveProductVisibility;
use OroB2B\Bundle\AccountBundle\Visibility\Cache\CacheBuilderInterface;
use OroB2B\Bundle\ProductBundle\Entity\Product;

class ResolveProductVisibilityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var CacheBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheBuilder;

    /**
     * @var ResolveProductVisibility
     */
    protected $action;

    protected function setUp()
    {
        $this->registry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $this->cacheBuilder = $this->getMock('OroB2B\Bundle\AccountBundle\Visibility\Cache\CacheBuilderInterface');
        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $this->action = new ResolveProductVisibility();
        $this->action->setRegistry($this->registry);
        $this->action->setCacheBuilder($this->cacheBuilder);
        $this->action->setDispatcher($eventDispatcher);
    }

    /**
     * @param bool $resetVisibility
     * @dataProvider executeDataProvider
     */
    public function testExecute($resetVisibility)
    {
        $originalVisibility = ProductVisibility::VISIBLE;
        $defaultVisibility = ProductVisibility::getDefault(new Product());
        $expectedVisibility = $resetVisibility ? $defaultVisibility : $originalVisibility;

        $entity = new ProductVisibility();
        $entity->setVisibility($originalVisibility);

        $options = [];
        if ($resetVisibility) {
            $options['reset_visibility'] = true;
        }

        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->expects($this->any())
            ->method('transactional')
            ->willReturnCallback(
                function ($callback) {
                    call_user_func($callback);
                }
            );

        $this->registry->expects($this->any())
            ->method('getManagerForClass')
            ->with('OroB2BAccountBundle:VisibilityResolved\ProductVisibilityResolved')
            ->willReturn($entityManager);

        $this->cacheBuilder->expects($this->once())
            ->method('resolveVisibilitySettings')
            ->with($entity)
            ->willReturnCallback(
                function (VisibilityInterface $visibilitySettings) use ($expectedVisibility) {
                    $this->assertEquals($expectedVisibility, $visibilitySettings->getVisibility());
                }
            );

        $this->action->initialize($options);
        $this->action->execute(new ProcessData(['data' => $entity]));
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            'default' => [false],
            'reset visibility' => [true],
        ];
    }
}
