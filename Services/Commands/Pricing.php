<?php

/*
 * This file is part of the SOG/EnomBundle
 *
 * (c) Julian Ivanov <julian.petrov@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace SOG\EnomBundle\Services\Commands;

use SOG\EnomBundle\Services\HttpClient;

/**
 * Enom Account Retail Pricing operations
 *
 * @author Julian Ivanov <julian.petrov@gmail.com>
 * @link   http://www.enom.com/APICommandCatalog/index.htm
 */
class Pricing extends HttpClient
{
    /**
     * Get the retail pricing that this account charges for registrations, 
     * renewals, and transfers, by top-level domain.
     *
     * @return SimpleXMLElement Products Pricing Information
     */
    public function getDomainPricing()
    {
        $command = 'PE_GetDomainPricing';
        $data = $this->makeRequest($command, $this->payload);

        $products = Array();
        
        foreach ($data->pricestructure->children() as $product) {
            
            if ($product->getName() != 'product') {
                continue;
            }

            $products[] = (Array) $product;
        }

        $data = null;
        return $products;
    }
}