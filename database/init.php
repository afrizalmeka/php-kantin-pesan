<?php
function initDatabase(PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT 'pelanggan',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS menu (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nama TEXT NOT NULL,
        deskripsi TEXT,
        harga REAL NOT NULL,
        kategori TEXT NOT NULL DEFAULT 'Makanan',
        tersedia INTEGER NOT NULL DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS pesanan (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        total_harga REAL NOT NULL,
        status TEXT NOT NULL DEFAULT 'pending',
        catatan TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS pesanan_item (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        pesanan_id INTEGER NOT NULL,
        menu_id INTEGER NOT NULL,
        jumlah INTEGER NOT NULL,
        harga REAL NOT NULL,
        FOREIGN KEY (pesanan_id) REFERENCES pesanan(id),
        FOREIGN KEY (menu_id) REFERENCES menu(id)
    )");

    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    if ($stmt->fetchColumn() == 0) {
        $adminPass = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO users (name, email, password, role) VALUES ('Admin Kantin', 'admin@kantinku.com', '$adminPass', 'admin')");
        $userPass = password_hash('user123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO users (name, email, password, role) VALUES ('Budi Mahasiswa', 'budi@student.com', '$userPass', 'pelanggan')");

        $menu = [
            ['Nasi Goreng Spesial', 'Nasi goreng dengan telur, ayam, dan sayuran segar', 15000, 'Makanan'],
            ['Mie Goreng', 'Mie goreng dengan telur dan bakso', 12000, 'Makanan'],
            ['Ayam Geprek', 'Ayam goreng crispy dengan sambal bawang', 18000, 'Makanan'],
            ['Gado-Gado', 'Sayuran segar dengan bumbu kacang', 10000, 'Makanan'],
            ['Es Teh Manis', 'Teh manis dingin segar', 4000, 'Minuman'],
            ['Es Jeruk', 'Jeruk peras dingin', 5000, 'Minuman'],
            ['Jus Alpukat', 'Jus alpukat segar dengan susu', 8000, 'Minuman'],
            ['Pisang Goreng', 'Pisang goreng crispy, 3 buah', 7000, 'Snack'],
        ];
        $stmt = $pdo->prepare("INSERT INTO menu (nama, deskripsi, harga, kategori) VALUES (?,?,?,?)");
        foreach ($menu as $m) $stmt->execute($m);
    }
}
