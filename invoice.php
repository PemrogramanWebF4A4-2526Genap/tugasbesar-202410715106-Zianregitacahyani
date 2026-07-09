<?php
session_start();
include 'koneksi.php';

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

// Ambil data pesanan
$stmt = $mysqli->prepare("
    SELECT
        o.*,
        u.name AS buyer_name
    FROM orders o
    INNER JOIN users u ON o.buyer_id = u.id
    WHERE o.id = ?
    LIMIT 1
");

$stmt->bind_param("i", $order_id);
$stmt->execute();

$order = $stmt->get_result()->fetch_assoc();

$stmt->close();

// Ambil semua produk dalam pesanan
$stmtItem = $mysqli->prepare("
    SELECT
        oi.quantity,
        oi.price,
        p.name,
        p.image
    FROM order_items oi
    INNER JOIN products p
        ON oi.product_id = p.id
    WHERE oi.order_id = ?
");

$stmtItem->bind_param("i", $order_id);
$stmtItem->execute();

$resultItem = $stmtItem->get_result();

$items = [];

while ($row = $resultItem->fetch_assoc()) {
    $items[] = $row;
}

$stmtItem->close();

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Invoice #<?= $order['id']; ?></title>

    <link href="bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f5f7fb;
            padding: 40px;
        }

        .invoice {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 35px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .08);
        }

        table td,
        table th {
            vertical-align: middle;
        }

        @media print {

            body {
                background: white;
                padding: 0;
            }

            .invoice {
                box-shadow: none;
                border: none;
            }

            .btn {
                display: none;
            }

        }
    </style>

</head>

<body>

    <div class="invoice">

        <h2 class="mb-4">
            Invoice #<?= $order['id']; ?>
        </h2>

        <div class="row mb-4">

            <div class="col-md-6">
                <h5>Data Pembeli</h5>

                <p class="mb-1">
                    <strong>Nama :</strong>
                    <?= htmlspecialchars($order['buyer_name']); ?>
                </p>

                <p class="mb-1">
                    <strong>Alamat :</strong>
                    <?= nl2br(htmlspecialchars($order['shipping_address'])); ?>
                </p>

            </div>

            <div class="col-md-6 text-md-end">

                <p class="mb-1">
                    <strong>Tanggal :</strong>
                    <?= date('d M Y H:i', strtotime($order['created_at'])); ?>
                </p>

                <p class="mb-1">
                    <strong>Status :</strong>
                    <?= ucfirst($order['status']); ?>
                </p>

                <p class="mb-1">
                    <strong>Pembayaran :</strong>
                    <?= htmlspecialchars($order['payment_method']); ?>
                </p>

            </div>

        </div>
        <hr>

        <table class="table table-bordered">

            <thead class="table-primary">
                <tr>
                    <th>Produk</th>
                    <th width="120">Qty</th>
                    <th width="170">Harga</th>
                    <th width="170">Subtotal</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($items as $item): ?>

                    <tr>

                        <td><?= htmlspecialchars($item['name']); ?></td>

                        <td><?= $item['quantity']; ?></td>

                        <td>
                            Rp <?= number_format($item['price'], 0, ',', '.'); ?>
                        </td>

                        <td>
                            Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>
        <div class="row justify-content-end mt-4">

            <div class="col-md-5">

                <table class="table">

                    <?php
                    $subtotal = 0;
                    foreach ($items as $item) {
                        $subtotal += $item['price'] * $item['quantity'];
                    }
                    ?>

                    <tr>
                        <th>Subtotal</th>
                        <td class="text-end">
                            Rp <?= number_format($subtotal, 0, ',', '.'); ?>
                        </td>
                    </tr>

                    <tr>
                        <th>Ongkir</th>
                        <td class="text-end">
                            Rp <?= number_format($order['shipping_cost'], 0, ',', '.'); ?>
                        </td>
                    </tr>

                    <tr class="table-primary">
                        <th>Total Belanja</th>
                        <th class="text-end">
                            Rp <?= number_format($order['total_amount'], 0, ',', '.'); ?>
                        </th>
                    </tr>

                </table>

            </div>

        </div>

        <hr class="my-4">

        <div class="d-flex justify-content-between">

            <a href="pesanan-saya.php?detail=<?= $order['id']; ?>" class="btn btn-secondary">
                ← Kembali
            </a>

            <button onclick="window.print()" class="btn btn-primary">
                🖨 Print Invoice
            </button>

        </div>