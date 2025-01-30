<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Printers extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->config->set_item('csrf_protection', true); 
        $this->load->model('admin/Printer_model', 'Printer_model');    
        $this->load->model('admin/PrintJob_model', 'PrintJob_model');
        $this->load->model('admin/Room_model', 'Room_model');
    }

    public function list() {
        $data['printers'] = $this->Printer_model->get_printers();
		$this->load->view('admin/includes/_header');
        $this->load->view('admin/printers/list', $data);
		$this->load->view('admin/includes/_footer');
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = array(
                'name' => $this->input->post('printerName'),
                'description' => $this->input->post('printerDescription'),
                'address' => $this->input->post('printerAddress'),
                'api_key' => $this->input->post('printerApiKey')
            );

            $printer_id = $this->Printer_model->create_printer($data);
            redirect('admin/printers/list');

        } else {
            $this->load->view('admin/includes/_header');
            $this->load->view('admin/printers/add');
            $this->load->view('admin/includes/_footer');
        }
    }

    public function edit($printer_id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = array(
                'name' => $this->input->post('printerName'),
                'description' => $this->input->post('printerDescription'),
                'address' => $this->input->post('printerAddress'),
                'api_key' => $this->input->post('printerApiKey')
            );

            $this->Printer_model->update_printer($printer_id, $data);
            redirect('admin/printers/list');

        } else {
            $data['printer'] = $this->Printer_model->get_printer_by_id($printer_id);
            $this->load->view('admin/includes/_header');
            $this->load->view('admin/printers/edit', $data);
            $this->load->view('admin/includes/_footer');
        }
    }

    public function delete($printer_id) {
        $this->Printer_model->delete_printer($printer_id);
        redirect('admin/printers/list');
    }


    public function print_jobs() {
        $data['print_jobs'] = $this->PrintJob_model->get_all_print_jobs();
        $this->load->view('admin/includes/_header');
        $this->load->view('admin/printers/print_jobs_list', $data);
        $this->load->view('admin/includes/_footer');
    }


    public function print_job_edit($id) {
        $data['print_job'] = $this->PrintJob_model->get_print_job_by_id($id);
        $this->load->view('admin/includes/_header');
        $this->load->view('admin/printers/print_job_edit', $data);
        $this->load->view('admin/includes/_footer');
    }

    public function print_jobs_list($printer_id = 0) {
        if ($printer_id!=0) {
            $printJobs = $this->PrintJob_model->get_print_jobs_by_printer_id($printer_id);
            $printerData = $this->Printer_model->get_printer_by_id($printer_id);
            $data['printer'] = $printerData->name;

        } else {
            $printJobs = $this->PrintJob_model->get_all_print_jobs();
            $data['printer'] = 'Všetky tlačiarne';           
        }    

        $data['printJobs'] = $printJobs;
        $this->load->view('admin/includes/_header');
        $this->load->view('admin/printers/print_jobs_list', $data);
        $this->load->view('admin/includes/_footer');
    }

    public function print_job_logs($id = 0) {
        $data['print_job'] = $this->PrintJob_model->get_print_job_by_id($id);

        if (!$data['print_job']) {
            $data['filename'] = 'All print jobs';
        } else {
            $data['filename'] = $id.': '.$data['print_job']['filename'];
        }

        $data['printer_id'] = $data['print_job']['printer_id'];
        $data['print_logs'] = $this->PrintJob_model->get_print_logs_by_job_id($id);
        $this->load->view('admin/includes/_header');
        $this->load->view('admin/printers/print_job_logs', $data);
        $this->load->view('admin/includes/_footer');        
    }

    public function print_job_log($print_job_id, $log_data) {
        $newPrintJobLog = array(
            'print_job_id' => $print_job_id,
            'log_data' => json_encode($log_data),
            'log_timestamp' => date('Y-m-d H:i:s')
        );

        $this->PrintJob_model->insert_print_job_log($newPrintJobLog);
    }

    public function print_job_create($filename, $printerId, $note = '') {
        $newPrintJob = array(
            'filename' => $filename,
            'printer_id' => $printerId,
            'start_time' => date('Y-m-d H:i:s')
        );

        if ($note != '') {
            $newPrintJob['notes'] = $note;
        }

        $this->PrintJob_model->insert_print_job($newPrintJob);
        $newPrintJobId = $this->db->insert_id();
        return $newPrintJobId;
    }

    public function get_state($printerId) {
        $printer = $this->Printer_model->get_printer_by_id($printerId);

        if (!$printer) {
            die('Printer not found');
        }

        if ($printer->state === null) {
            $state = "Undefined";
        }

        $currentTime = time();
        $lastCheckTime = strtotime($printer->last_check);

        if (($currentTime - $lastCheckTime) > 20) {
            $state = "Connection Lost";
            $this->Printer_model->update_state($printerId, $state);
        } else {
            $state = $printer->state;
        }

        header('Content-Type: application/json');
        echo json_encode(array('state' => $state));
    }

    public function webhook() {

        if($json = json_decode(file_get_contents("php://input"),true)) {
            $data = $json;
        } else {
            $data = $_POST;
        }

        if (empty($data)) { die('no input data'); }

        $octoprintData = $data;
        $printer = $octoprintData['deviceIdentifier'];
        $printerId = $this->PrintJob_model->get_printer_id_by_identifier($printer);

        $this->Printer_model->update_state($printerId, $octoprintData['topic']);

        $octoprintDataExtra = json_decode($octoprintData['extra'], true);
        $filename = $octoprintDataExtra['name'];
        var_dump($octoprintData);

        if ($octoprintData && isset($octoprintData['topic']) && in_array($octoprintData['topic'], array("Print Started", "Starting"))) {

            if (is_null($printerId)) { die('uknown printer: '.$printer);}

            $existingJob = $this->PrintJob_model->get_unfinished_print_job($printerId);
            if ($existingJob) {
                $this->PrintJob_model->finishPrintJob($existingJob['id'], date('Y-m-d H:i:s').': Closed by script. Didn\'t finish properly.');
            }

            $newPrintJobId = $this->print_job_create($filename, $printerId);
            $this->print_job_log($newPrintJobId, $octoprintData);

        } elseif (in_array($octoprintData['topic'], array("Print Progress", "Printing"))) {

            $lastPrint = $this->PrintJob_model->get_unfinished_print_job($printerId);

            if (!$lastPrint) {
                $newPrintJobId = $this->print_job_create($filename, $printerId);
                $this->print_job_log($newPrintJobId, $octoprintData);

            } elseif ($lastPrint['filename'] !== $filename) {
                $this->PrintJob_model->finishPrintJob($lastPrint['id'], date('Y-m-d H:i:s') . ': Closed by script. New print started.');
                $newPrintJobId = $this->print_job_create($filename, $printerId);
                $this->print_job_log($newPrintJobId, $octoprintData);

            } else {
                $this->print_job_log($lastPrint['id'], $octoprintData);
            }

        } elseif (in_array($octoprintData['topic'], array("Print Done", "Print Failed", "Error")) ) {
            $lastPrint = $this->PrintJob_model->get_unfinished_print_job($printerId);
            $this->PrintJob_model->update_finish_time($lastPrint['id'], date('Y-m-d H:i:s'));
            $this->print_job_log($lastPrint['id'], $octoprintData);

        } elseif(in_array($octoprintData['topic'], array("Operational"))) {
            $lastPrint = $this->PrintJob_model->get_unfinished_print_job($printerId);

            if ($lastPrint) {
                $this->PrintJob_model->update_finish_time($lastPrint['id'], date('Y-m-d H:i:s'));
                $octoprintData['topic'] = 'Print Done';
                $octoprintDataExtra = json_decode($octoprintData['extra'], true);
                $octoprintDataExtra['progress']['completion'] = '100';
                $octoprintData['extra'] = json_encode($octoprintDataExtra);
                $this->print_job_log($lastPrint['id'], $octoprintData);
            }
        
        } else {
            $lastPrint = $this->PrintJob_model->get_unfinished_print_job($printerId);
            if ($lastPrint) {
                $this->print_job_log($lastPrint['id'], $octoprintData);
            }
        }               

        header('Content-Type: application/json');
        echo json_encode(array("message" => "Data received successfully: ". $octoprintData['topic']));
        die;  
    }

    public function rooms_list() {
        $data['rooms'] = $this->Room_model->get_rooms();
		$this->load->view('admin/includes/_header');
        $this->load->view('admin/printers/rooms_list', $data);
		$this->load->view('admin/includes/_footer');
    }

    public function add_room() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = array(
                'name' => $this->input->post('roomName'),
                'description' => $this->input->post('roomDescription')
            );

            $this->Room_model->create_room($data);
            redirect('admin/printers/rooms_list');

        } else {
            $this->load->view('admin/includes/_header');
            $this->load->view('admin/printers/add_room');
            $this->load->view('admin/includes/_footer');
        }
    }

    public function edit_room($room_id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = array(
                'name' => $this->input->post('roomName'),
                'description' => $this->input->post('roomDescription'),
            );

            $this->Room_model->update_room($room_id, $data);
            redirect('admin/printers/rooms_list');

        } else {
            $data['room'] = $this->Room_model->get_room_by_id($room_id);
            $this->load->view('admin/includes/_header');
            $this->load->view('admin/printers/edit_room', $data);
            $this->load->view('admin/includes/_footer');
        }
    }

    public function delete_room($room_id) {
        $this->Room_model->delete_room($room_id);
        redirect('admin/printers/rooms_list');
    }

    public function job_add_card($job_id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$this->PrintJob_model->job_add_card($job_id, $this->input->post('cardId'));
			redirect('admin/printers/print_jobs_list/');

		} else {
            $data['job_id'] = $job_id;
            $this->load->view('admin/includes/_header');
            $this->load->view('admin/printers/job_add_card', $data);
            $this->load->view('admin/includes/_footer');
        }
    }
    
}
?>
