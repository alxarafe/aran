<?php
/* Copyright (C) 2019       Alxarafe                    <info@alxarafe.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
namespace Alixar\Base;

/**
 * This class contains the methods and attributes common to all Alixar view
 *
 * @author Alxarafe
 */
class AlixarView extends \Alxarafe\Base\View
{

    public $defaultlang;
    public $favicon;
    public $title;

    public function __construct()
    {
        parent::__construct();

        $this->defaultlang = 'ES';
        $this->favicon = DOL_BASE_URI . '/theme/eldy/img/favicon.ico';
        $this->title = 'Inicio - Alixar 0.0.0-alpha';
    }

    public function getTopMenu()
    {
        $ret[] = [
            'text' => '<i class="fa fa-cog fa-fw"></i> Config',
            'href' => '?call=EditConfig',
        ];
        $ret[] = [
            'text' => '<i class="fa fa-database fa-fw"></i> Database',
            'href' => 'index.html',
            'options' => [
                [
                    'text' => '<i class="fa fa-address-book fa-fw"></i> People',
                    'href' => '?call=People'
                ],
                [
                    'text' => '<i class="fa fa-automobile fa-fw"></i> Vehicles',
                    'href' => '?call=Vehicles',
                    'options' => [
                        'text' => '<i class="fa fa-address-book fa-fw"></i> People',
                        'href' => '?call=People'
                    ]
                ]
            ]
        ];

        return $ret;
    }

    public function getLeftMenu(): array
    {
        $ret[] = [
            'text' => '<i class="fa fa-cog fa-fw"></i> Config',
            'href' => '?call=EditConfig',
        ];
        $ret[] = [
            'text' => '<i class="fa fa-database fa-fw"></i> Database',
            'href' => 'index.html',
            'options' => [
                [
                    'text' => '<i class="fa fa-address-book fa-fw"></i> People',
                    'href' => '?call=People'
                ],
                [
                    'text' => '<i class="fa fa-automobile fa-fw"></i> Vehicles',
                    'href' => '?call=Vehicles',
                    'options' => [
                        'text' => '<i class="fa fa-address-book fa-fw"></i> People',
                        'href' => '?call=People'
                    ]
                ]
            ]
        ];

        return $ret;
    }
}
