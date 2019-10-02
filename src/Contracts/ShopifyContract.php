<?php

namespace BNMetrics\Shopify\Contracts;

interface ShopifyContract
{
    /**
     * @param $shopURL
     * @param array $scope
     * @param string $apiVersion
     *
     * @return mixed
     */
    public function make($shopURL, array $scope, $apiVersion);

    /**
     * Redirect the user of the application to the provider's authentication screen.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirect();


    /**
     * @return $this with validated user info
     *
     */
    public function auth();
}