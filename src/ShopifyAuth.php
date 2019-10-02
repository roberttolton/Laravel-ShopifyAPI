<?php

namespace BNMetrics\Shopify;

use BNMetrics\Shopify\Traits\ResponseOptions;
use Illuminate\Http\Request;
use Laravel\Socialite\Two\User;
use Laravel\Socialite\Two\AbstractProvider;

class ShopifyAuth extends AbstractProvider
{
    use ResponseOptions;

    protected $shopURL;

    protected $adminPath = "/admin/";

    protected $apiVersion = "";

    protected $requestPath;

    protected $responseHeaders;

    /**
     * Set the myshopify domain URL for the API request.
     * eg. example.myshopify.com
     *
     * @param Request $shopURL
     * @return $this
     */
    public function setShopURL($shopURL)
    {
        $this->shopURL = $shopURL;

        return $this;
    }

    /**
     * Set the API version.
     * eg. 2019-04
     *
     * @param Request $shopURL
     * @return $this
     */
    public function setApiVersion($apiVersion)
    {
        $this->apiVersion = $apiVersion;

        return $this;
    }

    /**
     * Get the API request path
     *
     * @return string
     */
    public function requestPath()
    {
        if($this->shopURL != null)
            $this->requestPath = 'https://' . $this->shopURL . $this->adminPath . ( $this->apiVersion != '' ? 'api/' . $this->apiVersion . '/' : '' );

        return $this->requestPath;
    }

    /**
     * Get the authentication URL for the provider.
     *
     * @param string $state
     * @return string
     */
    protected function getAuthUrl($state)
    {
        $url =  $this->requestPath()."oauth/authorize";

        return $this->buildAuthUrlFromBase( $url, $state );
    }

    /**
     * Get the token URL for the provider.
     *
     * @return string
     */
    protected function getTokenUrl()
    {
        // 'https://example.myshopify.com/admin/oauth/access_token'
        return 'https://' . $this->shopURL . $this->adminPath . "oauth/access_token";
    }

    /**
     * Get the raw user for the given access token.
     *
     * @param  string $token
     * @return array
     */
    protected function getUserByToken($token)
    {
        $userUrl = 'https://' . $this->shopURL . $this->adminPath . "shop.json";


        $response = $this->getHttpClient()->get( $userUrl,
                [
                    'headers' => $this->getResponseHeaders($token)
                ]);

        $user = json_decode($response->getBody(), true);

        $this->responseHeaders = $response->getHeaders();

        return $user['shop'];
    }

    /**
     * Map the raw user array to a Socialite User instance.
     *
     * @param  array $user
     * @return \Laravel\Socialite\Two\User
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['id'],
            'nickname' => $user['name'],
            'name' => $user['myshopify_domain'],
            'email' => $user['email'],
            'avatar' => null
        ]);
    }

    /**
     * Return current shopify api limit - 1 is lower
     *
     * @return int
     */
    public function checkCurrentApiLimit(): int
    {
        $limit = $this->responseHeaders['X-Shopify-Shop-Api-Call-Limit'] ??
                 $this->responseHeaders['HTTP_X_SHOPIFY_SHOP_API_CALL_LIMIT'] ?? ['1/40'];

        return (int)explode('/', $limit[0])[0];
    }


    /**
     * this method is for when you need to make an embedded shopify app
     *
     * @return string
     */
    public function fetchAuthUrl()
    {
        $state = $this->getState();

        $authUrl = $this->getAuthUrl($state);

        return $authUrl;
    }

}