<?php
namespace Plab\PaytmRest\Api;

/**
 * @api
 */
interface PaytmResponseInterface
{
    /**
     * Get params
     *
     * @param string $response
     * @return array response
     */
    public function setResponse($response);
}
