<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Cart extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Coupon_model');
        $this->load->model('Stock_model');
        $this->load->library('EmailService');
        $this->load->helper('shipping');
        $this->load->helper('cart');
    }
    // Aplica o cupom de desconto
    public function apply_coupon()
    {
        $code = strtoupper(trim($this->input->post('coupon_code')));
        $cart = $this->session->userdata('cart') ?? [];
        $subtotal = calculate_subtotal($cart);        
        $coupon = $this->Coupon_model->get_valid($code, $subtotal);
        // var_dump($coupon).die;
        if ($coupon) {
            $this->session->set_userdata('coupon', $coupon);
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => true, 'discount' => $coupon['amount']]));
        } else {
            $this->session->unset_userdata('coupon');
            echo json_encode(['success' => false, 'message' => 'Cupom invalido ou expirado']);
        }
    } 
    public function get_cart_totals()
    {
        $cart = $this->session->userdata('cart') ?? [];
        $coupon = $this->session->userdata('coupon') ?? null;

        $subtotal = calculate_subtotal($cart);
        $discount = $coupon ? (float) $coupon['amount'] : 0;
        $shipping = calculate_shipping_fee($subtotal - $discount);
        $total = $subtotal + $shipping - $discount;

        echo json_encode([
            'cart' => $cart,
            'subtotal' => number_format($subtotal, 2, ',', '.'),
            'discount' => number_format($discount, 2, ',', '.'),
            'shipping' => number_format($shipping, 2, ',', '.'),
            'total' => number_format($total, 2, ',', '.')
        ]);
    }
    public function finalize_order()
    {
        $post = $this->input->post();
        $cart = $this->session->userdata('cart') ?? [];
        $coupon = $this->session->userdata('coupon') ?? null;

        if (empty($cart)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Carrinho vazio.'
                ]));
        }

        // Calcula os valores
        $subtotal = calculate_subtotal($cart);
        $discount = $coupon['amount'] ?? 0;
        $shipping_fee = calculate_shipping_fee($subtotal);
        $total = $subtotal - $discount + $shipping_fee;
        $order_number = strtoupper(uniqid('ORD'));
        // var_dump($post).die;
        $order_data = [
            'order_number'        => $order_number,
            'customer_name'       => $post['customer_name'],
            'customer_email'      => $post['customer_email'],
            'customer_phone'      => $post['customer_phone'],
            'postal_code'         => $post['postal_code'],
            'address'             => $post['address'],
            'address_number'      => $post['address_number'],
            'address_complement'  => $post['address_complement'],
            'neighborhood'        => $post['neighborhood'],
            'city'                => $post['city'],
            'state'               => $post['state'],
            'subtotal'            => $subtotal,
            'discount'            => $discount,
            'shipping_fee'        => $shipping_fee,
            'total'               => $total,
            'coupon_code'         => $coupon['code'] ?? null,
        ];

        $this->load->model('Order_model');
        $order_id = $this->Order_model->create_order($order_data);

        //atualiza estoque
        foreach ($cart as $item) {
            $stock_id = $item['stock_id'] ?? null;
            $quantity = $item['quantity'] ?? 0;

            if ($stock_id && $quantity > 0) {
                $success = $this->Stock_model->decrease_stock($stock_id, $quantity);
                if (!$success) {
                    return $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode([
                            'success' => false,
                            'message' => "Estoque insuficiente ou variação inválida (ID: {$stock_id})."
                        ]));
                }
            }
        }
        if (!$order_id) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Erro ao salvar o pedido no banco de dados.'
                ]));
        }
        // Envia o e-mail de confirmação
        $this->emailservice->send_order_email($order_data, $cart);

        // Limpa carrinho e cupom
        $this->session->unset_userdata(['cart', 'coupon']);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'order_number' => $order_number
            ]));
    }
}
