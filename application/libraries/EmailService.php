<?php
defined('BASEPATH') or exit('No direct script access allowed');

class EmailService
{

    protected $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->library('email');
        $this->CI->load->config('email');
    }

    public function send_order_email($order, $items)
    {   
        $this->CI->email->from($this->CI->config->item('smtp_user'), 'Mini ERP');

        $this->CI->email->to($order['customer_email']);

        $this->CI->email->subject('Confirmação do Pedido #' . $order['order_number']);

        $message = "<h2>Obrigado por sua compra, {$order['customer_name']}!</h2>";
        $message .= "<p>Resumo do seu pedido:</p>";
        $message .= "<ul>";

        foreach ($items as $item) {
            $price = number_format($item['unit_price'], 2, ',', '.');
            $message .= "<li>{$item['name']} - R$ {$price}</li>";
        }

        $total = number_format($order['total'], 2, ',', '.');
        $message .= "</ul>";
        $message .= "<p><strong>Total: R$ {$total}</strong></p>";
        $message .= "<p>Seu pedido será processado em breve.</p>";

        $this->CI->email->message($message);

        try {
            if (!$this->CI->email->send()) {
                $error = $this->CI->email->print_debugger(['headers']);
                log_message('error', 'Erro ao enviar email: ' . $error);
                throw new Exception('Falha no envio do email: ' . $error);
            }
        } catch (Exception $ex) {
            log_message('error', 'Exception no envio do email: ' . $ex->getMessage());
            throw $ex;
        }
    }
}
