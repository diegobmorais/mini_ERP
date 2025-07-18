<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Gerenciamento de Cupom</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .coupon-form input {
            height: 45px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Gestão de Cupons</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?= site_url('coupon/save') ?>" class="row g-3 coupon-form">
                            <div class="col-md-3">
                                <label class="form-label">Coupon Code</label>
                                <input type="text" name="code" class="form-control" placeholder="e.e., PROMO10" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Desconto (R$)</label>
                                <input type="number" step="0.01" name="amount" class="form-control" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Min Subtotal (R$)</label>
                                <input type="number" step="0.01" name="min_subtotal" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Validade</label>
                                <input type="date" name="expires_at" class="form-control" required>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button class="btn btn-success w-100">Salvar Cupon</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0">Cupons Existentes</h6>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($coupons)): ?>
                            <div class="p-3 text-muted">Nenhum cupom encontrado.</div>
                        <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($coupons as $coupon): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?= $coupon['code'] ?></strong>
                                            <span class="text-muted"> - R$<?= number_format($coupon['amount'], 2, ',', '.') ?>
                                                <?php if (!empty($coupon['min_subtotal'])): ?>
                                                    (Min: R$<?= number_format($coupon['min_subtotal'], 2, ',', '.') ?>)
                                                <?php endif; ?>
                                                - Expira em: <?= date('d/m/Y', strtotime($coupon['expires_at'])) ?></span>
                                        </div>
                                        <form method="POST" action="<?= site_url('coupon/delete/' . $coupon['id']) ?>">
                                            <button class="btn btn-sm btn-outline-danger">Excluir</button>
                                        </form>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</body>

</html>