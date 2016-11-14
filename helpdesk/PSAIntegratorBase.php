<?php
/*************************************************************************
 * PSAIntegrator v1.0.0
 * Copyright (C) 2016 Switzer Business Solutions, LLC and Stephen Switzer
 * http://www.sbsroc.com  /  (585) 298-9420
 *************************************************************************
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
 *************************************************************************
 * Requires curl
 *************************************************************************/
require_once 'RequestTracker.php';
class PSAIntegratorBase
{
	protected $rt_username;
	protected $rt_password;
	protected $apikey;
	protected $rt;
	protected $rtstatusobj;
	protected $rtstatusarr;
   
	public function __construct() {
		$this->rtstatusobj=json_decode('[
			{ "id": 1, "type": "StatusType", "statusTypeName": "New" },
			{ "id": 2, "type": "StatusType", "statusTypeName": "Open" },
			{ "id": 3, "type": "StatusType", "statusTypeName": "Stalled" },
			{ "id": 4, "type": "StatusType", "statusTypeName": "Rejected" },
			{ "id": 5, "type": "StatusType", "statusTypeName": "Deleted" },
			{ "id": 6, "type": "StatusType", "statusTypeName": "Resolved" }
		]');
	}

	public function init() {
		$this->rt = new RequestTracker($this->rt_server, $this->rt_username, $this->rt_password);
		foreach($this->rtstatusobj as $status) {
			$this->rtstatusarr[strtolower($status->statusTypeName)]=$status;
		}
	}
   
   
	/** ******************************************************
	* @url GET /ra/Preferences
	*/
	public function getPreferences()
	{
		header('Content-Type: application/json');
		print '{ "id": 1, "type": "Preference", "version": "PSA Integrator 1.0", "useRoomsBoolean": false, "roomPopup": false, "useDepartmentsBoolean": false, "defaultPriorityTypeId": 3, "defaultStatusTypeId": 1, "needsApprovalStatusTypeId": 7, "defaultNoteStatusTypeId": null, "billingEnabledBoolean": false, "ticketClientRequiredBoolean": true, "apiVersion": "12.1.0.276" }';
		return;
	}
	

	/** ******************************************************
	* @url GET /ra/Locations
	*/
	public function getLocations() //Somewhat synonymous with customers. Each location is for a specific customer (many:1) Not sure how to best use this with RT yet.
	{
		$outarray = Array();
		$outarray[]=array(
			"id" => 1,
			"type" => "Location",
			"locationName" => "Main",
			"priorityTypes" => [],
			"defaultPriorityTypeId" => null,
		);
		return $outarray;
	}
	
	
	/** ******************************************************
	* @url GET /ra/RequestTypes
	*/
	public function getRequestTypes() //Mapped to RT Queues
	{
		$response=$this->rt->searchQueues("",'','s');
		$outarray = Array();
		foreach($response as $id => $queue) {
			$outarray[]=array(
				"id" => $id,
				"type" => "RequestType",
				"parentId" => null,
				"problemTypeName" => $queue,
				"approvalProcess" => null,
				"customFieldDefinitions" => array(),
				"isLeafChild" => true,
				"hasTechGroup" => false,
				"autoAssignType" => "None",
				"priorityTypeId" => 3,
				"techGroupName" => $queue,
				"sendClientEmailBoolean" => true,
				"sendTechEmailBoolean" => true,
				"sendLevelTechEmailBoolean" => false,
				"sendGroupMgrEmailBoolean" => false,
			);
		}
		return $outarray;
	}
	
	
	/** ******************************************************
	* @url GET /ra/StatusTypes
	*/
	public function getStatusTypes() //Ticket Status
	{
		header('Content-Type: application/json');
		return $this->rtstatusobj;
	}
	
	
	/** ******************************************************
	* @url GET /ra/PriorityTypes
	*/
	public function getPriorityTypes()
	{
		$outarray = array();
		$outarray[]=array(
			"type" => "PriorityType", "id" => 5, "priorityTypeName" => "Critical",
			"dueTimeMinutes" => 1, "alertReminderMinutes" => 1, "clientReminderMinutes" => -1,
		);
		$outarray[]=array(
			"type" => "PriorityType", "id" => 4, "priorityTypeName" => "Urgent",
			"dueTimeMinutes" => 240, "alertReminderMinutes" => -1, "clientReminderMinutes" => -1,
		);
		$outarray[]=array(
			"type" => "PriorityType", "id" => 3, "priorityTypeName" => "High",
			"dueTimeMinutes" => 2880, "alertReminderMinutes" => -1, "clientReminderMinutes" => -1,
		);
		$outarray[]=array(
			"type" => "PriorityType", "id" => 2, "priorityTypeName" => "Medium",
			"dueTimeMinutes" => 10080, "alertReminderMinutes" => -1, "clientReminderMinutes" => -1,
		);
		$outarray[]=array(
			"type" => "PriorityType", "id" => 1, "priorityTypeName" => "Low",
			"dueTimeMinutes" => 132420, "alertReminderMinutes" => -1, "clientReminderMinutes" => -1,
		);
		
		return $outarray;
	}
	
	
	
