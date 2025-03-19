<?php

namespace Montapacking\MontaCheckout\Controller;

use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;
use Monta\CheckoutApiWrapper\Objects\Settings;
use Montapacking\MontaCheckout\Model\Config\Provider\Carrier as CarrierConfig;
use Monta\CheckoutApiWrapper\MontapackingShipping as MontpackingApi;

abstract class AbstractDeliveryOptions extends Action
{
    /** @var $carrierConfig CarrierConfig */
    private $carrierConfig;

    public $cart;
    protected $storeManager;
    protected $currency;


    /**
     * AbstractDeliveryOptions constructor.
     *
     * @param Context $context
     * @param CarrierConfig $carrierConfig
     * @param Cart $cart
     * @param StoreManagerInterface $storeManager
     * @param CurrencyInterface $currencyInterface
     */
    public function __construct(
        Context                      $context,
        CarrierConfig                $carrierConfig,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\CurrencyInterface $currencyInterface
    )
    {
        $this->carrierConfig = $carrierConfig;

        $this->cart = $cart;
        $this->storeManager = $storeManager;
        $this->currency = $currencyInterface;

        parent::__construct(
            $context
        );
    }

    /**
     * @return CarrierConfig
     */
    public function getCarrierConfig()
    {
        return $this->carrierConfig;
    }

    public function getCart()
    {
        return $this->cart;
    }

    /**
     * @param string $data
     * @param null $code
     *
     * @return mixed
     */
    public function jsonResponse($data = '', $code = null)
    {
        $response = $this->getResponse();

        if ($code !== null) {
            $response->setStatusCode($code);
        }

        return $response->representJson(
            json_encode($data)
        );
    }

