<?php

namespace OroB2B\Bundle\ShoppingListBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\CurrencyBundle\Entity\CurrencyAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\OrganizationBundle\Entity\Ownership\OrganizationAwareTrait;

use OroB2B\Bundle\AccountBundle\Entity\AccountOwnerAwareInterface;
use OroB2B\Bundle\AccountBundle\Entity\Ownership\FrontendAccountUserAwareTrait;
use OroB2B\Bundle\PricingBundle\SubtotalProcessor\Model\LineItemsNotPricedAwareInterface;
use OroB2B\Bundle\ShoppingListBundle\Model\ExtendShoppingList;
use OroB2B\Bundle\WebsiteBundle\Entity\Website;
use OroB2B\Bundle\WebsiteBundle\Entity\WebsiteAwareInterface;
use OroB2B\Component\Checkout\Entity\CheckoutSourceEntityInterface;

/**
 * @ORM\Table(
 *      name="orob2b_shopping_list",
 *      indexes={
 *          @ORM\Index(name="orob2b_shop_lst_created_at_idx", columns={"created_at"})
 *      }
 * )
 * @ORM\Entity(repositoryClass="OroB2B\Bundle\ShoppingListBundle\Entity\Repository\ShoppingListRepository")
 * @ORM\AssociationOverrides({
 *      @ORM\AssociationOverride(name="accountUser",
 *          joinColumns=@ORM\JoinColumn(name="account_user_id", referencedColumnName="id", onDelete="CASCADE")
 *      )
 * })
 * @Config(
 *      routeName="orob2b_shopping_list_index",
 *      routeView="orob2b_shopping_list_view",
 *      defaultValues={
 *          "entity"={
 *              "icon"="icon-shopping-cart"
 *          },
 *          "ownership"={
 *              "frontend_owner_type"="FRONTEND_USER",
 *              "frontend_owner_field_name"="accountUser",
 *              "frontend_owner_column_name"="account_user_id",
 *              "organization_field_name"="organization",
 *              "organization_column_name"="organization_id"
 *          },
 *          "dataaudit"={
 *              "auditable"=true
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"="commerce"
 *          }
 *      }
 * )
 * @ORM\HasLifecycleCallbacks()
 */
class ShoppingList extends ExtendShoppingList implements
    OrganizationAwareInterface,
    LineItemsNotPricedAwareInterface,
    CurrencyAwareInterface,
    AccountOwnerAwareInterface,
    WebsiteAwareInterface,
    CheckoutSourceEntityInterface
{
    use DatesAwareTrait;
    use OrganizationAwareTrait;
    use FrontendAccountUserAwareTrait;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string", length=255)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $label;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="text", nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $notes;

    /**
     * @var Website
     *
     * @ORM\ManyToOne(targetEntity="OroB2B\Bundle\WebsiteBundle\Entity\Website")
     * @ORM\JoinColumn(name="website_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $website;

    /**
     * @var ArrayCollection|LineItem[]
     *
     * @ORM\OneToMany(
     *      targetEntity="OroB2B\Bundle\ShoppingListBundle\Entity\LineItem",
     *      mappedBy="shoppingList",
     *      cascade={"ALL"},
     *      orphanRemoval=true
     * )
     **/
    protected $lineItems;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_current", type="boolean", options={"default"=false})
     */
    protected $current = false;

    /**
     * @var float
     *
     * @ORM\Column(name="subtotal", type="money", nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "entity"={
     *              "is_subtotal"=true
     *          }
     *      }
     * )
     */
    protected $subtotal;

    /**
     * @var float
     *
     * @ORM\Column(name="total", type="money", nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "entity"={
     *              "is_total"=true
     *          }
     *      }
     * )
     */
    protected $total;

    /**
     * @var string
     *
     * @ORM\Column(name="currency", type="string", length=3, nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "entity"={
     *              "is_total_currency"=true
     *          }
     *      }
     * )
     */
    protected $currency;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();

        $this->lineItems = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->label;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param string $notes
     *
     * @return $this
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     *
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @param LineItem $item
     *
     * @return $this
     */
    public function addLineItem(LineItem $item)
    {
        if (!$this->lineItems->contains($item)) {
            $item->setShoppingList($this);
            $this->lineItems->add($item);
        }

        return $this;
    }

    /**
     * @param LineItem $item
     *
     * @return $this
     */
    public function removeLineItem(LineItem $item)
    {
        if ($this->lineItems->contains($item)) {
            $this->lineItems->removeElement($item);
        }

        return $this;
    }

    /**
     * @return ArrayCollection|LineItem[]
     */
    public function getLineItems()
    {
        return $this->lineItems;
    }

    /**
     * @return bool
     */
    public function isCurrent()
    {
        return $this->current;
    }

    /**
     * @param bool $current
     *
     * @return $this
     */
    public function setCurrent($current)
    {
        $this->current = (bool)$current;

        return $this;
    }

    /**
     * Pre persist event handler
     *
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * Pre update event handler
     *
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * @param Website $website
     *
     * @return $this
     */
    public function setWebsite(Website $website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * @return Website
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set currency
     *
     * @param string $currency
     *
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Get currency
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set subtotal
     *
     * @param float $subtotal
     *
     * @return $this
     */
    public function setSubtotal($subtotal)
    {
        $this->subtotal = $subtotal;

        return $this;
    }

    /**
     * Get subtotal
     *
     * @return float
     */
    public function getSubtotal()
    {
        return $this->subtotal;
    }

    /**
     * Set total
     *
     * @param float $total
     *
     * @return $this
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Get total
     *
     * @return float
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->getId();
    }

    /**
     * @return $this
     */
    public function getSourceDocument()
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getSourceDocumentIdentifier()
    {
        return $this->label;
    }
}
