<?php

namespace OroB2B\Bundle\ProductBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

use OroB2B\Bundle\ProductBundle\Entity\Product;
use OroB2B\Bundle\ProductBundle\Entity\Repository\ProductRepository;
use OroB2B\Bundle\ProductBundle\Form\Type\ProductRowType;
use OroB2B\Bundle\ProductBundle\Model\DataStorageAwareProcessor;
use OroB2B\Bundle\ProductBundle\Storage\ProductDataStorage;

abstract class AbstractPostQuickAddTypeExtension extends AbstractTypeExtension
{
    /** @var Request */
    protected $request;

    /** @var ProductDataStorage */
    protected $storage;

    /** @var string */
    protected $productClass;

    /** @var string */
    protected $dataClass;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /**
     * @param ProductDataStorage $storage
     * @param DoctrineHelper $doctrineHelper
     * @param string $productClass
     */
    public function __construct(ProductDataStorage $storage, DoctrineHelper $doctrineHelper, $productClass)
    {
        $this->storage = $storage;
        $this->doctrineHelper = $doctrineHelper;
        $this->productClass = $productClass;
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function setRequest($request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @param string $dataClass
     * @return $this
     */
    public function setDataClass($dataClass)
    {
        $this->dataClass = $dataClass;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->request->get(DataStorageAwareProcessor::QUICK_ADD_PARAM)) {
            $entity = isset($options['data']) ? $options['data'] : null;
            if ($entity instanceof $this->dataClass && !$this->doctrineHelper->getSingleEntityIdentifier($entity)) {
                $this->fillItems($entity);
            }
        }
    }

    /**
     * @param object $entity
     */
    protected function fillItems($entity)
    {
        $data = $this->storage->get();
        $this->storage->remove();

        if (!$data) {
            return;
        }

        $repository = $this->getProductRepository();
        foreach ($data as $dataRow) {
            if (!array_key_exists(ProductRowType::PRODUCT_SKU_FIELD_NAME, $dataRow) ||
                !array_key_exists(ProductRowType::PRODUCT_QUANTITY_FIELD_NAME, $dataRow)
            ) {
                continue;
            }

            $product = $repository->findOneBySku($dataRow[ProductRowType::PRODUCT_SKU_FIELD_NAME]);
            if (!$product) {
                continue;
            }

            $this->addProductToEntity($product, $entity, (float)$dataRow[ProductRowType::PRODUCT_QUANTITY_FIELD_NAME]);
        }
    }

    /**
     * @param Product $product
     * @param object $entity
     * @param float $quantity
     */
    abstract protected function addProductToEntity(Product $product, $entity, $quantity);

    /**
     * @return ProductRepository
     */
    protected function getProductRepository()
    {
        return $this->doctrineHelper->getEntityRepository($this->productClass);
    }
}