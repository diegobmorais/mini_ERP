<?php
class Product_model extends CI_Model
{
    public function insert($name, $price)
    {
        $this->db->insert('products', [
            'name' => $name,
            'price' => $price
        ]);
        return $this->db->insert_id();
    }
    public function update($id, $name, $price)
    {
        $this->db->where('id', $id)->update('products', [
            'name' => $name,
            'price' => $price
        ]);
    }
    public function delete_stock($product_id)
    {
        $this->db->where('product_id', $product_id)->delete('stock');
    }
    public function insert_stock($product_id, $variation, $quantity)
    {
        $this->db->insert('stock', [
            'product_id' => $product_id,
            'variation' => $variation,
            'quantity' => $quantity
        ]);
    }
    public function get_all()
    {
        $this->db->select('p.*, COALESCE(SUM(s.quantity), 0) AS total_quantity');
        $this->db->from('products p');
        $this->db->join('stock s', 's.product_id = p.id', 'left');
        $this->db->group_by('p.id');
        return $this->db->get()->result();
    }
    public function get_by_id($id)
    {
        return $this->db
            ->select('products.*, stock.variation, stock.quantity')
            ->from('products')
            ->join('stock', 'stock.product_id = products.id', 'left')
            ->where('products.id', $id)
            ->get()
            ->row();
    }
    public function get_stock_by_product($product_id)
    {
        return $this->db->where('product_id', $product_id)->get('stock')->result();
    }
    public function decrease_stock($product_id, $quantity)
    {
        $this->db->set('quantity', 'quantity - ' . (int)$quantity, false);
        $this->db->where('id', $product_id);
        $this->db->update('stock');
    }
}
