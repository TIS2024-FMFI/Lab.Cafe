<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Finance extends MY_Controller {

	public function __construct(){

		parent::__construct();
		auth_check();
		$this->load->model('admin/Loyalty_model', 'Loyalty_model');
	}

    public function eblok_categories_reload() {
        $data['title'] = 'Bločky, update kategorii zo superfaktury';
        $this->load->view('admin/includes/_header');
        $this->load->view('admin/finance/eblok_categories_reload', $data);
        $this->load->view('admin/includes/_footer');
    }

    public function eblok() {
        $data['title'] = 'Bločky, náklady';
		$filePath = 'categories.html';

		if (file_exists($filePath)) {
			$content = file_get_contents($filePath);
			$data['categories'] = $content;
		} else {
			$data['categories'] = "The file does not exist.";
		}		

        $this->load->view('admin/includes/_header');
        $this->load->view('admin/finance/eblok', $data);
        $this->load->view('admin/includes/_footer');
    }

	public function smena($id = 0) {

		$this->load->helper(array('curl_helper', 'papaya_helper'));
		$shift_index = (int) $id;

		$args = array();
		$day = date('j');
		$month = date('n');
		$year = date('Y');

		$dateFrom = mktime(0,0,1,$month,$day,$year).'000';
		$dateTo = mktime(23,59,59,$month,$day,$year).'000';
		$dateFormat = "d.m.Y H:i:s"; 

		papaya_login();
		$url = url_root."admin/AccountingPeriodServlet";
		$htm = curl_post($url, $args, 30, TRUE, 'json', 'GET');
		$htm =  (array) json_decode($htm);		

		$workshift_id = $htm[$shift_index]->id;

		$url = url_root."admin/WorkShiftServlet?id=".$workshift_id;
		$htm = curl_post($url, $args, 30, TRUE, 'json', 'GET');
		$htm =  (array) json_decode($htm);

		$data['title'] = "Today revenue";
		$data['start'] = $htm['start'];
		$data['end'] = $htm['end'];
		$data['generated_on'] = date($dateFormat, time());
		$dateFrom = strtotime($htm["start"]).'000';
		$dateTo = isset($htm['end']) ? strtotime($htm["end"]).'000' : time().'000';
		$data['prev_link_url'] = site_url('admin/finance/smena').'/'.($shift_index+1);
		$data['next_link_url'] = site_url('admin/finance/smena').'/'.(($shift_index>0) ? ($shift_index-1) : $shift_index);
		$data['next_link_disabled'] = (($shift_index>0) ? '' : 'disabled');
		
		$data['card_total'] = $htm['totals']->RECEIPT->CARD;
		$data['cash_total'] = $htm['totals']->RECEIPT->CASH;
		$data['operating_cost'] = $htm['totals']->RECEIPT->OPERATING_COST;
		$data['total'] = $htm['totalAmount'];

		$args = array();
		$url_arg = "sEcho=3&iColumns=12&sColumns=%2C%2C%2C%2C%2C%2C%2C%2C%2C%2C%2C&iDisplayStart=0&iDisplayLength=100&mDataProp_0=0&sSearch_0=&bRegex_0=false&bSearchable_0=true&bSortable_0=false&mDataProp_1=1&sSearch_1=&bRegex_1=false&bSearchable_1=true&bSortable_1=false&mDataProp_2=2&sSearch_2=&bRegex_2=false&bSearchable_2=true&bSortable_2=false&mDataProp_3=3&sSearch_3=&bRegex_3=false&bSearchable_3=true&bSortable_3=false&mDataProp_4=4&sSearch_4=&bRegex_4=false&bSearchable_4=true&bSortable_4=false&mDataProp_5=5&sSearch_5=&bRegex_5=false&bSearchable_5=true&bSortable_5=false&mDataProp_6=6&sSearch_6=&bRegex_6=false&bSearchable_6=true&bSortable_6=false&mDataProp_7=7&sSearch_7=&bRegex_7=false&bSearchable_7=true&bSortable_7=false&mDataProp_8=8&sSearch_8=&bRegex_8=false&bSearchable_8=true&bSortable_8=true&mDataProp_9=9&sSearch_9=&bRegex_9=false&bSearchable_9=true&bSortable_9=true&mDataProp_10=10&sSearch_10=&bRegex_10=false&bSearchable_10=true&bSortable_10=true&mDataProp_11=11&sSearch_11=&bRegex_11=false&bSearchable_11=true&bSortable_11=false&sSearch=&bRegex=false&iSortCol_0=8&sSortDir_0=desc&iSortingCols=1";
		$url = url_root."admin/ViewAccountingTransactionsServlet?".$url_arg."&filter[createDate][start]=".$dateFrom."&filter[createDate][end]=".$dateTo."&filter[status][OPENED]=true";
		$htm = curl_post($url, $args, 30, TRUE, 'str', 'GET');
		$htm = (array) json_decode($htm);

		$sum = 0; 
		foreach ($htm['aaData'] as $key => $value) {
			$value[6] = str_replace(",", ".", $value[6]);
			$sum += floatval($value[6]);
		}

		$data['otvorene_ucty'] = $sum;
		$data['total_today'] = ($data['card_total']+$data['cash_total']+$data['otvorene_ucty']);
		$data['aktivne_ucty'] = array();

		foreach ($htm['aaData'] as $key => $value) {

		  $start_date = new DateTime($value[10]);
		  $since_start = $start_date->diff(new DateTime("now"));

		  $timediff_str = ($since_start->days > 0 ? $since_start->days.' days ' : '');
		  $timediff_str .= $since_start->h.':'.$since_start->i.':'.$since_start->s;

			$accTxId = preg_match('/accTxId\=([^"]+)/', $value[13], $match);
			$accTxId = $match[1];
			$url1 = url_root."admin/DetailAccountingTransactionServlet?accountingTransactionId=".$accTxId;
			$htm1 = curl_post($url1, $args, 30, TRUE, 'str', 'GET');
			$htm1 =  (array) json_decode($htm1);
			$polozky_array = array();
			foreach ($htm1['accountingEntries'] as $key1 => $value1) {
				array_push($polozky_array, array( 
					"nazov" => $value1->title,
					"count" => $value1->count,
					"priceSum" => $value1->priceSum));
			} 
			
			$data['aktivne_ucty'][$value[1]] = array();
			$data['aktivne_ucty'][$value[1]]['ucet_polozky'] = array();
			$data['aktivne_ucty'][$value[1]]['ucet_cislo'] = $value[0];
			$data['aktivne_ucty'][$value[1]]['ucet_meno'] = $value[1];
			$data['aktivne_ucty'][$value[1]]['ucet_vytvoreny'] = $value[10];
			$data['aktivne_ucty'][$value[1]]['ucet_timediff'] = $timediff_str;
			$data['aktivne_ucty'][$value[1]]['ucet_suma'] = $value[6];
			$data['aktivne_ucty'][$value[1]]['ucet_polozky'] = $polozky_array;
		}

		$this->load->view('admin/includes/_header');
		$this->load->view('admin/finance/smena', $data);
		$this->load->view('admin/includes/_footer');
	}

	public function purchase_list() {
		$data['purchases'] = $this->Loyalty_model->get_all_purchases();
		$this->load->view('admin/includes/_header');
		$this->load->view('admin/finance/purchases_list', $data);
		$this->load->view('admin/includes/_footer');
	}

    public function purchase_delete($purchase_id) {
        $this->Loyalty_model->purchase_delete($purchase_id);
        redirect('admin/finance/purchase_list');
    }

	 public function purchase_add($pos_id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = array(
                'loyaltycard_purchase_pos_id' => $pos_id,
                'loyaltycard_purchase_name' => $this->input->post('purchaseName'),
				'loyaltycard_purchase_count' => $this->input->post('purchaseCount'),
				'loyaltycard_purchase_price' => $this->input->post('purchasePrice'),
				'loyaltycard_purchase_taxRate' => $this->input->post('purchaseTax')
            );
            
            $purchase_id = $this->Loyalty_model->purchase_add($data);
            redirect('admin/finance/purchase_detail/'.$pos_id);

        } else {
			$data['pos_id'] = $pos_id;
            $this->load->view('admin/includes/_header');
            $this->load->view('admin/finance/purchase_add', $data);
            $this->load->view('admin/includes/_footer');
        }
    }

	public function purchase_edit($purchase_id) {
		
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$data = array(
				'loyaltycard_purchase_name' => $this->input->post('purchaseName'),
				'loyaltycard_purchase_count' => $this->input->post('purchaseCount'),
				'loyaltycard_purchase_price' => $this->input->post('purchasePrice'),
				'loyaltycard_purchase_taxRate' => $this->input->post('purchaseTax')
			);

			$this->Loyalty_model->purchase_update($purchase_id, $data);
			redirect('admin/finance/purchase_detail/'.$this->input->post('purchasePOSId'));

		} else {
			$data['purchase'] = $this->Loyalty_model->get_purchase_by_id($purchase_id);
			$this->load->view('admin/includes/_header');
			$this->load->view('admin/finance/purchase_edit', $data);
			$this->load->view('admin/includes/_footer');
		}
	}

	public function purchase_detail($purchase_id) {
		$data['purchase_id'] = $purchase_id;
		$data['purchases'] = $this->Loyalty_model->purchases_detail($purchase_id);
		$this->load->view('admin/includes/_header');
		$this->load->view('admin/finance/purchases_detail', $data);
		$this->load->view('admin/includes/_footer');
	}
}
?>
