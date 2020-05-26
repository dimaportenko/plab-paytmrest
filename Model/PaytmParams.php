<?php

namespace Plab\PaytmRest\Model;

class PaytmParams extends \Magento\Payment\Model\Method\AbstractMethod implements \Plab\PaytmRest\Api\PaytmParamsInterface
{
    const CODE = 'paytm';
    protected $_code = self::CODE;

    protected $helper;
    protected $urlBuilder;
    protected $storeManager;
    protected $_orderFactory;

    CONST CHANNEL_ID = "WAP";

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \One97\Paytm\Helper\Data $helper
    )
    {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger
        );

        $this->_orderFactory = $orderFactory;
        $this->helper = $helper;
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
    }

    public function getOrder($orderId) {
        return $this->_orderFactory->create()->load($orderId);
    }

    public function buildPaytmParams($order) {
        $paytmOrderId = $magentoOrderId = $order->getRealOrderId();
        if($this->helper::APPEND_TIMESTAMP){
            $paytmOrderId = $magentoOrderId.'_'.time();
        }

        if($this->helper::SAVE_PAYTM_RESPONSE){
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            $objDate = $objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
            $date = $objDate->gmtDate();

            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();

            $tableName = $resource->getTableName('paytm_order_data');
            if(!$connection->isTableExists($tableName)){
                $sql = "CREATE TABLE ".$tableName."(id INT(11) PRIMARY KEY AUTO_INCREMENT, order_id TEXT NOT NULL, paytm_order_id TEXT NOT NULL, transaction_id TEXT, status TINYINT(1)  DEFAULT '0', paytm_response TEXT, date_added DATETIME, date_modified DATETIME )";
                $connection->query($sql);
            }
            $tableName = $resource->getTableName('paytm_order_data');
            $sql = "INSERT INTO ".$tableName."(order_id, paytm_order_id, date_added, date_modified) VALUES ('".$magentoOrderId."', '".$paytmOrderId."', '".$date."', '".$date."')";
            $connection->query($sql);
        }

        $callBackUrl = $this->urlBuilder->getUrl('paytm/Standard/Response', ['_secure' => true]);
        $params = array(
            'MID' => trim($this->getConfigData("MID")),
            'TXN_AMOUNT' => round($order->getGrandTotal(), 2),
            'CHANNEL_ID' => $this::CHANNEL_ID,
            'INDUSTRY_TYPE_ID' => trim($this->getConfigData("Industry_id")),
            'WEBSITE' => trim($this->getConfigData("Website")),
            'CUST_ID' => $order->getCustomerEmail(),
            'ORDER_ID' => $paytmOrderId,
            'EMAIL' => $order->getCustomerEmail(),
            'CALLBACK_URL' => trim($callBackUrl)
        );
//        if(isset($order->paytmPromoCode)){
//            $params['PROMO_CAMP_ID']=$order->paytmPromoCode;
//        }
        $checksum = $this->helper->generateSignature($params, $this->getConfigData("merchant_key"));
        $params['CHECKSUMHASH'] = $checksum;

        return $params;
//
//        $version = $this->getLastUpdate();
//        $params['X-REQUEST-ID']=$this->helper::X_REQUEST_ID.str_replace('|', '_', str_replace(' ', '-', $version));
//        $inputForm='';
//        foreach ($params as $key => $value) {
//            $inputForm.="<input type='hidden' name='".$key."' value='".$value."' />";
//        }
//        return $inputForm;
    }

    /**
     * @inheritDoc
     */
    public function getParams($orderId)
    {
//        $store = $this->storeManager->getStore();
//        $this->setData('store', $store->getId());

        $order = $this->getOrder($orderId);
        if ($order->getBillingAddress()) {
            $order->setState("pending_payment")->setStatus("pending_payment");
            $order->addStatusToHistory($order->getStatus(), "Customer was redirected to paytm.");
            $order->save();
            $params = $this->buildPaytmParams($order);
//            $data['actionURL'] = $this->_paytmModel->getRedirectUrl();

//            return $params;
            $response[] = [
                "data"  => $params,
                "status" => 'ok'
            ];

            return $response;
        }

        $response[] = [
            "data"  => [],
            "status" => 'fail'
        ];

        return $response;
    }
}
