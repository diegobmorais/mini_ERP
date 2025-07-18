<?php

class Stock_model extends CI_Model
{
    public function decrease_stock($stock_id, $quantity)
    {
        $this->db->where('id', $stock_id);
        $this->db->where('quantity >=', $quantity);
        $this->db->set('quantity', 'quantity - ' . (int)$quantity, false);
        $this->db->update('stock');

        return $this->db->affected_rows() > 0;
    }
    public function get_stock_by_product_id($product_id)
    {
        return $this->db
            ->where('product_id', $product_id)
            ->get('stock')
            ->result();
    }
    public function get_stock_by_id($stock_id)
    {
        return $this->db->where('id', $stock_id)->get('stock')->row();
    }
}
