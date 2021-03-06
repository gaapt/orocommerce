<?php

namespace OroB2B\Bundle\PricingBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;

use OroB2B\Bundle\AccountBundle\Entity\AccountGroup;
use OroB2B\Bundle\PricingBundle\Entity\PriceListToAccountGroup;

class LoadPriceListToAccountGroupDemoData extends LoadBasePriceListRelationDemoData
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $locator = $this->container->get('file_locator');
        $filePath = $locator
            ->locate('@OroB2BPricingBundle/Migrations/Data/Demo/ORM/data/price_lists_to_account_group.csv');

        if (is_array($filePath)) {
            $filePath = current($filePath);
        }

        $handler = fopen($filePath, 'r');
        $headers = fgetcsv($handler, 1000, ',');

        while (($data = fgetcsv($handler, 1000, ',')) !== false) {
            $row = array_combine($headers, array_values($data));
            /** @var EntityManager $manager */
            $account = $this->getAccountGroupByName($manager, $row['accountGroup']);
            $priceList = $this->getPriceListByName($manager, $row['priceList']);
            $website = $this->getWebsiteByName($manager, $row['website']);

            $priceListToAccountGroup = new PriceListToAccountGroup();
            $priceListToAccountGroup->setAccountGroup($account)
                ->setPriceList($priceList)
                ->setWebsite($website)
                ->setPriority($row['priority'])
                ->setMergeAllowed((boolean)$row['mergeAllowed']);

            $manager->persist($priceListToAccountGroup);
        }

        fclose($handler);

        $manager->flush();
    }

    /**
     * @param EntityManager $manager
     * @param string $name
     * @return AccountGroup
     */
    protected function getAccountGroupByName(EntityManager $manager, $name)
    {
        $website = $manager->getRepository('OroB2BAccountBundle:AccountGroup')->findOneBy(['name' => $name]);

        if (!$website) {
            throw new \LogicException(sprintf('There is no account group with name "%s" .', $name));
        }

        return $website;
    }
}