    public function generateApi(RequestInterface $request, $language, $logger = null, $use_googlekey = false)
    {
        $street = $request->getParam('street', '');
        if ($street) {
            if (is_array($street)) {
                $street = trim(implode(' ', $street));
            } else {
                $street = trim($street);
            }
        }

        $postcode = $request->getParam('postcode') ? trim($request->getParam('postcode')) : "";
        $city = $request->getParam('city') ? trim($request->getParam('city')) : "";
        $country = $request->getParam('country') ? trim($request->getParam('country')) : "";

        $housenumber = $request->getParam('housenumber') ? trim($request->getParam('housenumber')) : "";
        $housenumberaddition = $request->getParam('housenumberaddition') ? trim($request->getParam('housenumberaddition')) : "";
        $state = '';

        $postcode = str_replace(" ", "", $postcode);

        // check is ZIPCODE valid for dutch customers
        if ($country == 'NL') {
            if (!preg_match("/^\W*[1-9]{1}[0-9]{3}\W*[a-zA-Z]{2}\W*$/", $postcode)) {
                $postcode = '';
            }
        }

        if ($country == 'BE') {
            if (!preg_match('~\A[1-9]\d{3}\z~', $postcode)) {
                $postcode = '';
            }
        }

        /**
         * Configs From Admin
         */
        $webshop = $this->getCarrierConfig()->getWebshop();
        $username = $this->getCarrierConfig()->getUserName();
        $password = $this->getCarrierConfig()->getPassword();
        $imageForStoreCollect = $this->getCarrierConfig()->getImageForStoreCollect();
        $nameForStoreCollect = $this->getCarrierConfig()->getCustomNameStoreCollect();

        $googleapikey = null;
        if ($use_googlekey) {
            $googleapikey = $this->getCarrierConfig()->getGoogleApiKey();
        }

        $leadingstockmontapacking = $this->getCarrierConfig()->getLeadingStockMontapacking();
        $disabledeliverydays = $this->getCarrierConfig()->getDisableDeliveryDays();
        $disabledPickupPoints = $this->getCarrierConfig()->getDisablePickupPoints();
        $defaultShippingCost = $this->getCarrierConfig()->getPrice();
        $maxPickupPoints = $this->getCarrierConfig()->getMaxpickuppoints() ?: 4;
        $showZeroCostsAsFree = $this->getCarrierConfig()->getShowZeroCostsAsFree() ?: false;

        $currentStore = $this->storeManager->getStore();
        $currentCurrencyCode = $currentStore->getCurrentCurrency()->getCode();
        $currencySymbol = $this->currency->getCurrency($currentCurrencyCode)->getSymbol();
        $currencyRate = $this->storeManager->getStore()->getCurrentCurrencyRate();

        /**
         * Retrieve Order Information
         */
        $cart = $this->getCart();

        /**
         * Todo: Fix to make dynamic from Magento settings later
         */
        $settings = new Settings(
            $webshop,
            $username,
            $password,
            !$disabledPickupPoints,
            $maxPickupPoints,
            $googleapikey,
            $defaultShippingCost,
            $language,
            $currencySymbol,
            false,
            $showZeroCostsAsFree
        );

        $settings->setExcludeShippingDiscount(false);

        $oApi = new MontpackingApi($settings, $language);
        $oApi->setAddress($street, $housenumber, $housenumberaddition, $postcode, $city, $state, $country);

        $quote = $cart->getQuote();

        $priceIncl = $quote->getSubtotal();
        $priceExcl = $quote->getSubtotal();

        if ($quote->getSubtotalInclTax() > 0) {
            $priceIncl = $quote->getSubtotalInclTax();
            $priceIncl = $quote->getSubtotalInclTax();
        } else if ($quote->getShippingAddress()->getSubtotalInclTax() > 0) {
            $priceIncl = $quote->getShippingAddress()->getSubtotalInclTax();
            $priceExcl = $quote->getShippingAddress()->getSubtotal();
        }

        $oApi->setOrder($priceIncl, $priceExcl); //phpcs:ignore

        $items = $quote->getAllVisibleItems();

        $bAllProductsAvailable = true;

        foreach ($items as $item) {

            if (!$leadingstockmontapacking) {
                $stockItem = $item->getProduct()->getExtensionAttributes()->getStockItem();

                if ($stockItem->getQty() <= 0 || $stockItem->getQty() < $item->getQty()) {

                    $bAllProductsAvailable = false;
                }
            }

            if($leadingstockmontapacking) {
                $oApi->addProduct(
                    (string)$item->getSku(),
                    (int)$item->getQty(),
                    0,
                    0,
                    0,
                    0,
                    (float)$item->getData('price_incl_tax') ?: 0);
            } else {
                $oApi->addProduct(
                    (string)$item->getSku(),
                    (int)$item->getQty(),
                    (int)$item->getData('length') ?: 0,
                    (int)$item->getData('width') ?: 0,
                    (int)$item->getData('height') ?: 0,
                    (int)$item->getData('weight') * 1000 ?: 0,
                    (float)$item->getData('price_incl_tax') ?: 0
                );
            }

        }

        if (false === $bAllProductsAvailable || $disabledeliverydays) {
            $oApi->setOnstock(false);
        }

        $frames = $oApi->getShippingOptions();

        if ($disabledeliverydays) {

            unset($frames['DeliveryOptions']);
            $frames['DeliveryOptions'] = [];
        }


        if ($frames['StoreLocation'] != null) {

            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $imageName = null;
            if (isset($imageForStoreCollect)) {
                $imageName = $imageForStoreCollect;
            }
            if (isset($nameForStoreCollect)) {
                $frames['StoreLocation']->displayName = $nameForStoreCollect;
            }
            $frames['StoreLocation']->imageName = $imageName;
            $frames['PickupOptions'][] = $frames['StoreLocation'];
        }

        foreach ($frames['PickupOptions'] as $item) {
            if ($item->code !== "AFH") {
                $item->imageName  = null;
            }

            $item->distanceMeters = round($item->distanceMeters / 1000, 2);
        }

        return $frames;
    }
}
