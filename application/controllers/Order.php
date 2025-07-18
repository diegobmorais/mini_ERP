<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Order extends CI_Controller
{
    public function webhook_update_status()
    {
        // Recebe dados JSON do webhook (POST)
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['order_id']) || !isset($input['status'])) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode(['success' => false, 'message' => 'Dados inválidos']));
        }

        $order_id = $input['order_id'];
        $status = strtolower($input['status']);

        $this->load->model('Order_model');

        if ($status === 'canceled') {
            // Remove o pedido
            $deleted = $this->Order_model->delete_order($order_id);
            if ($deleted) {
                $response = ['success' => true, 'message' => "Pedido $order_id removido"];
            } else {
                $response = ['success' => false, 'message' => "Falha ao remover pedido $order_id"];
            }
        } else {
            // Atualiza o status do pedido
            $updated = $this->Order_model->update_order_status($order_id, $status);
            if ($updated) {
                $response = ['success' => true, 'message' => "Status do pedido $order_id atualizado para $status"];
            } else {
                $response = ['success' => false, 'message' => "Falha ao atualizar status do pedido $order_id"];
            }
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
}
