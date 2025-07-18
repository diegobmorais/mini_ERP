<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Coupon extends CI_Controller {

    public function index() {
        $this->load->model('Coupon_model');
        $data['coupons'] = $this->Coupon_model->get_all();
        $this->load->view('coupons/coupon_form', $data);
    }

    public function save() {
        $this->load->model('Coupon_model');
        $data = [
            'code' => strtoupper($this->input->post('code')),
            'amount' => $this->input->post('amount'),
            'min_subtotal' => $this->input->post('min_subtotal') ?? 0.00,
            'expires_at' => $this->input->post('expires_at'),
            'is_active' => 1
        ];
        $this->Coupon_model->insert($data);
        redirect('coupon');
    }

    public function delete($id) {
        $this->load->model('Coupon_model');
        $this->Coupon_model->delete($id);
        redirect('coupon');
    }
}
