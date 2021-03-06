<?php

namespace OroB2B\Bundle\PricingBundle\Controller;

use Doctrine\ORM\EntityManager;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use OroB2B\Bundle\PricingBundle\Entity\BasePriceList;
use OroB2B\Bundle\PricingBundle\Entity\Repository\ProductPriceRepository;
use OroB2B\Bundle\ProductBundle\Entity\Product;
use OroB2B\Bundle\ProductBundle\Formatter\ProductUnitLabelFormatter;

abstract class AbstractAjaxProductPriceController extends Controller
{
    /**
     * Get products prices by price list and product ids
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getProductPricesByAccount(Request $request)
    {
        $priceListId = $this->get('orob2b_pricing.model.price_list_request_handler')
            ->getPriceListByAccount()
            ->getId();

        return new JsonResponse(
            $this->get('orob2b_pricing.provider.combined_product_price')
                ->getPriceByPriceListIdAndProductIds(
                    $priceListId,
                    $request->get('product_ids', []),
                    $request->get('currency')
                )
        );
    }

    /**
     * @param string $class
     * @param mixed $id
     * @return object
     */
    protected function getEntityReference($class, $id)
    {
        return $this->getManagerForClass($class)->getReference($class, $id);
    }

    /**
     * @param string $class
     * @return EntityManager
     */
    protected function getManagerForClass($class)
    {
        return $this->get('oro_entity.doctrine_helper')->getEntityManagerForClass($class);
    }

    /**
     * @return ProductUnitLabelFormatter
     */
    protected function getProductUnitFormatter()
    {
        return $this->container->get('orob2b_product.formatter.product_unit_label');
    }

    /**
     * Get product units that for which prices in given currency are exists.
     *
     * @param BasePriceList $priceList
     * @param Request $request
     * @param string $productPriceClass
     * @return JsonResponse
     */
    protected function getProductUnitsByCurrency(BasePriceList $priceList, Request $request, $productPriceClass)
    {
        $productClass = $this->getParameter('orob2b_product.entity.product.class');

        /** @var Product $product */
        $product = $this->getEntityReference($productClass, $request->get('id'));

        /** @var ProductPriceRepository $repository */
        $repository = $this->getManagerForClass($productPriceClass)->getRepository($productPriceClass);
        $units = $repository->getProductUnitsByPriceList($priceList, $product, $request->get('currency'));

        return new JsonResponse(['units' => $this->getProductUnitFormatter()->formatChoices($units)]);
    }
}
