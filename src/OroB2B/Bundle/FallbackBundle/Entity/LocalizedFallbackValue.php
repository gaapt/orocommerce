<?php

namespace OroB2B\Bundle\FallbackBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

use OroB2B\Bundle\FallbackBundle\Model\FallbackType;
use OroB2B\Bundle\WebsiteBundle\Entity\Locale;

/**
 * @ORM\Table(
 *      name="orob2b_fallback_locale_value",
 *      indexes={
 *          @ORM\Index(name="idx_orob2b_fallback_fallback", columns={"fallback"}),
 *          @ORM\Index(name="idx_orob2b_fallback_string", columns={"string"})
 *      }
 * )
 * @ORM\Entity
 * @Config(mode="hidden")
 */
class LocalizedFallbackValue
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
     * @var string|null
     *
     * @ORM\Column(name="fallback", type="string", length=64, nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $fallback;

    /**
     * @var string|null
     *
     * @ORM\Column(name="string", type="string", length=255, nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=false
     *          }
     *      }
     * )
     */
    protected $string;

    /**
     * @var string|null
     *
     * @ORM\Column(name="text", type="text", nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=false
     *          }
     *      }
     * )
     */
    protected $text;

    /**
     * @var Locale|null
     *
     * @ORM\ManyToOne(targetEntity="OroB2B\Bundle\WebsiteBundle\Entity\Locale")
     * @ORM\JoinColumn(name="locale_id", referencedColumnName="id", onDelete="CASCADE")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "identity"=true
     *          }
     *      }
     * )
     */
    protected $locale;

    /**
     * @return array
     */
    public static function getFallbacks()
    {
        return [FallbackType::SYSTEM, FallbackType::PARENT_LOCALE, FallbackType::NONE];
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $fallback
     * @return $this
     */
    public function setFallback($fallback)
    {
        $this->fallback = $fallback;

        return $this;
    }

    /**
     * @return string
     */
    public function getFallback()
    {
        return $this->fallback;
    }

    /**
     * @param string $string
     * @return $this
     */
    public function setString($string)
    {
        $this->string = $string;

        return $this;
    }

    /**
     * @return string
     */
    public function getString()
    {
        return $this->string;
    }

    /**
     * @param string $text
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return Locale|null
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param Locale|null $locale
     * @return $this
     */
    public function setLocale(Locale $locale = null)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if ($this->string) {
            return (string)$this->string;
        } elseif ($this->text) {
            return (string)$this->text;
        } else {
            return '';
        }
    }

    public function __clone()
    {
        $this->id = null;
    }
}