	/** ******************************************************
	* @url GET /ra/BillingRates
	*/
	public function getBillingRates()
	{
		return array();
	}
	
	
	
	/** ******************************************************
	* @url GET /ra/CustomFieldDefinitions/Asset
	*/
	public function getCustomFieldDefinitions()
	{
		return array();
	}
	
	
	/** ******************************************************
	* @url POST /ra/Tickets
	*/

	public function postTickets($data)
	{
		
		$ticketdata=array(
			"id" => "ticket/new",
			"Queue" => $this->rt_queue,
			/* TODO: Make rt_queue assigned by looking at $data->problemtype->id */
			"Requestors" => $this->rt_username,
			//"Status" => "new",
			"Subject" => $data->subject,
			"Text" => $data->detail,
			"CF.{How Reported}" => 'PSA',
			
			//TODO: Possible expansion?
			//"CF.{Reporter Type}" => 'other',
			//"CF.{Customer}" => 'xxxxxxxx',
		);
		if(isset($data->assets[0]->id)) {
			$ticketdata["CF.{AssetID}"]=$data->assets[0]->id;
		}
		
		$response = $this->rt->createTicket($ticketdata);
		
		$pattern = '/Ticket\ (\d+)\ created/';
		preg_match($pattern, serialize($response), $matches);
		if(isset($matches[1])) {
			$result=array(
				"type" => "Ticket",
				"id" => $matches[1],
				"subject" => $data->subject,
				"statusTypeId" => $data->statustype->id,
				"locationId" => $data->location->id,
				"lastUpdated" => gmdate("Y-m-d\TH:i:s\Z"),
			);
	
			header('HTTP/1.1 201 Created',true);
			header('Server: Apache-Coyote/1.1',true);
			header('Content-Type: application/json;charset=UTF-8',true);
			return $result;
		}
	}
	
	
	
	/** ******************************************************
	* @url GET /ra/Tickets
	*/
	public function getTickets($data)
	{
		global $_GET;
		$search="(Status='new' OR Status='open' OR Status='resolved')";
		$pattern = "/assets.assetNumber='(\d+)'/";
		preg_match($pattern, $_GET['qualifier'], $matches);
		if(isset($matches[1])) {
			$search .= " AND CF.{AssetID}='".$matches[1]."'";
		}
		$tickets = $this->rt->search($search,'Status,-Created', 's');
		if(!is_array($tickets)) {return;}
		
		$results=array();
		
		foreach($tickets as $ticket => $subject) {
			if($ticket != "") {
				$thisticket=$this->getTicketHDMFormat($ticket);
				$results[]=$thisticket;
			}
		}
		
		return $results;
	}
	



	/** ******************************************************
	* @url GET /ra/Tickets/$ticket
	*/
	public function ticketGet($data, $ticket)
	{
		return $this->getTicketHDMFormat($ticket);
	}
	


