<?php

namespace OroB2B\Bundle\ProductBundle\ImportExport\TemplateFixture;

use Oro\Bundle\ImportExportBundle\TemplateFixture\AbstractTemplateRepository;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateFixtureInterface;
use Oro\Component\Testing\Unit\Entity\Stub\StubEnumValue;

use OroB2B\Bundle\FallbackBundle\Entity\LocalizedFallbackValue;
use OroB2B\Bundle\ProductBundle\Entity\Product;
use OroB2B\Bundle\ProductBundle\Entity\ProductUnit;
use OroB2B\Bundle\ProductBundle\Entity\ProductUnitPrecision;
use OroB2B\Bundle\WebsiteBundle\Entity\Locale;

class ProductFixture extends AbstractTemplateRepository implements TemplateFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEntityClass()
    {
        return 'OroB2B\Bundle\ProductBundle\Entity\Product';
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->getEntityData('Example Product');
    }

    /**
     * {@inheritdoc}
     */
    protected function createEntity($key)
    {
        return new Product();
    }

    /**
     * @param string  $key
     * @param Product $entity
     */
    public function fillEntityData($key, $entity)
    {
        $inventoryStatus = new StubEnumValue(Product::INVENTORY_STATUS_IN_STOCK, 'in stock');

        $locale = new Locale();
        $locale->setCode('en');

        $name = new LocalizedFallbackValue();
        $name->setString('Product Name');

        $localizedName = new LocalizedFallbackValue();
        $localizedName->setLocale($locale)
            ->setString('US Product Name')
            ->setFallback('system');

        $description = new LocalizedFallbackValue();
        $description->setText('Product Description');

        $localizedDescription = new LocalizedFallbackValue();
        $localizedDescription->setLocale($locale)
            ->setText('US Product Description')
            ->setFallback('system');

        $shortDescription = new LocalizedFallbackValue();
        $shortDescription->setText('Product Short Description');

        $localizedShortDescription = new LocalizedFallbackValue();
        $localizedShortDescription->setLocale($locale)
            ->setText('US Product Short Description')
            ->setFallback('system');

        $productUnit = (new ProductUnit())
            ->setCode('kg')
            ->setDefaultPrecision(3);

        $productUnitPrecision = (new ProductUnitPrecision())
            ->setUnit($productUnit)
            ->setPrecision($productUnit->getDefaultPrecision());

        $entity->setSku('sku_001')
            ->setStatus('enabled')
            ->setInventoryStatus($inventoryStatus)
            ->addName($name)
            ->addName($localizedName)
            ->addUnitPrecision($productUnitPrecision)
            ->addDescription($description)
            ->addDescription($localizedDescription)
            ->addShortDescription($shortDescription)
            ->addShortDescription($localizedShortDescription)
            ->setHasVariants(true);
    }
}
