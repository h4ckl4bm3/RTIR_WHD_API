<?php
/**
 * PSAIntegrator v1.0.0
 * Copyright (C) 2016 Switzer Business Solutions, LLC and Stephen Switzer

    This file is part of PSAIntegrator.

    PSAIntegrator is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    PSAIntegrator is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with PSAIntegrator.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Requires curl
 */
require 'RestServer.php';

spl_autoload_register(); // don't load our classes unless we use them

$mode = 'production'; // 'debug' or 'production'
$server = new RestServer($mode);
//$server->refreshCache(); // uncomment momentarily to clear the cache if classes change in production mode

require 'PSAIntegrator.php';
$server->addClass('PSAIntegrator', '/helpdesk/WebObjects/Helpdesk.woa');
$server->handle();

?>