	/** ******************************************************
	* @url PUT /ra/Tickets/$ticket
	*/
	public function ticketEdit($data, $ticket)
	{
		
		if(isset($data->statustype) && $data->statustype->type == "StatusType") {
			
			foreach($this->rtstatusobj as $status) {
				if($status->id == $data->statustype->id) {
					$textstatus = $status->statusTypeName;
				}
			}
			
			if($textstatus!='') {
				$ticketmods=array(
					"Status" => strtolower($textstatus),
				);
				$results=$this->rt->editTicket($ticket,$ticketmods);
				//TODO: Look for:
				//# Ticket 1366 updated.
				//#Status: Status 'stalled' isn't a valid status for this ticket
				//# You are not allowed to modify ticket 1016.
			}
		}
		
		/* TODO: What status code should we return? */
		return $this->getTicketHDMFormat($ticket);
		
	}
	
	
	/** ******************************************************
	* @url POST /ra/TechNotes
	*/
	public function ticketNotes($data)
	{

		if($data->type == "TechNote" && isset($data->jobticket) && $data->jobticket->type == "Ticket") { //Type=="JobTicket"?? Both in documentation.
			$ticket=$data->jobticket->id;
		}
		$content=array(
			"Text" => $data->noteText,
			"Status" => strtolower($data->statustype->statusTypeName),
		);
		if($data->emailClient) {
			$results=$this->rt->doTicketReply($ticket,$content);
		} else {
			$results=$this->rt->doTicketComment($ticket,$content);
		}

		foreach($this->rtstatusobj as $status) {
			if($status->id == $data->statusTypeId) {
				$textstatus = $status->statusTypeName;
			}
		}
		
		if($textstatus!='') {
			$ticketmods=array(
				"Status" => strtolower($textstatus),
			);
			$results=$this->rt->editTicket($ticket,$ticketmods);
			
			//TODO: Look for:
			//# Ticket 1366 updated.
			//#Status: Status 'stalled' isn't a valid status for this ticket
			//# You are not allowed to modify ticket xxxx.
		}
		
		header('HTTP/1.1 201 Created',true);
		return;
		/* TODO: What data should we return? */
	}
	

	/** ******************************************************
	* @url GET /ra/Tickets/$ticket/escalate
	*/
	public function ticketEscalate($ticket)
	{
		header('HTTP/1.0 501 Not Implemented');
		return;
		/* TODO: What status code should we return? */
	}
	
	
	
	/** ******************************************************
	* @url GET /ra/Tickets/$ticket/deescalate
	*/
	public function ticketDeEscalate($ticket)
	{
		header('HTTP/1.0 501 Not Implemented');
		return;
		/* TODO: What status code should we return? */
	}
	
	
	/** ******************************************************
	* @url GET /ra/Departments
	* @url GET /ra/Departments/$departmentid
	*/
	public function getDepartments($departmentid=null) //Not mapped to anything
	{
		return array();
	}
	
	

	/** ******************************************************
	* @url GET /ra/Rooms
	* @url GET /ra/Rooms/$roomid
	*/
	public function getRooms($roomid=null) //Not mapped to anything
	{
		return array();
	}
	
	

	/** ******************************************************
	* @noAuth
	* @url GET /wa/TicketActions/view
	*/
	public function viewTicket()
	{
		header('Location: '.$this->rt_server_links.'/Ticket/Display.html?id='.$_GET['ticket']);
		exit;
	}
	
	
	/** ******************************************************
	* @url GET /ra/Techs
	* @url GET /ra/Techs/$techid
	*/
	public function getTechs($techid=null)
	{	/* techid may equal "currentTech" */
		return array();
		/* TODO: Is this needed? I haven't seen N-Central query this before. */
	}
	
	
	/** ******************************************************
	* @url GET /ra/Assets
	*/
	public function getAssets($data)
	{
		/* TODO: Actually implement this if there's a real reason to.
		   No asset field on tickets, and a CF can't be told to grab possibilites from a list of Assets.
		   Return fake information for now... it works.
		*/
		global $_GET;
		header('Content-Type: application/json');
		$asset = array(
			"id" => $_GET['assetNumber'],
			"type" => "Asset",
			"macAddress" => null,
			"networkName" => null,
			"assetNumber" => $_GET['assetNumber'],
			"model" => array(
				"id" => 1,
				"type" => "Model"
			),
			"modelName" => "unknown",
			"manufacturer" => array(
				"id" => 1,
				"type" => "Manufacturer"
			),
		);
		return array($asset);
	}
	
	
	/** ******************************************************
	* @url GET /ra/AssetStatuses
	*/
	public function getAssetStatuses()
	{
		/* TODO: Actually implement this if there's a real reason to. */
		return array();
		/*
		print '[
			{ "id": 4, "type": "AssetStatus", "name": "Awaiting Tag" },
			{ "id": 1, "type": "AssetStatus", "name": "Deployed" },
			{ "id": 6, "type": "AssetStatus", "name": "DOA" }
		]';
		return;*/
	}
	
	
	/* ******************************************************
	* Error Processing
	*/
	public function authorize() {
		global $_GET,$_SERVER;
		
		header('HTTP/1.0 200 PSA Integrator');
		if ((isset($_GET['apiKey'])?$_GET['apiKey']:'') == $this->apikey) {
			return true;
		}
		return false;
	}
	
