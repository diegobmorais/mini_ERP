<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Gestão de Produtos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="text-center py-3">
            <h3>Gestão de Produtos</h3>
            <h5>Mini ERP</h5>
        </div>
        <div class="row">
            <!-- cadastro de produtos -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h4><?= isset($edit_product) ? 'Editar Produto' : 'Novo Produto' ?></h4>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?= base_url('product/save') ?>">
                            <?php if (isset($edit_product)): ?>
                                <input type="hidden" name="product_id" value="<?= $edit_product->id ?>">
                            <?php endif; ?>
                            <div class="mb-3">
                                <label>Nome do Produto</label>
                                <input type="text" name="name" class="form-control" required
                                    value="<?= isset($edit_product) ? $edit_product->name : '' ?>">
                            </div>
                            <div class="mb-3">
                                <label>Preço</label>
                                <input type="number" name="price" class="form-control" step="0.01" required
                                    value="<?= isset($edit_product) ? $edit_product->price : '' ?>">
                            </div>
                            <div id="variations-container">
                                <?php if (isset($edit_stock)): ?>
                                    <?php foreach ($edit_stock as $stock): ?>
                                        <div class="row mb-2">
                                            <div class="col">
                                                <input type="text" name="variation[]" class="form-control" placeholder="Variação (Cor - Tam)"
                                                    value="<?= $stock->variation ?>">
                                            </div>
                                            <div class="col">
                                                <input type="number" name="quantity[]" class="form-control" placeholder="Qnt"
                                                    value="<?= $stock->quantity ?>" min="0">
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="row mb-2">
                                        <div class="col">
                                            <input type="text" name="variation[]" class="form-control" placeholder="Variação (Cor - Tam)">
                                        </div>
                                        <div class="col">
                                            <input type="number" name="quantity[]" class="form-control" placeholder="Qnt" min="0">
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="btn btn-sm btn-secondary mb-3" onclick="addVariation()">+ Add Variação</button>
                            <br>
                            <div class="d-flex gap-2 mt-2">
                                <button type="submit" class="btn btn-primary w-100 mt-2">
                                    <?= isset($edit_product) ? 'Atualizar Produto' : 'Salvar' ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card mt-4 p-2">
                    <h5>Verificar CEP</h5>
                    <div class="input-group mb-3">
                        <input type="text" id="postal_code" class="form-control" placeholder="CEP (e.x. 30110-000)">
                        <button class="btn btn-outline-primary" type="button" onclick="checkCEP()">Validar CEP</button>
                    </div>
                    <div id="address-result" class="text-muted"></div>
                </div>
            </div>
            <!-- lista de produtos -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Produtos Registrados</h4>
                        <div class="d-flex gap-2">
                            <a href="<?= base_url('product') ?>" class="btn btn-sm btn-outline-primary">+ Novo Produto</a>
                            <a href="<?= site_url('coupon') ?>" class="btn btn-warning btn-sm">Gerenciar Cupons</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            <?php foreach ($products as $product): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                    <div style="flex: 1 1 60%;">
                                        <a href="<?= base_url('product?id=' . $product->id) ?>">
                                            <?= $product->name ?>
                                        </a>
                                        <br>
                                        <small class="text-muted">Em estoque: <?= $product->total_quantity ?></small>
                                        <div class="mt-2">
                                            <select class="form-control form-control-sm" id="variation-<?= $product->id ?>">
                                                <option selected disabled>Selecione a variação</option>
                                                <?php if (!empty($product->variations)): ?>
                                                    <?php foreach ($product->variations as $var): ?>
                                                        <option value="<?= $var->id ?>">
                                                            <?= $var->variation ?> (<?= $var->quantity ?> disponíveis)
                                                        </option>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <option disabled selected>Sem variações</option>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="text-end" style="flex: 1 1 35%;">
                                        <span class="d-block">R$ <?= number_format($product->price, 2, ',', '.') ?></span>

                                        <button type="button"
                                            class="btn btn-success btn-sm mt-2"
                                            onclick="buyProduct(<?= $product->id ?>, document.getElementById('variation-<?= $product->id ?>').value)">
                                            Comprar
                                        </button>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                            <?php if (empty($products)): ?>
                                <li class="list-group-item text-muted">Nenhum produto registrado no momento.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
                <!-- Carrinho de Compra -->
                <div class="card mt-4" id="cart-summary">
                    <div class="card-header bg-dark text-white">
                        Seu Carrinho
                    </div>
                    <div class="card-body" id="cart-items">
                        <?php if (!empty($cart)): ?>
                            <?php foreach ($cart as $index => $item): ?>
                                <div class="cart-item-box d-flex justify-content-between align-items-center mb-2 p-2">
                                    <div>
                                        <strong><?= $item['name'] ?></strong>
                                        <br>
                                        <small>
                                            <?= $item['quantity'] ?>x R$ <?= isset($item['unit_price']) && $item['unit_price'] !== null ? number_format($item['unit_price'], 2, ',', '.') : '0,00' ?>
                                        </small>
                                        <br>
                                        <strong>Total: R$ <?= number_format($item['unit_price'] * $item['quantity'], 2, ',', '.') ?></strong>
                                    </div>
                                    <button class="btn btn-sm btn-danger" onclick="removeCartItem(<?= $index ?>)">Excluir</button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">Não há produtos no carrinho de compras.</p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer">
                        <strong>Subtotal:</strong> R$ <span id="cart-subtotal"><?= $cart_summary['subtotal'] ?? '0,00' ?></span><br>
                        <strong>Frete:</strong> R$ <span id="cart-shipping"><?= $cart_summary['shipping'] ?? '0,00' ?></span><br>
                        <strong>Desconto:</strong> R$ <span id="cart-discount"><?= $cart_summary['discount'] ?? '0,00' ?></span><br>
                        <strong>Total:</strong> R$ <span id="cart-total"><?= $cart_summary['total'] ?? '0,00' ?></span>
                    </div>
                    <button class="btn btn-success mt-3" data-bs-toggle="modal" data-bs-target="#checkoutModal">
                        Finalizar Compra
                    </button>
                    <!-- Cupom input -->
                    <div class="card mt-3">
                        <div class="card-header">Aplicar Cupom</div>
                        <div class="card-body">
                            <div class="input-group">
                                <input type="text" id="coupon_code" class="form-control" placeholder="Insira seu cupom de desconto">
                                <button class="btn btn-outline-primary" onclick="applyCoupon()">Aplicar</button>
                            </div>
                            <div id="couponFeedback" class="mt-2 text-success" style="display: none;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal de Finalização de Compra -->
    <div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="checkoutForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="checkoutModalLabel">Informações do Cliente</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body row g-3">
                        <div class="col-md-6">
                            <label for="customer_name" class="form-label">Nome completo</label>
                            <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="customer_email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="customer_email" name="customer_email" required>
                        </div>
                        <div class="col-md-6">
                            <label for="customer_phone" class="form-label">Telefone</label>
                            <input type="text" class="form-control" id="customer_phone" name="customer_phone">
                        </div>
                        <div class="col-md-6">
                            <label for="postal_code" class="form-label">CEP</label>
                            <input type="text" class="form-control" id="postal_code" name="postal_code" required>
                        </div>
                        <div class="col-md-8">
                            <label for="address" class="form-label">Endereço</label>
                            <input type="text" class="form-control" id="address" name="address" required>
                        </div>
                        <div class="col-md-2">
                            <label for="address_number" class="form-label">Número</label>
                            <input type="text" class="form-control" id="address_number" name="address_number" required>
                        </div>
                        <div class="col-md-2">
                            <label for="address_complement" class="form-label">Complemento</label>
                            <input type="text" class="form-control" id="address_complement" name="address_complement">
                        </div>
                        <div class="col-md-6">
                            <label for="neighborhood" class="form-label">Bairro</label>
                            <input type="text" class="form-control" id="neighborhood" name="neighborhood" required>
                        </div>
                        <div class="col-md-4">
                            <label for="city" class="form-label">Cidade</label>
                            <input type="text" class="form-control" id="city" name="city" required>
                        </div>
                        <div class="col-md-2">
                            <label for="state" class="form-label">UF</label>
                            <input type="text" maxlength="2" class="form-control text-uppercase" id="state" name="state" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Confirmar Pedido</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function addVariation() {
            const container = document.getElementById('variations-container');
            const html = `
                        <div class="row mb-2">
                        <div class="col">
                            <input type="text" name="variation[]" class="form-control" placeholder="Variação (Cor - Tam)">
                        </div>
                        <div class="col">
                            <input type="number" name="quantity[]" class="form-control" placeholder="Qnt" min="0">
                        </div>
                        </div>`;
            container.insertAdjacentHTML('beforeend', html);
        }

        function buyProduct(productId, stockId) {
            if (!stockId || stockId === 'Selecione a variação') {
                alert('Por favor, selecione uma variação antes de comprar.');
                return;
            }
            fetch('<?= base_url('product/buy') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'product_id=' + productId + '&stock_id=' + stockId
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'ok') {
                        renderCart(data);
                    } else {
                        alert('Erro: ' + data.message);
                    }
                });

        }

        function renderCart(data) {
            const container = document.getElementById('cart-items');
            const discount = data.discount || 0;
            container.innerHTML = '';

            if (!data.cart.length) {
                container.innerHTML = '<p class="text-muted">Não há produtos no carrinho de compras.</p>';
            } else {
                data.cart.forEach((item, index) => {
                    const unitPrice = formatCurrency(item.unit_price);
                    const totalPrice = formatCurrency(item.unit_price * item.quantity);

                    const div = document.createElement('div');
                    div.classList.add('cart-item-box', 'd-flex', 'justify-content-between', 'align-items-center', 'mb-2', 'p-2');

                    div.innerHTML = `
                        <div>
                            <strong>${item.name}</strong><br>
                            <small>${item.quantity}x R$ ${unitPrice}</small><br>
                            <strong>Total: R$ ${totalPrice}</strong>
                        </div>
                        <button class="btn btn-sm btn-danger" onclick="removeCartItem(${index})">Excluir</button>
                    `;
                    container.appendChild(div);
                });
            }
            document.getElementById('cart-subtotal').innerText = data.subtotal;
            document.getElementById('cart-shipping').innerText = data.shipping;
            document.getElementById('cart-discount').innerText = formatCurrency(discount);
            document.getElementById('cart-total').innerText = data.total;
        }

        function formatCurrency(value) {
            return parseFloat(value).toFixed(2).replace('.', ',');
        }

        function removeCartItem(index) {
            fetch('<?= base_url('product/remove_cart_item') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'index=' + index
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'ok') {
                        renderCart(data);
                    } else {
                        alert('Error removing item');
                    }
                });
        }

        function checkCEP() {
            const cep = document.getElementById('postal_code').value.replace(/\D/g, '');
            if (cep.length !== 8) {
                document.getElementById('address-result').innerText = 'CEP Invalido';
                return;
            }
            fetch('https://viacep.com.br/ws/' + cep + '/json/')
                .then(res => res.json())
                .then(data => {
                    if (data.erro) {
                        document.getElementById('address-result').innerText = 'CEP não encontrado.';
                    } else {
                        document.getElementById('address-result').innerHTML = `
                        <strong>${data.logradouro}</strong><br>
                         ${data.bairro} - ${data.localidade}/${data.uf}
                        `;
                    }
                })
                .catch(() => {
                    document.getElementById('address-result').innerText = 'Erro ao buscar CEP.';
                });
        }

        function applyCoupon() {
            const code = document.getElementById('coupon_code').value.trim();
            if (!code) return alert('Por favor entre com cupon valido')

            fetch('<?= site_url('cart/apply_coupon') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `coupon_code=${encodeURIComponent(code)}`
                })
                .then(response => response.json())
                .then(data => {
                    const feedback = document.getElementById('couponFeedback');
                    if (data.success) {
                        feedback.classList.remove('text-danger');
                        feedback.classList.add('text-success');
                        feedback.innerText = `Cupom Aplicado: -R$${data.discount}`;
                    } else {
                        feedback.classList.remove('text-success');
                        feedback.classList.add('text-danger');
                        feedback.innerText = data.message;
                    }
                    feedback.style.display = 'block';
                    updateCart();
                })
                .catch(err => {
                    alert('Erro ao aplicar cupom');
                    console.error(err);
                });
        }

        function updateCart() {
            fetch('<?= site_url('cart/get_cart_totals') ?>')
                .then(response => {
                    if (!response.ok) throw new Error('Erro na resposta da requisição');
                    return response.json();
                })
                .then(data => {
                    if (data && data.subtotal && data.total) {
                        document.getElementById('cart-subtotal').innerText = 'R$' + data.subtotal;
                        document.getElementById('cart-shipping').innerText = 'R$' + data.shipping;
                        document.getElementById('cart-discount').innerText = '-R$' + data.discount;
                        document.getElementById('cart-total').innerText = 'R$' + data.total;
                    } else {
                        console.error('Dados incompletos retornados do servidor:', data);
                    }
                })
                .catch(error => {
                    console.error('Erro ao atualizar o carrinho:', error);
                });
        }
    </script>
    <script>
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('<?= site_url('order/checkout') ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Pedido finalizado com sucesso! Um e-mail foi enviado.');
                        location.reload();
                    } else {
                        alert('Erro: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro ao finalizar o pedido:', error);
                    alert('Erro ao finalizar o pedido.');
                });
        });
    </script>
</body>
<style>
    .cart-item-box {
        border: 1px solid #dee2e6;
        border-radius: 6px;
        background-color: #f8f9fa;
    }
</style>

</html>