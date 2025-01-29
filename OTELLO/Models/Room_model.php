<?php
class Room_model extends CI_Model {
/*    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
*/
    // Create a new 3D printer record
    public function create_room($data) {
        $this->db->insert('printers_rooms', $data);
        return $this->db->insert_id();
    }

    // Get a list of all 3D printers
    public function get_rooms() {
        return $this->db->get('printers_rooms')->result();
    }

    // Get details of a specific 3D printer by ID
    public function get_room_by_id($room_id) {
        return $this->db->get_where('printers_rooms', array('id' => $room_id))->row();
    }

    // Update the details of a 3D printer
    public function update_room($room_id, $data) {
        $this->db->where('id', $room_id);
        $this->db->update('printers_rooms', $data);
    }

    // Delete a 3D printer by ID
    public function delete_room($room_id) {
        $this->db->where('id', $room_id);
        $this->db->delete('printers_rooms');
    }
}