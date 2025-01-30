<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PrintJob_model extends CI_Model {

    private $table = 'print_jobs';

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_all_print_jobs() {
        $this->db->select('print_jobs.*, printers.name as printer_name');
        $this->db->from('print_jobs');
        $this->db->join('printers', 'printers.id = print_jobs.printer_id');
        $this->db->order_by('start_time', 'desc');
        $query = $this->db->get();
        return $query->result_array();        
    }

    public function get_print_job_by_id($id) {
        return $this->db->get_where($this->table, array('id' => $id))->row_array();
    }

    public function insert_print_job($data) {
        return $this->db->insert($this->table, $data);
    }

    public function update_print_job($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    public function delete_print_job($id) {
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }
    
    public function get_unfinished_print_job($printerId) {
        $this->db->where('printer_id', $printerId);
        $this->db->order_by('start_time', 'desc');
        $this->db->limit(1);
        $query = $this->db->get($this->table);

        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            if ($result['finish_time'] === null) {
                return $result;
            }
        }

        return null; // No unfinished print job found
    }

    public function get_printer_id_by_identifier($printerIdentifier) {
        $query = $this->db->select('id')
            ->from('printers')
            ->where('name', $printerIdentifier)
            ->get();
    
        if ($query->num_rows() > 0) {
            return $query->row()->id;
        } else {
            return null; // Printer not found
        }
    }

    public function insert_print_job_log($data) {
        return $this->db->insert('print_job_logs', $data);
    }    


    public function update_finish_time($printJobId, $finishTime) {
        $this->db->where('id', $printJobId);
        $this->db->update('print_jobs', array('finish_time' => $finishTime));

        return $this->db->affected_rows() > 0;
    }    

    public function get_print_jobs_by_printer_id($printerId) {
        $this->db->where('printer_id', $printerId);
        $this->db->order_by('start_time', 'desc');
        $query = $this->db->get('print_jobs');

        return $query->result_array();
    }

    public function get_print_logs_by_job_id($job_id = 0)
    {
        $this->db->select('print_job_logs.*, print_jobs.id as printjob_id, print_jobs.filename as printjob_filename, printers.name as printer_name');
        $this->db->from('print_job_logs');
        $this->db->join('print_jobs', 'print_jobs.id = print_job_logs.print_job_id');
        $this->db->join('printers', 'printers.id = print_jobs.printer_id');
        if ($job_id != 0) { $this->db->where('print_job_id', $job_id); }
        $this->db->order_by('print_job_logs.log_timestamp', 'desc');
        $query = $this->db->get();
    
        return $query->result_array();
    }

    public function finishPrintJob($print_job_id, $note = '') {
        // Get the last print job log for the specified print_job_id
        $this->db->select('log_timestamp');
        $this->db->from('print_job_logs');
        $this->db->where('print_job_id', $print_job_id);
        $this->db->order_by('log_timestamp', 'desc');
        $this->db->limit(1);
        $query = $this->db->get();
    
        if ($query->num_rows() > 0) {
            $lastLog = $query->row();
            $finishTime = $lastLog->log_timestamp;
            $data = array('finish_time' => $finishTime, 'notes' => $note);
            $this->db->where('id', $print_job_id);
            $this->db->update('print_jobs', $data);
    
            return true;
        } else {
            return false; 
        }
    }   

    public function job_add_card($printJobId, $card_id) {
        $this->db->where('id', $printJobId);
        $this->db->update('print_jobs', array('user_name' => $card_id));
    }

}
