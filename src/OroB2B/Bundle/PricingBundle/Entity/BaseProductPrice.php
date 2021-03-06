<?php

namespace OroB2B\Bundle\PricingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

use OroB2B\Bundle\ProductBundle\Entity\Product;
use OroB2B\Bundle\ProductBundle\Entity\ProductUnit;
use OroB2B\Bundle\ProductBundle\Model\ProductHolderInterface;
use OroB2B\Bundle\ProductBundle\Model\ProductUnitHolderInterface;

/**
 * @ORM\MappedSuperclass()
 * @ORM\HasLifecycleCallbacks()
 */
class BaseProductPrice implements ProductUnitHolderInterface, ProductHolderInterface
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $id;

    /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="OroB2B\Bundle\ProductBundle\Entity\Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "order"=10,
     *              "identity"=true
     *          }
     *      }
     * )
     **/
    protected $product;

    /**
     * @var string
     *
     * @ORM\Column(name="product_sku", type="string", length=255)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $productSku;

    /**
     * @var BasePriceList
     **/
    protected $priceList;

    /**
     * @var float
     *
     * @ORM\Column(name="quantity", type="float")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "order"=20,
     *              "identity"=true
     *          }
     *      }
     * )
     */
    protected $quantity;

    /**
     * @var ProductUnit
     *
     * @ORM\ManyToOne(targetEntity="OroB2B\Bundle\ProductBundle\Entity\ProductUnit")
     * @ORM\JoinColumn(name="unit_code", referencedColumnName="code", nullable=false, onDelete="CASCADE")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "order"=30,
     *              "identity"=true
     *          }
     *      }
     * )
     **/
    protected $unit;

    /**
     * @var float
     *
     * @ORM\Column(name="value", type="money")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "order"=40,
     *              "header"="Price"
     *          }
     *      }
     * )
     */
    protected $value;

    /**
     * @var string
     *
     * @ORM\Column(name="currency", type="string", length=3)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "order"=50,
     *              "identity"=true
     *          }
     *      }
     * )
     */
    protected $currency;

    /**
     * Changes to this value object wont affect entity change set
     * To change persisted price value you should create and set new Price
     *
     * @var Price
     */
    protected $price;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param Product $product
     * @return ProductPrice
     */
    public function setProduct(Product $product)
    {
        $this->product    = $product;
        $this->productSku = $product->getSku();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductSku()
    {
        return $this->productSku;
    }

    /**
     * @param BasePriceList $priceList
     * @return ProductPrice
     */
    public function setPriceList(BasePriceList $priceList)
    {
        $this->priceList = $priceList;

        return $this;
    }

    /**
     * @return BasePriceList
     */
    public function getPriceList()
    {
        return $this->priceList;
    }

    /**
     * @param float $quantity
     * @return ProductPrice
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return float
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @return ProductUnit
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param ProductUnit $unit
     * @return ProductPrice
     */
    public function setUnit(ProductUnit $unit)
    {
        $this->unit = $unit;

        return $this;
    }

    /**
     * @param Price $price
     * @return ProductPrice
     */
    public function setPrice(Price $price)
    {
        $this->price = $price;
        $this->updatePrice();

        return $this;
    }

    /**
     * @return Price|null
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @ORM\PostLoad
     */
    public function loadPrice()
    {
        $this->price = Price::create($this->value, $this->currency);
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatePrice()
    {
        if ($this->price) {
            $this->value = $this->price->getValue();
            $this->currency = $this->price->getCurrency();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityIdentifier()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductHolder()
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductUnit()
    {
        return $this->getUnit();
    }

    /**
     * {@inheritdoc}
     */
    public function getProductUnitCode()
    {
        return $this->getUnit()->getCode();
    }
}
