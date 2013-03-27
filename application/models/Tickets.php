<?php

/**
 * Tickets
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Tickets extends BaseTickets {
	
	public static function grid($rowNum = 10) {
		
		$translator = Zend_Registry::getInstance ()->Zend_Translate;
		
		$config ['datagrid'] ['columns'] [] = array ('label' => null, 'field' => 't.ticket_id', 'alias' => 'ticket_id', 'type' => 'selectall' );
		$config ['datagrid'] ['columns'] [] = array ('label' => $translator->translate ( 'ID' ), 'field' => 't.ticket_id', 'alias' => 'ticket_id', 'sortable' => true, 'direction'=> 'desc', 'searchable' => true, 'type' => 'string' );
		$config ['datagrid'] ['columns'] [] = array ('label' => $translator->translate ( 'Creation date' ), 'field' => 't.date_open', 'alias' => 'creation_date', 'sortable' => true, 'direction'=> 'desc', 'searchable' => true, 'type' => 'date' );
		$config ['datagrid'] ['columns'] [] = array ('label' => $translator->translate ( 'Updating date' ), 'field' => 't.date_open', 'alias' => 'creation_date', 'sortable' => true, 'direction'=> 'desc', 'searchable' => true, 'type' => 'date' );
		$config ['datagrid'] ['columns'] [] = array ('label' => $translator->translate ( 'Category' ), 'field' => 'tc.category', 'alias' => 'category', 'sortable' => true, 'searchable' => true, 'type' => 'string' );
		$config ['datagrid'] ['columns'] [] = array ('label' => $translator->translate ( 'Subject' ), 'field' => 't.subject', 'alias' => 'subject', 'sortable' => true, 'searchable' => true, 'type' => 'string' );
		$config ['datagrid'] ['columns'] [] = array ('label' => $translator->translate ( 'Company' ), 'field' => "CONCAT(c.firstname, ' ', c.lastname, ' ', c.company)", 'alias' => 'customer', 'sortable' => true, 'searchable' => true, 'type' => 'string');
		$config ['datagrid'] ['columns'] [] = array ('label' => $translator->translate ( 'Statuses' ), 'field' => 's.status', 'alias' => 'status', 'sortable' => true, 'searchable' => true);
		$config ['datagrid'] ['columns'] [] = array ('label' => $translator->translate ( 'Replies' ), 'field' => '', 'alias' => 'replies', 'type' => 'string', 'searchable' => false);
		$config ['datagrid'] ['columns'] [] = array ('label' => $translator->translate ( 'Files' ), 'field' => '', 'alias' => 'files', 'type' => 'string', 'searchable' => false);
		
		$config ['datagrid'] ['fields'] = "t.ticket_id,
											t.subject as subject, 
											t.category_id as category_id,
											tc.category as category, 
											s.status as status, 
											c.lastname as lastname,
											DATE_FORMAT(t.date_open, '%d/%m/%Y') as creation_date, 
											CONCAT(c.firstname, ' ', c.lastname, ' ', c.company) as customer";
		
		$dq = Doctrine_Query::create ()
							->select ( $config ['datagrid'] ['fields'] )
							->from ( 'Tickets t' )
							->leftJoin ( 't.TicketsCategories tc' )
							->leftJoin ( 't.Customers c' )
							->leftJoin ( 't.Statuses s' )
							->orderBy('ticket_id desc');
		
		$dq->addSelect('( SELECT COUNT( * ) FROM TicketsNotes tn WHERE tn.ticket_id = t.ticket_id) as replies' );
		$dq->addSelect('( SELECT COUNT( * ) FROM Files f WHERE f.id = t.ticket_id) as files' );
		
		$config ['datagrid'] ['dqrecordset'] = $dq;
		$config ['datagrid'] ['rownum'] = $rowNum;
		$config ['datagrid'] ['basepath'] = "/admin/tickets/";
		$config ['datagrid'] ['rowlist'] = array ('10', '50', '100', '1000' );
	
		$config ['datagrid'] ['massactions']['common'] = array ('bulkexport' => 'Export', 'massdelete' => 'Delete' );
		
		$statuses = Statuses::getList('tickets');
		if(!empty($statuses))
			$customacts = array();
			foreach ($statuses as $key => $value) {
				$customacts['bulk_set_status&status=' . $key ] = "Set as $value";
			}
			$config ['datagrid'] ['massactions']['status'] = $customacts;
					
		
		return $config;
	}
	

	/**
	 * findAll
	 * Get records from the DB
	 * @param $currentPage
	 * @param $rowNum
	 * @param $sort
	 * @param $where
	 * @return array
	 */
	public static function findAll($fields = "*", $currentPage = 1, $rowNum = 2, array $sort = array(), array $where = array()) {
		
		$module = Zend_Controller_Front::getInstance ()->getRequest ()->getModuleName ();
		$controller = Zend_Controller_Front::getInstance ()->getRequest ()->getControllerName ();
		
		// Defining the url sort
		$uri = isset ( $sort [1] ) ? "/sort/$sort[1]" : "";
		$dq = Doctrine_Query::create ()->select ( $fields )->from ( 'Tickets t' )->leftJoin ( 't.TicketsCategories tc' )->leftJoin ( 't.Customers c' )->leftJoin ( 't.Statuses s' );
		
		$pagerLayout = new Doctrine_Pager_Layout ( new Doctrine_Pager ( $dq, $currentPage, $rowNum ), new Doctrine_Pager_Range_Sliding ( array ('chunk' => 10 ) ), "/$module/$controller/list/page/{%page_number}" . $uri );
		
		// Get the pager object
		$pager = $pagerLayout->getPager ();
		
		// Set the Order criteria
		if (isset ( $sort [0] )) {
			$pager->getQuery ()->orderBy ( $sort [0] );
		}
		
		if (isset ( $where ) && is_array ( $where )) {
			foreach ( $where as $filters ) {
				
				if (isset ( $filters [0] ) && is_array ( $filters [0] )) {
					foreach ( $filters as $filter ) {
						$method = $filter ['method'];
						$value = $filter ['value'];
						$criteria = $filter ['criteria'];
						$pager->getQuery ()->$method ( $criteria, $value );
					}
				} else {
					$method = $filters ['method'];
					$value = $filters ['value'];
					$criteria = $filters ['criteria'];
					$pager->getQuery ()->$method ( $criteria, $value );
				}
			}
		}
		
		$pagerLayout->setTemplate ( '<a href="{%url}">{%page}</a> ' );
		$pagerLayout->setSelectedTemplate ( '<a class="active" href="{%url}">{%page}</a> ' );
		
		$records = $pagerLayout->execute ( array (), Doctrine_Core::HYDRATE_ARRAY );
		$pagination = $pagerLayout->display ( null, true );
		return array ('records' => $records, 'pagination' => $pagination, 'pager' => $pager, 'recordcount' => $dq->count () );
	}	
	
	/**
	 * SortingData
	 * Manage the request of sorting of the tickets 
	 * @return string
	 */
	private function sortingData($sort) {
		$strSort = "";
		if (! empty ( $sort )) {
			$sort = addslashes ( htmlspecialchars ( $sort ) );
			$sorts = explode ( "-", $sort );
			
			foreach ( $sorts as $sort ) {
				$sort = explode ( ",", $sort );
				$strSort .= $sort [0] . " " . $sort [1] . ",";
			}
			
			if (! empty ( $strSort )) {
				$strSort = substr ( $strSort, 0, - 1 );
			}
		}
		
		return $strSort;
	}
	
	/**
	 * setNewStatus
	 * Set the status of all items passed
	 * @param $items
	 * @return void
	 */
	public static function setNewStatus($items) {
		$request = Zend_Controller_Front::getInstance ()->getRequest ();
		if(!empty($request)){
			$status = $request->getParams ( 'params' );
			$params = parse_str ( $status ['params'], $output );
			$status = $output ['status'];
			if (is_array ( $items ) && is_numeric ( $status )) {
				foreach ( $items as $ticketsid ) {
					if (is_numeric ( $ticketsid )) {
						self::setStatus ( $ticketsid, $status );
					}
				}
				return true;
			}
		}
		return false;
	}
	
	/**
	 * massdelete
	 * delete the tickets selected 
	 * @param array
	 * @return Boolean
	 */
	public static function massdelete($items) {
		$retval = Doctrine_Query::create ()->delete ()->from ( 'Domains d' )->whereIn ( 'd.domain_id', $items )->execute ();
		return $retval;
	}
	
	/**
	 * find
	 * Get a record by ID
	 * @param $id
	 * @return Doctrine Record
	 */
	public static function find($id) {
		return Doctrine::getTable ( 'Tickets' )->findOneBy ( 'ticket_id', $id );
	}
	
	/**
	 * getWaitingReply
	 * Get all the tickects that need a answer by the customer
	 * status_id = 22 -> Waiting Reply
	 * @return array
	 */
	public static function getWaitingReply() {
		return Doctrine_Query::create ()->from ( 'Tickets t' )
		->leftJoin ( 't.TicketsCategories tc' )
		->leftJoin ( 't.Customers c' )
		->leftJoin ( 't.Statuses s' )
		->where ( 't.status_id = ?', Statuses::id("processing", "tickets") )
		->execute ( array (), Doctrine_Core::HYDRATE_ARRAY );
	}
	
	/**
	 * setStatus
	 * Set a record with a status
	 * @param $id, $status
	 * @return Void
	 */
	public static function setStatus($id, $status) {
		if(is_numeric($id)){
			$object = Doctrine::getTable ( 'Tickets' )->find ( $id );
			$object->status_id = $status;
			$object->date_close = date ( 'Y-m-d H:i:s' );
			return $object->save ();
		}
		return false;
	}
	
	/**
	 * getByCustomerID
	 * Get all data  
	 * @param $customerID
	 * @return Array
	 */
	public static function getByCustomerID($customerID, $fields) {
		$records = Doctrine_Query::create ()->select ( $fields )
							->from ( 'Tickets t' )
							->leftJoin ( 't.Customers c' )
							->leftJoin ( 't.Statuses s' )
							->where ( "customer_id = ?", $customerID )
							->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		
		return $records;
	}
	
	/**
	 * getAllInfo
	 * Get all data starting from the ticketID 
	 * @param $id
	 * @return Doctrine Record / Array
	 */
	public static function getAllInfo($id, $fields = "*", $retarray = false) {
		$dq = Doctrine_Query::create ()->select ( $fields )
							->from ( 'Tickets t' )
							->leftJoin ( 't.Customers c' )
							->leftJoin ( 't.Domains d' )
							->leftJoin ( 'd.DomainsTlds dt' )
							->leftJoin ( 'dt.WhoisServers ws' )
							->leftJoin ( 't.Statuses s' )
							->leftJoin ( 't.Tickets t2' )
							->where ( "ticket_id = ?", $id )
							->limit ( 1 );
		
		$retarray = $retarray ? Doctrine_Core::HYDRATE_ARRAY : null;
		$items = $dq->execute ( array (), $retarray );
		
		return $items;
	}
	
	/**
	 * Update the ticket votes
	 *
	 * @param integer $noteid
	 */
	public static function updateTickectVote($noteid){
		$votes = array();
	
		if(!empty($noteid) && is_numeric($noteid)){
				
			// Get the parent tickect
			$Note = Doctrine::getTable ( 'TicketsNotes' )->find ( $noteid );
				
			if(is_numeric($Note->ticket_id)){
	
				// Get all the notes
				$notes = Tickets::Notes($Note->ticket_id, 'vote, admin', true );
				foreach ($notes as $note){
						
					// Get all the admin answers votes
					if($note['admin']){
						$votes[] = $note['vote'];
					}
				}
	
				// Count the occurrences of the votes
				$occurences = count($votes);
				$totalvotes = array_sum($votes);
	
				$average = $totalvotes / $occurences;
				if(is_numeric($average)){
					$Ticket = Doctrine::getTable ( 'Tickets' )->find ( $Note->ticket_id );
					$Ticket->vote = $average;
					$Ticket->save();
				}
				return true;
			}
		}
		return false;
	}	
	
	
	/**
	 * Notes
	 * Get all the Notes starting from the ticketID 
	 * @param $id
	 * @return Doctrine Record / Array
	 */
	public static function Notes($id, $fields = "*", $retarray = false) {
		$dq = Doctrine_Query::create ()->select ( $fields )
				->from ( 'TicketsNotes tn' )
				->leftJoin ( 'tn.Tickets t' )
				->leftJoin ( 't.Customers c' )
				->where( "ticket_id = $id" );
		
		$retarray = $retarray ? Doctrine_Core::HYDRATE_ARRAY : null;
		$items = $dq->execute ( array (), $retarray );
		return $items;
	}
	
	/**
	 * List of the last 10 tickets
	 * @return array
	 */
	public static function Last($customerid = "", $limit=10) {
		$dq = Doctrine_Query::create ()
								->select ( "t.ticket_id, 
											t.subject as subject, 
											tc.category as category, 
											DATE_FORMAT(t.date_updated, '%d/%m/%Y %H:%i') as updated,
											CONCAT(c.company, ' ', c.lastname, ' ', c.firstname ) as fullname, 
											s.status as status" )
								->from ( 'Tickets t' )
								->orderBy('t.date_updated')
								->leftJoin ( 't.Customers c' )
								->leftJoin ( 't.Statuses s' )
								->leftJoin ( 't.TicketsCategories tc' );
		
		if (is_numeric ( $customerid )) {
			$dq->where ( 't.customer_id = ?', $customerid );
		}

		// Open, Processing and Waiting Reply tickets
		$statuses = array(Statuses::id("expectingreply", "tickets"), Statuses::id("processing", "tickets"));
		$dq->whereIn('t.status_id', $statuses);
		
		$dq->orderBy ( 't.date_open desc' )->limit ( $limit );
		$records = $dq->execute ( null, Doctrine::HYDRATE_ARRAY );
		
		for ($i=0;$i<count($records); $i++){
			$records[$i]['subject'] = Shineisp_Commons_Utilities::truncate($records[$i]['subject']);
		}
		
		return $records;
	}
	
	/**
	 * getList
	 * Get a list ready for the html select object
	 * @return array
	 */
	public static function getList($empty = false) {
		$items = array ();
		$translations = Zend_Registry::getInstance ()->Zend_Translate;
		
		$records = Doctrine_Query::create ()->select ( "ticket_id, DATE_FORMAT(date_open, '%d/%m/%Y') as date_open, subject" )->from ( 'Tickets t' )->execute ( array (), Doctrine_Core::HYDRATE_ARRAY );
		
		if ($empty) {
			$items [] = $translations->translate ( 'Select ...' );
		}
		
		foreach ( $records as $c ) {
			$items [$c ['ticket_id']] = $c ['ticket_id'] . " - " . $c ['subject'];
		}
		
		return $items;
	}
	
	/**
	 * getListbyCustomerId
	 * Get a list ready for the html select object
	 * @return array
	 */
	public static function getListbyCustomerId($customer_id, $empty = false, $abbreviation=false) {
		$items = array ();
		$translations = Zend_Registry::getInstance ()->Zend_Translate;
		
		$records = Doctrine_Query::create ()->select ( "ticket_id, DATE_FORMAT(date_open, '%d/%m/%Y') as date_open, subject, s.status as status" )
											->from ( 'Tickets t' )
											->leftJoin ( 't.Statuses s' )
											->where('t.customer_id = ?', $customer_id)
											->execute ( array (), Doctrine_Core::HYDRATE_ARRAY );
		
		if ($empty) {
			$items [] = $translations->translate ( 'Select ...' );
		}
		
		foreach ( $records as $c ) {
			if($abbreviation){
				$subject = Shineisp_Commons_Utilities::truncate($c ['subject'], 25);
			}else{
				$subject = $c ['subject'];
			}
			$items [$c ['ticket_id']] = $c ['ticket_id'] . " - " . $c ['status'] . " - " . $subject;
		}
		
		return $items;
	}
	
	/**
	 * saveNew
	 * Save the data
	 */
	public static function saveNew($params, $customerid) {
		$translator = Zend_Registry::getInstance ()->Zend_Translate;
		$tickets = new Tickets ();
		
		$operatorId = Settings::findbyParam('tickets_operator', 'admin', Isp::getActiveISPID());
		if(!is_numeric($operatorId)){
			$operator = AdminUser::getFirstAdminUser();
			$operatorId = $operator['user_id'];
		}else{
			$operator = AdminUser::getAllInfo($operatorId);
		}

		if (is_numeric ( $customerid )) {
			$subject = ! empty ( $params ['subject'] ) ? $params ['subject'] : $translator->translate ( 'nosubject' );
			
			$tickets->subject = ! empty ( $params ['subject'] ) ? $params ['subject'] : $translator->translate ( 'Generic Issue' );
			$tickets->description = nl2br($params ['note']);
			$tickets->category_id = $params ['category_id'];
			$tickets->customer_id = $customerid;
			$tickets->user_id = $operatorId;
			$tickets->date_open = date ( 'Y-m-d H:i:s' );
			$tickets->domain_id = is_numeric($params ['domain_id']) && $params ['domain_id'] > 0 ? $params ['domain_id'] : NULL;
			$tickets->status_id = Statuses::id("expectingreply", "tickets"); // Expecting a reply
			$tickets->save ();
			
			$id = $tickets->getIncremented ();
			
			// Save the upload file
			self::UploadDocument($id, $customerid);
			
			// Create the fast link
			Fastlinks::CreateFastlink ( 'tickets', 'edit', json_encode ( array ('id' => $id ) ), 'tickets', $id, $customerid );
			
			// Send message
			self::sendMessage ( $id );
			
			return $id;
		}
	}
	
	/**
	 * sendMessage
	 * Send the email for the confirmation
	 * @param integer $customerid
	 */
	public static function sendMessage($ticketid) {
		$ticket = self::getAllInfo ( $ticketid, null, true );
		
		if (! empty ( $ticket [0] )) {
			$operator = AdminUser::getAllInfo($ticket [0] ['user_id']);
			$attachment = self::hasAttachments($ticketid);
			
			$isp = Isp::getActiveISP ();
			if ($isp) {
								
				$customer = $ticket [0] ['Customers'];
				$ispmail = explode ( "@", $isp ['email'] );
				$ispmail = "noreply@" . $ispmail [1];
				
				$retval = Shineisp_Commons_Utilities::getEmailTemplate ( 'ticket_message' );
				
				if ($retval) {
					$s = $retval ['subject'];
					$rec = Fastlinks::findlinks ( $ticketid, $customer ['customer_id'], 'tickets' );
					if (! empty ( $rec[0]['code'] )) {
						$customer_url = "http://" . $_SERVER ['HTTP_HOST'] . "/index/link/id/" . $rec[0]['code'];
						$admin_url = "http://" . $_SERVER ['HTTP_HOST'] . "/admin/login/link/id/" . $rec[0]['code'];
					}
					$subject = str_replace ( "[subject]", $ticket [0] ['subject'], $s );
					$subject = str_replace ( "[company]", $isp ['company'], $subject );
					$subject = str_replace ( "[issue_number]", $ticketid, $subject );
					
					$in_reply_to = md5($ticketid)  . "@" . $_SERVER['HTTP_HOST'];
					
					$body =  $retval ['template'];
					$body = str_replace ( "[subject]", $subject, $body );
					$body = str_replace ( "[customer]", $customer ['firstname'] . " " . $customer ['lastname'] . " " . $customer ['company'], $body );
					$body = str_replace ( "[issue_number]", $ticketid, $body );
					
					if(!empty($attachment)){
						$body = str_replace ( "[attachment]", "http://" . $_SERVER['HTTP_HOST'] . $attachment[0]['path'] . $attachment[0]['file'], $body );
						$body = str_replace ( "[attachment_name]", $attachment[0]['file'], $body );
					}else{
						$body = str_replace ( "[attachment]", "", $body );
						$body = str_replace ( "[attachment_name]", "", $body );
					}
					
					$body = str_replace ( "[operator]", $operator['lastname'] . " " . $operator['firstname'] . ".", $body );
					$body = str_replace ( "[status]", Statuses::getLabel ( $ticket [0] ['status_id'] ), $body );
					$body = str_replace ( "[link]", $customer_url, $body );
					$body = str_replace ( "[date_open]", Shineisp_Commons_Utilities::formatDateOut ( $ticket[0]['date_open'] ), $body );
					$body = str_replace ( "[date_close]", Shineisp_Commons_Utilities::formatDateOut ( $ticket[0]['date_close'] ), $body );
					$body = str_replace ( "[description]", $ticket[0] ['description'], $body );
					$body = str_replace ( "[company]", $isp ['company'], $body );
					
					Shineisp_Commons_Utilities::SendEmail ( $ispmail, Contacts::getEmails($customer ['customer_id']), null, $subject, $body, true, $in_reply_to);
					
					$body = str_replace ( $customer_url, $admin_url . "/keypass/" . Shineisp_Commons_Hasher::hash_string($operator['email']), $body );
					Shineisp_Commons_Utilities::SendEmail ( $ispmail, $isp ['email'], null, $subject, $body, true, $in_reply_to);
					return true;
				}
			}
		}
		return false;
	}
	/**
	 * sendMessageNotes
	 * Send the email for the confirmation
	 * @param integer $customerid
	 */
	public static function sendMessageNotes($noteid) {
		$ticket = TicketsNotes::getAllInfo ( $noteid );
		$isp = Isp::getActiveISP ();
		if (! empty ( $ticket [0] )) {
			$operator = AdminUser::getAllInfo($ticket [0] ['Tickets']['user_id']);
						
			$customer = $ticket [0] ['Tickets']['Customers'];
			if ($isp) {
				$ispmail = explode ( "@", $isp ['email'] );
				$ispmail = "noreply@" . $ispmail [1];
				
				$retval = Shineisp_Commons_Utilities::getEmailTemplate ( 'ticket_message' );
				
				if ($retval) {
					$s = $retval ['subject'];
					$ticketid =  $ticket [0] ['ticket_id'];
					$attachment = self::hasAttachments($ticketid);
					
					$in_reply_to = md5($ticketid)  . "@" . $_SERVER['HTTP_HOST'];
					
					$rec = Fastlinks::findlinks ( $ticketid, $customer ['customer_id'], 'tickets' );
					
					if (! empty ( $rec[0]['code'] )) {
						$customer_url = "http://" . $_SERVER ['HTTP_HOST'] . "/index/link/id/" . $rec[0]['code'] . "#last";
						$admin_url = "http://" . $_SERVER ['HTTP_HOST'] . "/admin/login/link/id/" . $rec[0]['code'];
					}
					
					$subject = str_replace ( "[subject]", $ticket [0] ['Tickets']['subject'], $s );
					$subject = str_replace ( "[company]", $isp ['company'], $subject );
					$subject = str_replace ( "[issue_number]", $ticketid, $subject );
					
					$body = $retval ['template'];
					$body = str_replace ( "[subject]", $subject, $body );
					$body = str_replace ( "[customer]", $customer ['firstname'] . " " . $customer ['lastname'] . " " . $customer ['company'], $body );
					$body = str_replace ( "[issue_number]", $ticketid, $body );
					
					if(!empty($attachment)){
						$body = str_replace ( "[attachment]", "http://" . $_SERVER['HTTP_HOST'] . $attachment[0]['path'] . $attachment[0]['file'], $body );
						$body = str_replace ( "[attachment_name]", $attachment[0]['file'], $body );
					}else{
						$body = str_replace ( "[attachment]", "", $body );
						$body = str_replace ( "[attachment_name]", "", $body );
					}
						
					$body = str_replace ( "[operator]", $operator['lastname'] . " " . $operator['firstname'] . ".", $body );
					
					$body = str_replace ( "[status]", $ticket [0] ['Tickets']['Statuses'] ['status'] , $body );
					$body = str_replace ( "[link]", $customer_url, $body );
					$body = str_replace ( "[date_open]", Shineisp_Commons_Utilities::formatDateOut ( $ticket[0]['Tickets']['date_open'] ), $body );
					$body = str_replace ( "[date_close]", Shineisp_Commons_Utilities::formatDateOut ( $ticket[0]['Tickets']['date_close'] ), $body );
					$body = str_replace ( "[description]", $ticket[0] ['note'], $body );
					$body = str_replace ( "[company]", $isp ['company'], $body );
					Shineisp_Commons_Utilities::SendEmail ( $ispmail, Contacts::getEmails($customer ['customer_id']), null, $subject, $body, true, $in_reply_to );
					
					$body = str_replace ( $customer_url, $admin_url . "/keypass/" . Shineisp_Commons_Hasher::hash_string ( $operator['email'] ), $body );
					Shineisp_Commons_Utilities::SendEmail ( $ispmail, $isp ['email'], null, $subject, $body, true, $in_reply_to );
					
					return true;
				}
			}
		}
		return false;
	}
	
	/**
	 * Has the Ticket some attachment?
	 * @return array
	 */
	public static function hasAttachments($ticketid) {
		
		return Files::findbyExternalId($ticketid, "tickets");
		
	}
	
	
	/**
	 * Summary of all the tickets
	 * @return array
	 */
	public static function summary() {
		$chart = "";
		
		$records = Doctrine_Query::create ()
										->select ( "t.ticket_id, count(*) as items, s.status as status" )
										->from ( 'Tickets t' )
										->leftJoin ( 't.Statuses s' )
										->where("s.section = 'tickets'")
										->groupBy('s.status')
										->execute(array (), Doctrine_Core::HYDRATE_ARRAY);
		
		// Strip the customer_id field
		if(!empty($records)){
			foreach($records as $key => $value) {
			  	array_shift($value);
			  	$newarray[] = $value;
			  	$chartLabels[] = $value['status'];
			  	$chartValues[] = $value['items'];
			}
			// Chart link
			$chart = "https://chart.googleapis.com/chart?chs=250x100&chd=t:".implode(",", $chartValues)."&cht=p3&chl=".implode("|", $chartLabels);
		}
		
		$record_group2 = Doctrine_Query::create ()
										->select ( "t.ticket_id, count(*) as items" )
										->from ( 'Tickets t' )
										->execute(array (), Doctrine_Core::HYDRATE_ARRAY);
		
		$newarray[] = array('items' => $record_group2[0]['items'], 'status' => "Total");
		
		return array('data' => $newarray, 'chart' => $chart);
	}
	
	
 	/**
     * UploadDocument
     * the extensions allowed are JPG, GIF, PNG
     */
    public static function UploadDocument($id, $customerid){
    	try{
    		
	    	$attachment = new Zend_File_Transfer_Adapter_Http();
	    	
			$files = $attachment->getFileInfo();
			
			// Create the directory
			@mkdir ( PUBLIC_PATH . "/documents/", 0777, true);
			@mkdir ( PUBLIC_PATH . "/documents/customers/", 0777, true );
			@mkdir ( PUBLIC_PATH . "/documents/customers/$customerid/", 0777, true );
			@mkdir ( PUBLIC_PATH . "/documents/customers/$customerid/tickets/", 0777, true );
			@mkdir ( PUBLIC_PATH . "/documents/customers/$customerid/tickets/$id/", 0777, true );
			
			if(is_dir(PUBLIC_PATH . "/documents/customers/$customerid/tickets/$id/")){
				// Set the destination directory
				$attachment->setDestination ( PUBLIC_PATH . "/documents/customers/$customerid/tickets/$id/" );
				
				if ($attachment->receive()) {
					return Files::saveit($files['attachments']['name'], "/documents/customers/$customerid/tickets/$id/", 'tickets', $id);
				}	
			}
    	}catch (Exception $e){
			echo $e->getMessage();
			die;	    		
    	}
    }	
    

	######################################### CRON METHODS ############################################
	
    /**
     * This batch has been created in order to remind customers
     * that one or more tickets are still open.
     */
    public static function checkTickets() {
    	$isp = Isp::getActiveISP ();
    	$tickets = Tickets::getWaitingReply ();
    
    	// Get the template from the main email template folder
    	$retval = Shineisp_Commons_Utilities::getEmailTemplate ( 'ticket_waitreply' );
    
    	foreach ( $tickets as $ticket ) {
    		$customer = $ticket ['Customers'];
    			
    		// Get the fastlink attached
    		$link_exist = Fastlinks::findlinks ( $ticket ['ticket_id'], $customer ['customer_id'], 'tickets' );
    		if (count ( $link_exist ) > 0) {
    			$fastlink = $link_exist [0] ['code'];
    		} else {
    			$fastlink = Fastlinks::CreateFastlink ( 'tickets', 'edit', json_encode ( array ('id' => $ticket ['ticket_id'] ) ), 'tickets', $ticket ['ticket_id'], $customer ['customer_id'] );
    		}
    			
    		$customer_url = "http://" . $_SERVER ['HTTP_HOST'] . "/index/link/id/$fastlink";
    			
    		if ($retval) {
    			$subject = $retval ['subject'];
    			$Template = nl2br ( $retval ['template'] );
    			$subject = str_replace ( "[subject]", $ticket ['subject'], $subject );
    			$Template = str_replace ( "[subject]", $ticket ['subject'], $Template );
    			$Template = str_replace ( "[lastname]", $customer ['lastname'], $Template );
    			$Template = str_replace ( "[issue_number]", $ticket ['ticket_id'], $Template );
    			$Template = str_replace ( "[date_open]", Shineisp_Commons_Utilities::formatDateOut ( $ticket ['date_open'] ), $Template );
    			$Template = str_replace ( "[link]", $customer_url, $Template );
    			$Template = str_replace ( "[signature]", $isp ['company'] . "<br/>" . $isp ['website'], $Template );
    
    			Shineisp_Commons_Utilities::SendEmail ( $isp ['email'], Contacts::getEmails($customer ['customer_id']), null, $subject, $Template, true );
    		}
    	}
    	return true;
    }
    
	######################################### BULK ACTIONS ############################################
	
	
	/**
	 * massdelete
	 * delete the tickets selected 
	 * @param array
	 * @return Boolean
	 */
	public static function bulk_delete($items) {
		if(!empty($items)){
			return self::massdelete($items);
		}
		return false;
	}
	

	/**
	 * Set the status of the records
	 * @param array $items Items selected
	 * @param array $parameters Custom paramenters
	 */
	public function bulk_set_status($items, $parameters) {
		if(!empty($parameters['status'])){
			foreach ($items as $item) {
				self::setStatus($item, $parameters['status']);
			}
		}
		return true;
	}	
}