<?php
class Room_model extends CI_Model {
    public function create_room($data) {
        $this->db->insert('printers_rooms', $data);
        return $this->db->insert_id();
    }

    public function get_rooms() {
        return $this->db->get('printers_rooms')->result();
    }

    public function get_room_by_id($room_id) {
        return $this->db->get_where('printers_rooms', array('id' => $room_id))->row();
    }

    public function update_room($room_id, $data) {
        $this->db->where('id', $room_id);
        $this->db->update('printers_rooms', $data);
    }

    public function delete_room($room_id) {
        $this->db->where('id', $room_id);
        $this->db->delete('printers_rooms');
    }
}
