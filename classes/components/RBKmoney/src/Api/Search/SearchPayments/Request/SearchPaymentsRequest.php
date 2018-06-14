<?php

namespace src\Api\Search\SearchPayments\Request;

use DateTime;
use src\Api\Exceptions\WrongDataException;
use src\Api\Interfaces\GetRequestInterface;
use src\Api\RBKmoneyDataObject;
use src\Api\Search\PaymentMethod;
use src\Api\Status as PaymentStatus;
use src\Api\Tokens\CreatePaymentResource\Request\TerminalProvider;

class SearchPaymentsRequest extends RBKmoneyDataObject implements GetRequestInterface
{

    const PATH = '/analytics/shops/{shopID}/payments';

    /**
     * Идентификатор магазина
     *
     * @var string
     */
    protected $shopId;

    /**
     * Начало временного отрезка
     *
     * @var DateTime
     */
    protected $fromTime;

    /**
     * Конец временного отрезка
     *
     * @var DateTime
     */
    protected $toTime;

    /**
     * Лимит выборки
     *
     * @var int
     */
    protected $limit;

    /**
     * Смещение выборки
     *
     * @var int | null
     */
    protected $offset;

    /**
     * Статус платежа для поиска
     *
     * @var PaymentStatus | null
     */
    protected $paymentStatus;

    /**
     * Метод оплаты
     *
     * @var PaymentMethod | null
     */
    protected $paymentMethod;

    /**
     * Провайдер платежного терминала
     *
     * @var TerminalProvider | null
     */
    protected $paymentTerminalProvider;

    /**
     * Идентификатор инвойса
     *
     * @var string | null
     */
    protected $invoiceID;

    /**
     * Идентификатор платежа
     *
     * @var string | null
     */
    protected $paymentID;

    /**
     * Email, указанный при оплате
     *
     * @var string | null
     */
    protected $payerEmail;

    /**
     * IP-адрес плательщика
     *
     * @var string | null
     */
    protected $payerIP;

    /**
     * Уникальный отпечаток user agent'а плательщика
     *
     * @var string | null
     */
    protected $payerFingerprint;

    /**
     * Идентификатор плательщика
     *
     * @var string | null
     */
    protected $customerID;

    /**
     * Маскированый номер карты
     *
     * @var string | null
     */
    protected $cardNumberMask;

    /**
     * Сумма платежа
     *
     * @var int | null
     */
    protected $paymentAmount;

    /**
     * @param string   $shopId
     * @param DateTime $fromTime
     * @param DateTime $toTime
     * @param int      $limit
     */
    public function __construct($shopId, DateTime $fromTime, DateTime $toTime, $limit)
    {
        $this->shopId = $shopId;
        $this->fromTime = $fromTime->format(DATE_ATOM);
        $this->toTime = $toTime->format(DATE_ATOM);
        $this->limit = $limit;
    }

    /**
     * @param int $offset
     *
     * @return SearchPaymentsRequest
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * @param PaymentStatus $status
     *
     * @return SearchPaymentsRequest
     */
    public function setPaymentStatus(PaymentStatus $status)
    {
        $this->paymentStatus = $status;

        return $this;
    }

    /**
     * @param PaymentMethod $method
     *
     * @return SearchPaymentsRequest
     */
    public function setPaymentMethod(PaymentMethod $method)
    {
        $this->paymentMethod = $method;

        return $this;
    }

    /**
     * @param TerminalProvider $provider
     *
     * @return SearchPaymentsRequest
     */
    public function setPaymentTerminalProvider(TerminalProvider $provider)
    {
        $this->paymentTerminalProvider = $provider;

        return $this;
    }

    /**
     * @param string $invoiceId
     *
     * @return SearchPaymentsRequest
     */
    public function setInvoiceId($invoiceId)
    {
        $this->invoiceID = $invoiceId;

        return $this;
    }

    /**
     * @param string $paymentId
     *
     * @return SearchPaymentsRequest
     */
    public function setPaymentId($paymentId)
    {
        $this->paymentID = $paymentId;

        return $this;
    }

    /**
     * @param string $payerEmail
     *
     * @return SearchPaymentsRequest
     */
    public function setPayerEmail($payerEmail)
    {
        $this->payerEmail = $payerEmail;

        return $this;
    }

    /**
     * @param string $payerIP
     *
     * @return SearchPaymentsRequest
     */
    public function setPayerIP($payerIP)
    {
        $this->payerIP = $payerIP;

        return $this;
    }

    /**
     * @param string $payerFingerprint
     *
     * @return SearchPaymentsRequest
     */
    public function setPayerFingerprint($payerFingerprint)
    {
        $this->payerFingerprint = $payerFingerprint;

        return $this;
    }

    /**
     * @param string $customerId
     *
     * @return SearchPaymentsRequest
     */
    public function setCustomerId($customerId)
    {
        $this->customerID = $customerId;

        return $this;
    }

    /**
     * @param string $cardNumberMask
     *
     * @return SearchPaymentsRequest
     *
     * @throws WrongDataException
     */
    public function setCardNumberMask($cardNumberMask)
    {
        if (!preg_match('/^\d{2,4}$/', $cardNumberMask)) {
            throw new WrongDataException(getLabel('RBK_MONEY_WRONG_VALUE') . ' `cardNumberMask`', RBK_MONEY_HTTP_CODE_BAD_REQUEST);
        }

        $this->cardNumberMask = $cardNumberMask;

        return $this;
    }

    /**
     * @param int $paymentAmount
     *
     * @return SearchPaymentsRequest
     */
    public function setPaymentAmount($paymentAmount)
    {
        $this->paymentAmount = $paymentAmount;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        $properties = [];

        foreach ($this as $property => $value) {
            if ('shopId' === $property) {
                continue;
            }
            if (!empty($value)) {
                if (is_object($value) && !($value instanceof DateTime)) {
                    $properties[$property] = $value->getValue();
                } else {
                    $properties[$property] = $value;
                }
            }
        }

        $url = str_replace('{shopID}', $this->shopId, self::PATH);

        return $url . '?' . http_build_query($properties, '', '&');
    }

}
