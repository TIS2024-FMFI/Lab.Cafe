<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends MY_Controller {
	 
	public function __construct(){

		parent::__construct();
        $this->load->model('admin/Loyalty_model', 'Loyalty_model');
		$this->load->model('admin/Printer_model', 'Printer_model');
		$this->load->helper(
			array('fabman_helper')
		);		    
	}

	function index($type=''){}


	public function get_printers() {
		$printers = $this->Printer_model->get_printers();
		$this->output->set_content_type('application/json');
		$this->output->set_output(json_encode(array('printers' => $printers)));
	}

	function check_access($device_id=null, $card_id=null) {
		if (is_null($device_id)) { die('{result: "error, no device_id"}'); }
		if (is_null($card_id)) { die('{result: "error, no card_id"}'); }

		$error = '';
		if (empty($card_id) || empty($device_id)) {
			$error = 'Error code: Missing required parameters. card_id, device_id';
			$this->output->set_content_type('application/json');
			$this->output->set_output(json_encode(array('error' => $error)));
			return;
		}

		$active_member = check_member_active_membership($card_id);
		fabman_resource_log($device_id, $active_member['member']['id']);

		if ($active_member['response']===false) { die ('{"response": 0}'); }
		die ('{"response": 1}');

	}

	public function loyaltycard_pos_load($device_id = null, $card_id = null) {

		if (is_null($device_id)) { die('{"response": 0, "text": "error, no device_id"}'); }
		if (is_null($card_id)) { die('{"response": 0, "text": "error, no card_id"}'); }

		$member = $this->Loyalty_model->loyaltycard_member_get(array('card_id'=>$card_id));

		if (is_null($member)) {
			$token = '85d17fe5-2fda-4404-968a-cd0b8b7262f5';
			$url = 'https://fabman.io/api/v1/members?account=294&keyType=em4102&keyToken=' . $card_id . '&orderBy=name&order=asc&limit=50';
			$authorization = "Authorization: Bearer " . $token;

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_URL, $url);
			$result = curl_exec($ch);
			curl_close($ch) ;
		
			$memberData = json_decode($result, true);

			if (count($memberData) == 0) {
				die('{"response": 0, "text": "error, no member found"}');
			}
						
			$memberData2DB = array(
					"loyaltycard_member_fabman_id" => $memberData[0]['id'],	
					"loyaltycard_member_cardnumber"	=> $card_id,
					"loyaltycard_member_firstname" =>	$memberData[0]['firstName'],	
					"loyaltycard_member_lastname"	=> $memberData[0]['lastName'],
					"loyaltycard_member_email"	=> $memberData[0]['emailAddress']
			);

			$this->Loyalty_model->loyaltycard_member_add($memberData2DB);
			$member = $this->Loyalty_model->loyaltycard_member_get(array('card_id'=>$card_id));
		}

		$newLoyaltycard = array(
			'loyaltycard_pos_device_id' => (int)$device_id,
			'loyaltycard_pos_cardnumber' => $card_id,
			'loyaltycard_pos_access_date' => date('Y-m-d H:i:s'),
			'loyaltycard_pos_end_date' => null,
			'loyaltycard_member_id' => $member['loyaltycard_member_id']
		);

		$this->Loyalty_model->loyaltycard_load($newLoyaltycard); 
		$data = array('name' => $member['loyaltycard_member_firstname'].' '.$member['loyaltycard_member_lastname']) + $member;
		$data = array('response' => 1, 'member' => $data);
		fabman_resource_log($device_id, $data['member']['loyaltycard_member_fabman_id']);
		die(json_encode($data));
	}

	public function loyaltycard_pos($program = null, $action = null) {
		$entityBody = $this->input->raw_input_stream;
		$body = simplexml_load_string($entityBody); 
		$con = json_encode($body); 		
		file_put_contents('log.txt', $_SERVER['REQUEST_URI']."\n\r".$con, FILE_APPEND);

		if ($action=='searchReservations') {
			$data = $this->Loyalty_model->get_active_member(0);
			$this->load->view('admin/api/pos_loyalty', $data);	
		} elseif ($action=='addAccountItem') {

			$comId = $body->comId;
            $items = $body->account->item;

            foreach ($items as $item) {
                $name = $item->name;
                $count = $item->count;
                $price = $item->price;
                $taxRate = $item->taxRate;

                $data = array(
                    'comId' => $loyaltyCard_active,
                    'name' => $name,
                    'count' => $count,
                    'price' => $price,
                    'taxRate' => $taxRate
                );
				
                $this->Loyalty_model->add_purchase($data);
            }

			die('<?xml version="1.0" encoding="UTF-8"?><request></request>');
		}
	}

	public function door_button_open($device_id = null) {
		fabman_resource_log($device_id, 0, "Door opened by button");
		die('{"response": 1}');
	}	

	public function loyaltycard_pos_get($device_id = null) {
		if (is_null($device_id)) { die('{"response": 0, "text": "error, no device_id"}'); }

		$data = $this->Loyalty_model->get_active_member($device_id);
		$this->output->set_content_type('application/json');

		if ($data === false) {
			die('{"response": 0}');
		} else {
			$data['name'] = $data['loyaltycard_member_firstname'].' '.$data['loyaltycard_member_lastname'];
			$data = array('response' => 1, 'member' => $data);
		}

		die(json_encode($data));
	}

	public function loyaltycard_pos_unload($device_id = null) {
		if (is_null($device_id)) { die('{"response": 0, "text": "error, no device_id"}'); }
		$this->Loyalty_model->loyaltycard_unload($device_id);

		die('{"response": 1, "text": "Card unloaded successfully"}');
	}

}
