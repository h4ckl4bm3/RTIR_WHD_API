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
require 'PSAIntegratorBase.php';
class PSAIntegrator extends PSAIntegratorBase
{
	public function __construct() {
		//**************************************************************
		//**N-Central Connection
		//**************************************************************
		//Enter the APIkey that you type into N-Central. DO NOT USE this example!!
		$this->apikey='i8765redcvbnmko876trfvbnmki876trfvbnmkiu76tr';
		
		
		//**************************************************************
		//**Request Tracker Connection
		//**************************************************************
		//Enter the RT username and password for the account you want N-Central to use for creating tickets, etc.
		$this->rt_username = 'noc';
		$this->rt_password = 'ki87fytgawnmkpfs978yf5ihjkpw9dytf84t';
		
		//Enter the URL that this API uses to connect to RT's REST interface
		$this->rt_server = "http://support";
		
		//Enter the URL that is used in links from the RT tech perspective.
		//The generated links appear in N-Central so that you can click to see ticket information in RT.
		$this->rt_server_links='https://support.example.com';
		
		//TODO: For now, enter the Queue name to create tickets in. This should come from what's assigned by N-Central's "Request Type" soon.
		$this->rt_queue="Incident Reports";
		
		
		//**************************************************************
		//**Do not remove this line
		//**************************************************************
		parent :: __construct();
	}
	
	/* Uncomment the below if you wish to log every query that N-Central makes.
   	This helped us to determine which functions N-central needed to function properly.*/
/*
	private function logit($data) {
		$time = @date('[Y/M/d:H:i:s]');
		error_log("\n".$time." ".$data,3,'psa-query.log');
	}
*/
}
?>
