<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/env.php';

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Mengirim email notifikasi SIPEKA.
 * Kegagalan email tidak akan menggagalkan proses utama aplikasi.
 */
function kirimEmailSipeka(
    string $tujuan,
    string $nama,
    string $subjek,
    string $pesan
): bool {
    if (strtolower(env_atau('MAIL_ENABLED', 'false')) !== 'true') {
        return false;
    }

    if (!filter_var($tujuan, FILTER_VALIDATE_EMAIL)) {
        error_log('Email SIPEKA tidak valid: ' . $tujuan);
        return false;
    }

    try {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = env_atau('MAIL_HOST', 'smtp.gmail.com');
        $mail->SMTPAuth = true;
        $mail->Username = env_atau('MAIL_USERNAME', '');
        $mail->Password = env_atau('MAIL_PASSWORD', '');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = (int)env_atau('MAIL_PORT', '587');

        $mail->CharSet = 'UTF-8';
        $mail->setFrom(
            env_atau('MAIL_FROM_EMAIL', ''),
            env_atau('MAIL_FROM_NAME', 'SIPEKA')
        );

        $mail->addAddress($tujuan, $nama);
        $mail->isHTML(true);
        $mail->Subject = $subjek;

        $namaAman = htmlspecialchars($nama, ENT_QUOTES, 'UTF-8');
        $pesanAman = nl2br(
            htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8')
        );

        $mail->Body = "
            <div style='font-family:Arial,sans-serif;line-height:1.6'>
                <h2>Notifikasi SIPEKA</h2>
                <p>Halo, {$namaAman}.</p>
                <p>{$pesanAman}</p>
                <p>Silakan masuk ke aplikasi SIPEKA untuk melihat
                informasi lebih lanjut.</p>
                <hr>
                <small>Email otomatis dari Sistem Pengaduan
                Kampus (SIPEKA). Mohon tidak membalas email ini.</small>
            </div>
        ";

        $mail->AltBody = "Halo, {$nama}\n\n{$pesan}\n\nSilakan masuk ke aplikasi SIPEKA.";

        return $mail->send();
    } catch (Throwable $e) {
        error_log('Gagal mengirim email SIPEKA: ' . $e->getMessage());
        return false;
    }
}

/**
 * Mengambil data laporan dan email mahasiswa pemilik laporan.
 */
function ambilDataEmailMahasiswa(PDO $pdo, int $idLaporan): ?array {
    $sql = "
        SELECT
            l.id_laporan,
            l.judul_laporan,
            l.status,
            l.tanggal_lapor,
            m.id_user,
            u.nama,
            u.email
        FROM laporan l
        JOIN mahasiswa m ON m.id_mahasiswa = l.id_mahasiswa
        JOIN users u ON u.id_user = m.id_user
        WHERE l.id_laporan = :id
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $idLaporan]);

    $data = $stmt->fetch();
    return $data ?: null;
}

/**
 * Menghasilkan nomor tiket laporan.
 */
function tiketEmailSipeka(array $laporan): string {
    return 'SPK-' .
        date('Y', strtotime($laporan['tanggal_lapor'])) .
        '-' .
        str_pad((string)$laporan['id_laporan'], 5, '0', STR_PAD_LEFT);
}
