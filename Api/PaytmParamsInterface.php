<?php
namespace Plab\PaytmRest\Api;

/**
 * @api
 */
interface PaytmParamsInterface
{
    /**
     * Get params
     *
     * @param int $orderId
     * @return array response
     */
    public function getParams($orderId);
}
