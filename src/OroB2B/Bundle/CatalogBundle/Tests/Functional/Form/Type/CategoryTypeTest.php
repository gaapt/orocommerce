<?php

namespace OroB2B\Bundle\CatalogBundle\Tests\Functional\Form\Type;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Doctrine\Common\Collections\Collection;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroB2B\Bundle\CatalogBundle\Entity\Category;
use OroB2B\Bundle\FallbackBundle\Model\FallbackType;
use OroB2B\Bundle\FallbackBundle\Entity\LocalizedFallbackValue;
use OroB2B\Bundle\ProductBundle\Entity\Product;
use OroB2B\Bundle\WebsiteBundle\Entity\Locale;
use OroB2B\Bundle\CatalogBundle\Form\Type\CategoryType;

/**
 * @dbIsolation
 */
class CategoryTypeTest extends WebTestCase
{
    const LARGE_IMAGE_NAME = 'large_image.png';
    const SMALL_IMAGE_NAME = 'small_image.png';

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var CsrfTokenManagerInterface
     */
    protected $tokenManager;

    protected function setUp()
    {
        $this->initClient();
        $this->loadFixtures(['OroB2B\Bundle\CatalogBundle\Tests\Functional\DataFixtures\LoadCategoryProductData']);

        $this->formFactory = $this->getContainer()->get('form.factory');
        $this->tokenManager = $this->getContainer()->get('security.csrf.token_manager');
    }

    public function testSubmit()
    {
        $doctrine = $localeRepository = $this->getContainer()->get('doctrine');
        $localeRepository = $doctrine->getRepository('OroB2BWebsiteBundle:Locale');
        $categoryRepository = $doctrine->getRepository('OroB2BCatalogBundle:Category');
        $productRepository = $doctrine->getRepository('OroB2BProductBundle:Product');

        /** @var Locale[] $locales */
        $locales = $localeRepository->findAll();
        /** @var Category $parentCategory */
        $parentCategory = $categoryRepository->findOneBy([]);
        /** @var Product[] $appendedProducts */
        $appendedProducts = $productRepository->findBy([], [], 2, 0);
        /** @var Product[] $removedProducts */
        $removedProducts = $productRepository->findBy([], [], 2, 2);

        $defaultTitle = 'Default Title';
        $defaultShortDescription = 'Default Short Description';
        $defaultLongDescription = 'Default Long Description';

        $fileLocator = $this->getContainer()->get('file_locator');

        $smallImageName = self::SMALL_IMAGE_NAME;
        $smallImageFile = $fileLocator->locate(
            '@OroB2BCatalogBundle/Tests/Functional/DataFixtures/files/' . $smallImageName
        );
        $largeImageName = self::LARGE_IMAGE_NAME;
        $largeImageFile = $fileLocator->locate(
            '@OroB2BCatalogBundle/Tests/Functional/DataFixtures/files/' . $largeImageName
        );

        $smallImage = new UploadedFile($smallImageFile, $smallImageName, null, null, null, true);
        $largeImage = new UploadedFile($largeImageFile, $largeImageName, null, null, null, true);

        // prepare input array
        $submitData = [
            'parentCategory' => $parentCategory->getId(),
            'titles' => [ 'values' => ['default' => $defaultTitle]],
            'shortDescriptions' => ['values' => ['default' => $defaultShortDescription]],
            'longDescriptions' => ['values' => [ 'default' => $defaultLongDescription]],
            'smallImage' => ['file' => $smallImage],
            'largeImage' => ['file' => $largeImage],
            'appendProducts' => implode(',', $this->getProductIds($appendedProducts)),
            'removeProducts' => implode(',', $this->getProductIds($removedProducts)),
            '_token' => $this->tokenManager->getToken('category')->getValue(),
        ];

        foreach ($locales as $locale) {
            $localeId = $locale->getId();
            $submitData['titles']['values']['locales'][$localeId] = [
                'use_fallback' => true,
                'fallback' => FallbackType::SYSTEM
            ];
            $submitData['shortDescriptions']['values']['locales'][$localeId] = [
                'use_fallback' => true,
                'fallback' => FallbackType::SYSTEM
            ];
            $submitData['longDescriptions']['values']['locales'][$localeId] = [
                'use_fallback' => true,
                'fallback' => FallbackType::SYSTEM
            ];
        }
        // submit form
        $form = $this->formFactory->create(CategoryType::NAME, new Category());
        $form->submit($submitData);
        $this->assertTrue($form->isValid());

        // assert category entity
        /** @var Category $category */
        $category = $form->getData();
        $this->assertInstanceOf('OroB2B\Bundle\CatalogBundle\Entity\Category', $category);
        $this->assertEquals($parentCategory->getId(), $category->getParentCategory()->getId());
        $this->assertEquals($defaultTitle, $category->getDefaultTitle()->getString());
        $this->assertEquals($defaultShortDescription, $category->getDefaultShortDescription()->getText());
        $this->assertEquals($defaultLongDescription, $category->getDefaultLongDescription()->getText());

        foreach ($locales as $locale) {
            $localizedTitle = $this->getValueByLocale($category->getTitles(), $locale);
            $this->assertNotEmpty($localizedTitle);
            $this->assertEmpty($localizedTitle->getString());
            $this->assertEquals(FallbackType::SYSTEM, $localizedTitle->getFallback());

            $localizedShortDescription = $this->getValueByLocale($category->getShortDescriptions(), $locale);
            $this->assertNotEmpty($localizedShortDescription);
            $this->assertEmpty($localizedShortDescription->getText());
            $this->assertEquals(FallbackType::SYSTEM, $localizedShortDescription->getFallback());

            $localizedLongDescription = $this->getValueByLocale($category->getLongDescriptions(), $locale);
            $this->assertNotEmpty($localizedLongDescription);
            $this->assertEmpty($localizedLongDescription->getText());
            $this->assertEquals(FallbackType::SYSTEM, $localizedLongDescription->getFallback());
        }
        // assert related products
        $this->assertEquals($appendedProducts, $form->get('appendProducts')->getData());
        $this->assertEquals($removedProducts, $form->get('removeProducts')->getData());
    }

    /**
     * @param Product[] $products
     * @return array
     */
    protected function getProductIds(array $products)
    {
        $ids = [];
        foreach ($products as $product) {
            $ids[] = $product->getId();
        }
        return $ids;
    }

    /**
     * @param Collection|LocalizedFallbackValue[] $values
     * @param Locale $locale
     * @return LocalizedFallbackValue|null
     */
    protected function getValueByLocale($values, Locale $locale)
    {
        $localeId = $locale->getId();
        foreach ($values as $value) {
            if ($value->getLocale()->getId() == $localeId) {
                return $value;
            }
        }

        return null;
    }
}
