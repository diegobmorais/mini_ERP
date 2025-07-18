<?php
class Coupon_model extends CI_Model
{

    public function insert($data)
    {
        $this->db->insert('coupons', $data);
    }

    public function get_all()
    {
        return $this->db->get('coupons')->result_array();
    }

    public function get_valid($code, $subtotal)
    {
        $this->db->where('code', strtoupper($code));
        $this->db->where('is_active', 1);
        $this->db->where('DATE(expires_at) >=', date('Y-m-d'));
        $this->db->where('min_subtotal <=', $subtotal);;
        return $this->db->get('coupons')->row_array();
    }

    public function delete($id)
    {
        $this->db->delete('coupons', ['id' => $id]);
    }
}
