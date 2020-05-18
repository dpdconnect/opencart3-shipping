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


class ListAction
{
    /**
     *
     * @var \Language  
     */
    protected $language;
    /**
     *
     * @var UrlGenerator  
     */
    protected $urlGenerator;
    /**
     *
     * @var string 
     */
    protected $route;

    /**
     * ListAction constructor.
     *
     * @param $language
     * @param $urlGenerator
     */
    public function __construct($language, $urlGenerator)
    {
        $this->language = $language;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     *
     * @param  int          $total_count
     * @param  int          $page
     * @param  int          $limit
     * @param  string|array $args        for the pagination url
     * @return string Rendered Pagination
     */
    protected function getPagination($total_count, $page, $limit, $args)
    {
        $pagination = new \Pagination();
        $pagination->total = $total_count;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $args['page'] = '{page}';
        $pagination->url = $this->urlGenerator->link($this->route, $args, true);;

        return $pagination->render();
    }

    /**
     *
     * @param  int $total_count
     * @param  int $start
     * @param  int $limit
     * @return string describing the pagination state
     */
    protected function getPaginationText($total_count, $start, $limit)
    {
        return sprintf(
            $this->language->get('text_pagination'),
            $total_count ? $start + 1 : 0,
            ($start > ($total_count - $limit)) ? $total_count : ($start + $limit),
            $total_count,
            ceil($total_count / $limit)
        );
    }

}
