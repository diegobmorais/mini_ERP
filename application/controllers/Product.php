<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Product extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Product_model');
        $this->load->model('Stock_model');
        $this->load->library('session');
        $this->load->helper('shipping');
        $this->load->helper('cart');
    }
    public function index()
    {
        $data['products'] = $this->Product_model->get_all();
        foreach ($data['products'] as &$product) {
            $product->variations = $this->Stock_model->get_stock_by_product_id($product->id);
        }
        $product_id = $this->input->get('id');

        if ($product_id) {
            $data['edit_product'] = $this->Product_model->get_by_id($product_id);
            $data['edit_stock'] = $this->Product_model->get_stock_by_product($product_id);
        }
        $data['cart'] = $this->session->userdata('cart') ?? [];
        $subtotal = $subtotal = calculate_subtotal($data['cart']);
        $shipping = calculate_shipping_fee($subtotal);
        $coupon = $this->session->userdata('coupon');
        $discount = $coupon['amount'] ?? 0;

        $data['cart_summary'] = [
            'subtotal' => number_format($subtotal, 2, ',', '.'),
            'shipping' => number_format($shipping, 2, ',', '.'),
            'discount' => number_format($discount, 2, ',', '.'),
            'total' => number_format($subtotal + $shipping, 2, ',', '.'),
        ];
        $this->load->view('products/product_form', $data);
    }

    public function save()
    {
        $product_id = $this->input->post('product_id');
        $name = $this->input->post('name');
        $price = $this->input->post('price');
        $variations = $this->input->post('variation');
        $quantities = $this->input->post('quantity');

        if ($product_id) {
            $this->Product_model->update($product_id, $name, $price);
            $this->Product_model->delete_stock($product_id);
        } else {
            $product_id = $this->Product_model->insert($name, $price);
        }
        for ($i = 0; $i < count($variations); $i++) {
            $this->Product_model->insert_stock($product_id, $variations[$i], $quantities[$i]);
        }
        redirect('product');
    }
    public function buy()
    {
        $product_id = $this->input->post('product_id');
        $stock_id   = $this->input->post('stock_id');

        if (!$product_id || !$stock_id) {
            echo json_encode(['status' => 'error', 'message' => 'Produto ou variação inválidos.']);
            return;
        }
        $product = $this->Product_model->get_by_id($product_id);
        $stock   = $this->Stock_model->get_stock_by_id($stock_id);

        if (!$product || !$stock) {
            echo json_encode(['status' => 'error', 'message' => 'Produto ou variação não encontrados.']);
            return;
        }
        $cart = $this->session->userdata('cart') ?? [];
        $found = false;
        $totalQty = 0;
    
        foreach ($cart as &$item) {
            if ($item['stock_id'] == $stock_id) {
                if ($item['quantity'] + 1 > $stock->quantity) {
                    echo json_encode(['status' => 'error', 'message' => 'Estoque insuficiente.']);
                    return;
                }

                $item['quantity'] += 1;
                $found = true;
                break;
            }
            $totalQty += $item['quantity'];
        }
        unset($item); 
    
        if (!$found) {
            if (1 > $stock->quantity) {
                echo json_encode(['status' => 'error', 'message' => 'Estoque insuficiente.']);
                return;
            }

            $cart[] = [
                'product_id' => $product->id,
                'stock_id'   => $stock_id,
                'name'       => $product->name . ' - ' . $stock->variation,
                'unit_price' => $product->price,
                'quantity'   => 1,
            ];
        }
        $this->session->set_userdata('cart', $cart);
   
        $subtotal = array_reduce($cart, function ($carry, $item) {
            $unit_price = isset($item['unit_price']) ? (float)$item['unit_price'] : 0;
            $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 0;
            return $carry + ($unit_price * $quantity);
        }, 0);

        $shipping = calculate_shipping_fee($subtotal);
        $total = $subtotal + $shipping;

        echo json_encode([
            'status' => 'ok',
            'cart' => $cart,
            'subtotal' => number_format($subtotal, 2, ',', '.'),
            'shipping' => number_format($shipping, 2, ',', '.'),
            'total' => number_format($total, 2, ',', '.')
        ]);
    }
    public function remove_cart_item()
    {
        $index = $this->input->post('index');
        $cart = $this->session->userdata('cart') ?? [];

        if (!isset($cart[$index])) {
            echo json_encode(['status' => 'error', 'message' => 'Item não encontrado no carrinho']);
            return;
        }
        if ($cart[$index]['quantity'] > 1) {
            $cart[$index]['quantity']--;
        } else {
            array_splice($cart, $index, 1);
        }
        $this->session->set_userdata('cart', $cart);

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['unit_price'] * $item['quantity'];
        }
        $shipping = calculate_shipping_fee($subtotal);
        $total = $subtotal + $shipping;

        if (empty($cart)) {
            $this->session->unset_userdata('coupon');
        }
        echo json_encode([
            'status' => 'ok',
            'cart' => $cart,
            'subtotal' => number_format($subtotal, 2, ',', '.'),
            'shipping' => number_format($shipping, 2, ',', '.'),
            'total' => number_format($total, 2, ',', '.')
        ]);
    }
}
