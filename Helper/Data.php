<?php

namespace Dragonfly\NovaposhtaBasic\Helper;

use Dragonfly\NovaposhtaBasic\Api\Data\ApiCityInterface;
use Dragonfly\NovaposhtaBasic\Api\Data\ApiDepotsInterface;
use Dragonfly\NovaposhtaBasic\Model\Data\ApiRegionData;
use Dragonfly\NovaposhtaBasic\Service\GetCity;
use Dragonfly\NovaposhtaBasic\Service\GetDepot;
use Dragonfly\ShippingPoint\Model\ShippingPointModel;
use Dragonfly\ShippingPoint\Service\GetShippingPoint;
use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Sales\Api\Data\OrderInterface;

/**
 *
 */
class Data extends AbstractHelper
{
    public const NOVAPOSHTA_BRANCH_METHOD_CODE = 'novaposhtabranch_novaposhtabranch';
    public const NOVAPOSHTA_POSHTOMAT_METHOD_CODE = 'novaposhtaposhtomat_novaposhtaposhtomat';

    /**
     * @var GetShippingPoint
     */
    private GetShippingPoint $getShippingPoint;

    /**
     * @var GetDepot
     */
    private GetDepot $getDepot;

    /**
     * @var GetCity
     */
    private GetCity $getCity;

    /**
     * @var ApiRegionData
     */
    private ApiRegionData $regionData;

    /**
     * @param Context $context
     * @param GetShippingPoint $getShippingPoint
     * @param GetDepot $getDepot
     * @param GetCity $getCity
     * @param ApiRegionData $regionData
     */
    public function __construct(
        Context          $context,
        GetShippingPoint $getShippingPoint,
        GetDepot         $getDepot,
        GetCity          $getCity,
        ApiRegionData    $regionData
    )
    {
        parent::__construct($context);
        $this->getShippingPoint = $getShippingPoint;
        $this->getDepot = $getDepot;
        $this->getCity = $getCity;
        $this->regionData = $regionData;
    }

    /**
     * @param OrderInterface $order
     * @return ShippingPointModel|null
     * @throws Exception
     */
    public function getShippingPointDetails(OrderInterface $order): ?ShippingPointModel
    {
        if ($this->isNovaposhtaBranchMethod($order) === false) {
            return null;
        }

        $shippingPoint = $this->getShippingPoint->execute($order->getEntityId());

        if (!$shippingPoint) {
            return null;
        }

        $this->getBranchDetails($shippingPoint->getShippingPointCode(), $shippingPoint);

        return $shippingPoint;
    }

    /**
     * @param OrderInterface $order
     * @return bool
     */
    public function isNovaposhtaBranchMethod(OrderInterface $order): bool
    {
        $methodCode = $order->getShippingMethod();
        if ($methodCode === self::NOVAPOSHTA_BRANCH_METHOD_CODE
            || $methodCode === self::NOVAPOSHTA_POSHTOMAT_METHOD_CODE) {
            return true;
        }

        return false;
    }

    /**
     * @throws Exception
     */
    private function getBranchDetails(string $ref, ShippingPointModel &$shippingPoint): void
    {
        /* @var ApiDepotsInterface $depot */
        $depot = $this->getDepot->execute($ref);

        if (!$depot) {
            return;
        }

        foreach ($depot->getData() as $key => $value) {
            $shippingPoint->setData($key, $value);
        }

        $cityRef = $depot->getCityRef();

        /* @var ApiCityInterface $city */
        $city = $this->getCity->execute($cityRef);

        if (!$city) {
            return;
        }

        $regionRef = $city->getArea();
        $regionName = $this->getRegionName($regionRef);

        $shippingPoint->setRegion($regionName);
    }

    /**
     * @param $ref
     * @return string
     */
    private function getRegionName($ref): string
    {
        $list = $this->regionData->getRegionList('uk_UA');

        foreach ($list as $value) {
            if ($value['id'] === $ref) {
                return $value['name'];
            }
        }

        return $ref;
    }
}