	public function log_call($httpmethod, $method, $params1, $params2, $data) {
		$this->logit($_SERVER['REQUEST_URI']);
		$this->logit($httpmethod.': '.$method."(".serialize($params2).')');
		$this->logit("Posted Data: ".serialize($data));
	}
	public function log_result($httpmethod, $method, $result) {
		$this->logit($_SERVER['REQUEST_URI']);
		$this->logit($httpmethod.': '.$method."(".serialize($params2).')');
		$this->logit("Result: ".serialize($result));
	}
	
	private function logit($data) {
		return;
	}
	
	/* ******************************************************
	* Get ticket, and convert to HDM format
	*/
	private function getTicketHDMFormat($ticket) {
		$ticketdata = $this->rt->getTicketProperties($ticket);
		$thisticket=array(
			"type" => "Ticket",
			"id" => $ticket,
			"subject" => $ticketdata['Subject'],
			"lastUpdated" => gmdate("Y-m-d\TH:i:s\Z",strtotime($ticketdata['LastUpdated'].' EST')),
			"lastUpdatedUtc" => date("Y-m-d\TH:i:s\Z",strtotime($ticketdata['LastUpdated'].' EST')),
			"reportDateUtc" => date("Y-m-d\TH:i:s\Z",strtotime($ticketdata['Created'].' EST')),
			
			"statusTypeId" => $this->rtstatusarr[$ticketdata['Status']]->id,
			"statustype" => $this->rtstatusarr[$ticketdata['Status']],
			"priorityTypeId" => $ticketdata['Priority'],
			"prioritytype" => array(
				"id" => $ticketdata['Priority'],
				"type" => "PriorityType",
				"priorityTypeName" => $ticketdata['Priority'],
			),
			//"techId" => 1,
			"clientTech" => array(
				"type" => "Tech",
				//"id" => 1,
				"displayName" => $ticketdata['Owner'],
			), //:{"id":1,"type":"Tech","email":"steve@sbsroc.com","displayName":"Steve Switzer"}
			"problemtype" => array(
				"type" => "RequestType",
				//"id" => 1,
				"detailDisplayName" => $ticketdata['Queue'],
			), //:{"id":1,"type":"RequestType","detailDisplayName":"Hardware"}
			
			"bookmarkableLink" => $this->rt_server_links.'/Ticket/Display.html?id='.$ticket,

			/*"latestNote" => array(
				"id" => 1,
				"type" => "TechNote",
				"mobileListText" => "<b>J. Admin: </b> ",
				"noteColor" => "clear",
				"noteClass" => "bubble right",
			),*/
/*			"notes" => array(
				array(
					"id" => 2,
					"type" => "TechNote", // ClientNote
					"date" => "2016-11-11T21:43:03Z",
					"isSolution" => false,
					"prettyUpdatedString" => "12 minutes ago <strong>Steve Switzer<\/strong> said",
					"mobileNoteText" => "I have a second note here!",
					"isTechNote" => true,
					"isHidden" => false,
					"attachments" => array(),
					"dateUtc" => "2016-11-12T02:43:03Z"
				),
			),*/
		);
		if($ticketdata['Due'] != 'Not set') {
			$thisticket["displayDueDate"] = date("Y-m-d\TH:i:s\Z",strtotime($ticketdata['Due'].' EST'));
		}
		if($ticketdata['Resolved'] != 'Not set') {
			$thisticket["closeDateUtc"] = gmdate("Y-m-d\TH:i:s\Z",strtotime($ticketdata['Resolved'].' EST'));
		}				
		return $thisticket;

	}
	
	
	/* ******************************************************
	* Error Processing
	*/
	private function doError($code, $msg) {
				error_log($msg);
				$this->server->handleError($code, $msg);
				exit;
	}
}
?>