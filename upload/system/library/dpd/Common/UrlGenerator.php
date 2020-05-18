<?php
/**
 * This file is part of the OpenCart Shipping module of DPD Nederland B.V.
 *
 * Copyright (C) 2019 DPD Nederland B.V.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace DpdConnect\Common;


class UrlGenerator
{
    /**
     *
     * @var \Url  
     */
    private $openCartUrl;
    /**
     *
     * @var string  
     */
    private $userToken;

    /**
     * UrlGenerator constructor.
     *
     * @param \Url   $openCartUrl
     * @param string $userToken
     */
    public function __construct(\Url $openCartUrl, $userToken)
    {
        $this->openCartUrl = $openCartUrl;
        $this->userToken = $userToken;
    }

    /**
     * Generate an url
     *
     * @param  string       $route
     * @param  string|array $args
     * @param  bool         $secure
     * @return string url
     */
    public function link($route, $args = '', $secure = true)
    {
        return $this->openCartUrl->link($route, $this->addUserToken($args), $secure);
    }

    /**
     *
     * @param object $rewrite
     */
    public function addRewrite($rewrite)
    {
        $this->openCartUrl->addRewrite($rewrite);
    }

    /**
     * Add $this->userToken to the supplied query
     *
     * @param  array|string $args
     * @return array|string
     */
    private function addUserToken($args)
    {
        if (is_array($args)) {
            return array_merge(['user_token' => $this->userToken], $args);
        }
        return 'user_token='. urlencode($this->userToken). '&'. ltrim($args, '&');
    }
}
