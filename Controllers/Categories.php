<?php
namespace Alixar\Controllers;

use Alixar\Base\AlixarController;
use Alixar\Models\Person;

class Categories extends AuthPageExtendedController
{
    /**
     * People constructor.
     */
    public function __construct()
    {
        parent::__construct(new Person());
    }

    /**
     * Returns the page details.
     *
     * @return array
     */
    public function pageDetails(): array
    {
        $details = [
            'title' => 'controller-people-title',
            'icon' => '<i class="fas fa-user"></i>',
            'description' => 'controller-people-description',
            'menu' => 'default',
        ];
        return $details;
    }
}
