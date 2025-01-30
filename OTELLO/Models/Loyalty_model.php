<?php
class Loyalty_model extends CI_Model {
/*    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
*/
    // After reading token on POS reader, upload active user to database
    public function loyaltycard_load($data) {

        $update_data = array('loyaltycard_pos_end_date' => date('Y-m-d H:i:s'));
        $this->db->where('loyaltycard_pos_end_date', null);
        $this->db->update('loyaltycard_pos', $update_data);    
        
    
        $this->db->insert('loyaltycard_pos', $data);
        return $this->db->insert_id();
    }


    public function loyaltycard_member_get($data) {
        return $this->db->get_where('loyaltycard_member', array('loyaltycard_member_cardnumber' => $data['card_id']))->row_array();
    }


    public function loyaltycard_member_add($data) {
        //check for unique card
        $this->db->insert('loyaltycard_member', $data);
        return $this->db->insert_id();
    }

    public function loyaltycard_unload($device_id) {
        $update_data = array('loyaltycard_pos_end_date' => date('Y-m-d H:i:s'));
        $this->db->where('loyaltycard_pos_end_date', null);
        $this->db->update('loyaltycard_pos', $update_data);    
    }


    public function get_active_member ($device_id) {
        $this->db->from('loyaltycard_pos');
        $this->db->join('loyaltycard_member','loyaltycard_pos.loyaltycard_member_id = loyaltycard_member.loyaltycard_member_id');
        $this->db->where('loyaltycard_pos.loyaltycard_pos_end_date', null);

        $query = $this->db->get(); 
        
        if ($query->num_rows() == 0){
            return false;
        }
        else{
            //Compare the password attempt with the password we have stored.
            $result = $query->row_array();
            return $result;
        }
    }

    public function purchase_add($data) {
        $this->db->insert('loyaltycard_purchase', $data);
    }

    public function get_all_purchases() {
        $this->db->select(
            'loyaltycard_pos.*,
             loyaltycard_member.loyaltycard_member_firstname,
             loyaltycard_member.loyaltycard_member_lastname,
             SUM(loyaltycard_purchase.loyaltycard_purchase_price) as total_price'
        );
        $this->db->from('loyaltycard_pos');
        $this->db->join('loyaltycard_member', 'loyaltycard_pos.loyaltycard_pos_cardnumber = loyaltycard_member.loyaltycard_member_cardnumber');
        $this->db->join('loyaltycard_purchase', 'loyaltycard_pos.loyaltycard_pos_id = loyaltycard_purchase.loyaltycard_purchase_pos_id', 'left');
        $this->db->where('loyaltycard_purchase.loyaltycard_purchase_pos_id IS NOT NULL');
        $this->db->group_by('loyaltycard_pos.loyaltycard_pos_id');
        $this->db->order_by('loyaltycard_pos.loyaltycard_pos_id', 'DESC');
        return $this->db->get()->result();
    }

    public function purchase_delete($purchase_id) {
        $this->db->where('loyaltycard_purchase_id', $purchase_id);
        $this->db->delete('loyaltycard_purchase');
    }

    public function purchase_update($purchase_id, $data) {
        $this->db->where('loyaltycard_purchase_id', $purchase_id);
        $this->db->update('loyaltycard_purchase', $data);
    }    

    public function get_purchase_by_id($purchase_id) {
        $this->db->select(
            'loyaltycard_purchase.*,
             loyaltycard_member.loyaltycard_member_firstname,
             loyaltycard_member.loyaltycard_member_lastname'
        );
        $this->db->from('loyaltycard_purchase');
        $this->db->join('loyaltycard_pos', 'loyaltycard_purchase.loyaltycard_purchase_pos_id = loyaltycard_pos.loyaltycard_pos_id');
        $this->db->join('loyaltycard_member', 'loyaltycard_pos.loyaltycard_pos_cardnumber = loyaltycard_member.loyaltycard_member_cardnumber');
        $this->db->where('loyaltycard_purchase.loyaltycard_purchase_id', $purchase_id);
        return $this->db->get()->row();
    }

    public function purchases_detail($purchase_id) {
        $this->db->from('loyaltycard_purchase');
        $this->db->where('loyaltycard_purchase_pos_id', $purchase_id);
        return $this->db->get()->result();
    }


}