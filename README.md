# mini_ERP

Sistema simples de ERP desenvolvido em CodeIgniter 3, com frontend em JavaScript e Bootstrap. Para Teste inicial da Montink

---

## Visão Geral

Esta aplicação permite o gerenciamento básico de produtos, variações e estoque, além de um carrinho de compras que controla estoque, valores e regras de frete. Possui também suporte a cupons de desconto, verificação de CEP via API ViaCEP, envio de e-mail na finalização do pedido, e um webhook para atualização ou remoção de pedidos baseado em status.

---

## Funcionalidades

- **Gestão de Produtos e Estoque:**
  - Cadastro de produtos com nome, preço e variações.
  - Controle de estoque para cada variação do produto.
  - Atualização de produtos e seus estoques.

- **Carrinho de Compras:**
  - Adição de produtos ao carrinho com controle de quantidade baseado no estoque disponível.
  - Cálculo automático do subtotal, frete e total.
  - Regras de frete:
    - Subtotal entre R$52,00 e R$166,59: frete de R$15,00.
    - Subtotal maior que R$200,00: frete grátis.
    - Outros valores: frete de R$20,00.

- **Cupons de Desconto:**
  - Criação e aplicação de cupons com validade e regras baseadas no subtotal do carrinho.
  - Atualização dos valores de desconto e totais ao aplicar cupom.

- **Verificação de CEP:**
  - Integração com API ViaCEP para preenchimento automático do endereço baseado no CEP.

- **Finalização de Pedido:**
  - Validação e persistência do pedido no banco.
  - Atualização do estoque automaticamente.
  - Envio de e-mail de confirmação ao cliente.
  - Limpeza da sessão do carrinho e cupom.

- **Webhook:**
  - Recebe atualizações de status de pedido.
  - Remove pedido em caso de cancelamento.
  - Atualiza status para outros casos.

---

## Tecnologias Utilizadas

- PHP 8.1 com CodeIgniter 3 (MVC)
- JavaScript (Fetch API)
- Bootstrap 5
- MySQL
- API ViaCEP para consulta de CEP
- EmailService para envio de e-mails via SMTP ou outra configuração adequada

---

## Estrutura do Banco de Dados

O sistema utiliza quatro tabelas principais: (Dump do bd no repositorio)

- `products`: informações dos produtos.
- `stock`: variações e quantidades em estoque.
- `order`: dados dos pedidos realizados.
- `coupon`: cupons de desconto com regras e validade.

---

## Instruções para Configuração

1. Clone o repositório:

   ```bash
   git clone https://github.com/diegobmorais/mini_ERP.git
  

