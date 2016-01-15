<?php

namespace OroB2B\Bundle\TaxBundle\Matcher;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

use OroB2B\Bundle\TaxBundle\Entity\Repository\TaxRuleRepository;
use OroB2B\Bundle\TaxBundle\Entity\TaxRule;

abstract class AbstractMatcher
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var string
     */
    protected $taxRuleClass;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param string         $taxRuleClass
     */
    public function __construct(DoctrineHelper $doctrineHelper, $taxRuleClass)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->taxRuleClass = $taxRuleClass;
    }

    /**
     * @return TaxRuleRepository
     */
    protected function getTaxRuleRepository()
    {
        return $this->doctrineHelper->getEntityRepositoryForClass($this->taxRuleClass);
    }

    /**
     * Find TaxRules by address
     *
     * @param AbstractAddress $address
     * @return TaxRule[]
     */
    abstract public function match(AbstractAddress $address);

    /**
     * @param ...TaxRule[]
     * @return TaxRule[]
     */
    protected function mergeResult()
    {
        $arguments = func_get_args();

        $result = [];

        /** @var TaxRule[] $argument */
        foreach ($arguments as $argument) {
            foreach ($argument as $taxRule) {
                $result[$taxRule->getId()] = $taxRule;
            }
        }

        return array_values($result);
    }
}
