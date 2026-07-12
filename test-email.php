<?php

require 'mail/kirim-email.php';

$hasil = kirimEmail(
    'zianregita996@gmail.com',
    'Zian',
    'Test Email AquaGas',
    '<h2>Halo Zian 👋</h2><p>Email dari AquaGas berhasil dikirim.</p>'
);

if ($hasil) {
    echo "Email berhasil dikirim.";
} else {
    echo "Email gagal dikirim.";
}
