<?php
namespace Granam\GpWebPay;

use Granam\GpWebPay\Codes\OperationCodes;
use Granam\GpWebPay\Codes\RequestPayloadKeys;
use Granam\Strict\Object\StrictObject;

class CardPayRequest extends StrictObject
{
    /** @var Settings */
    private $settings;
    /** @var string|null */
    private $lang;
    /** @var array $parametersWithoutDigest */
    private $parametersWithoutDigest;
    /** @var string */
    private $digest;

    /**
     * @param CardPayRequestValues $requestValues
     * @param Settings $settings
     * @param DigestSigner $digestSigner
     * @throws \Granam\GpWebPay\Exceptions\InvalidArgumentException
     * @throws \Granam\GpWebPay\Exceptions\PrivateKeyUsageFailed
     * @throws \Granam\GpWebPay\Exceptions\CanNotSignDigest
     */
    public function __construct(CardPayRequestValues $requestValues, Settings $settings, DigestSigner $digestSigner)
    {
        $this->settings = $settings;

        // parameters HAVE TO be in this order, see GP_webpay_HTTP_EN.pdf / GP_webpay_HTTP.pdf
        $this->parametersWithoutDigest[RequestPayloadKeys::MERCHANTNUMBER] = $settings->getMerchantNumber();
        $this->parametersWithoutDigest[RequestPayloadKeys::OPERATION] = OperationCodes::CREATE_ORDER; // the only operation currently available
        $this->parametersWithoutDigest[RequestPayloadKeys::ORDERNUMBER] = $requestValues->getOrderNumber(); // HAS TO be unique
        $this->parametersWithoutDigest[RequestPayloadKeys::AMOUNT] = $requestValues->getAmount();
        $this->parametersWithoutDigest[RequestPayloadKeys::CURRENCY] = $requestValues->getCurrency();
        $this->parametersWithoutDigest[RequestPayloadKeys::DEPOSITFLAG] = $requestValues->getDepositFlag();
        if ($requestValues->getMerOrderNum()) {
            $this->parametersWithoutDigest[RequestPayloadKeys::MERORDERNUM] = $requestValues->getMerOrderNum();
        }
        $this->parametersWithoutDigest[RequestPayloadKeys::URL] = $settings->getResponseUrl();
        if ($requestValues->getDescription()) {
            $this->parametersWithoutDigest[RequestPayloadKeys::DESCRIPTION] = $requestValues->getDescription();
        }
        if ($requestValues->getMd()) {
            $this->parametersWithoutDigest[RequestPayloadKeys::MD] = $requestValues->getMd();
        }
        if ($requestValues->getPayMethod()) {
            $this->parametersWithoutDigest[RequestPayloadKeys::PAYMETHOD] = $requestValues->getPayMethod();
        }
        if ($requestValues->getDisabledPayMethod()) {
            $this->parametersWithoutDigest[RequestPayloadKeys::DISABLEPAYMETHOD] = $requestValues->getDisabledPayMethod();
        }
        if ($requestValues->getPayMethods()) {
            $this->parametersWithoutDigest[RequestPayloadKeys::PAYMETHODS] = $requestValues->getPayMethods();
        }
        if ($requestValues->getEmail()) {
            $this->parametersWithoutDigest[RequestPayloadKeys::EMAIL] = $requestValues->getEmail();
        }
        if ($requestValues->getReferenceNumber()) {
            $this->parametersWithoutDigest[RequestPayloadKeys::REFERENCENUMBER] = $requestValues->getReferenceNumber();
        }
        if ($requestValues->getAddInfo()) {
            $this->parametersWithoutDigest[RequestPayloadKeys::ADDINFO] = $requestValues->getAddInfo();
        }
        if ($requestValues->getFastPayId()) {
            $this->parametersWithoutDigest[RequestPayloadKeys::FASTPAYID] = $requestValues->getFastPayId();
        }
        // HAS TO be at the very end after all other parameters already populated
        $this->digest = $digestSigner->createSignedDigest($this->parametersWithoutDigest);
        if ($requestValues->getLang()) { // lang IS NOT part of digest
            $this->lang = $requestValues->getLang();
        }
    }

    /**
     * @return string
     */
    public function getRequestUrl()
    {
        $parameters = $this->parametersWithoutDigest;
        $parameters[RequestPayloadKeys::DIGEST] = $this->digest;
        if ($this->lang !== null) { // lang IS NOT part of digest
            $parameters[RequestPayloadKeys::LANG] = $this->lang;
        }

        return $this->settings->getResponseUrl() . '?' . http_build_query($parameters);
    }

}