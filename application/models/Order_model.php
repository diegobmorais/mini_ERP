<?php

class Order_model extends CI_Model
{
    public function create_order($data)
    {
        $this->db->insert('orders', $data);
        return $this->db->insert_id();
    }
    public function delete_order($order_id)
    {
        $this->db->where('id', $order_id);
        return $this->db->delete('orders');
    }

    public function update_order_status($order_id, $status)
    {
        $this->db->where('id', $order_id);
        return $this->db->update('orders', ['status' => $status]);
    }
}
