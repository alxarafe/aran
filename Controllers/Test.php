<?php

namespace Alixar\Controllers;

use Alxarafe\Base\View;
use Alxarafe\Helpers\Skin;
use Alixar\Base\AlixarController;

class Test extends AlixarController
{

    function main()
    {
        Skin::setView(new View($this));
        Skin::setTemplate('default');
    }

}
