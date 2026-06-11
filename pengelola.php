<?php
session_start();
include "db.php";

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

$username_session = $_SESSION['username'];
$admin = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT * FROM tbl_admin WHERE Username='$username_session' LIMIT 1"
));

$msg_success = '';
$msg_error   = '';

/* =========================
   UPLOAD FOTO PROFIL
========================= */
if (isset($_POST['action']) && $_POST['action'] == 'upload_foto') {
    $id = $admin['id_admin'];
    $file = $_FILES['foto'];
    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $msg_error = "Error upload: kode " . $file['error'];
    } elseif (!in_array($ext, $allowed)) {
        $msg_error = "Format file tidak didukung.";
    } elseif ($file['size'] > 2 * 1024 * 1024) {
        $msg_error = "Ukuran file maksimal 2MB.";
    } else {
        $folder_abs = __DIR__ . "/uploads/profil/";

        // Buat folder jika belum ada
        if (!is_dir($folder_abs)) {
            mkdir($folder_abs, 0777, true);
            chmod($folder_abs, 0777);
        }

        $filename = "admin_" . $id . "." . $ext;
        $dest_abs = $folder_abs . $filename;

        // Hapus foto lama
        foreach ($allowed as $e) {
            $old = $folder_abs . "admin_" . $id . "." . $e;
            if (file_exists($old)) unlink($old);
        }

        if (move_uploaded_file($file['tmp_name'], $dest_abs)) {
            chmod($dest_abs, 0644);
            mysqli_query($koneksi,
                "UPDATE tbl_admin SET foto='$filename' WHERE id_admin='$id'"
            );
            $msg_success = "Foto profil berhasil diperbarui.";
            $admin = mysqli_fetch_assoc(mysqli_query($koneksi,
                "SELECT * FROM tbl_admin WHERE id_admin='$id'"
            ));
        } else {
            // Debug lebih detail
            $msg_error = "Gagal memindahkan file. Tmp: " . $file['tmp_name'] 
                       . " | Dest: " . $dest_abs
                       . " | Writable: " . (is_writable($folder_abs) ? 'YA' : 'TIDAK');
        }
    }
}

/* =========================
   HAPUS FOTO PROFIL
========================= */
if (isset($_POST['action']) && $_POST['action'] == 'hapus_foto') {
    $id     = $admin['id_admin'];
    $folder_abs = __DIR__ . "/uploads/profil/";
    foreach (['jpg','jpeg','png','webp'] as $e) {
        $f = $folder_abs . "admin_" . $id . "." . $e;
        if (file_exists($f)) unlink($f);
    }
    mysqli_query($koneksi,
        "UPDATE tbl_admin SET foto=NULL WHERE id_admin='$id'"
    );
    $msg_success = "Foto profil berhasil dihapus.";
    $admin['foto'] = null;
}

/* =========================
   EDIT PROFIL
========================= */
if (isset($_POST['action']) && $_POST['action'] == 'edit_profil') {
    $id    = $admin['id_admin'];
    $nama  = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $uname = mysqli_real_escape_string($koneksi, $_POST['username']);

    // Cek username duplikat
    $cek = mysqli_fetch_assoc(mysqli_query($koneksi,
        "SELECT id_admin FROM tbl_admin WHERE Username='$uname' AND id_admin != '$id'"
    ));
    if ($cek) {
        $msg_error = "Username sudah digunakan oleh admin lain.";
    } else {
        mysqli_query($koneksi,
            "UPDATE tbl_admin SET Nama='$nama', Email='$email', Username='$uname'
             WHERE id_admin='$id'"
        );
        $_SESSION['username'] = $uname;
        $msg_success = "Profil berhasil diperbarui.";
        $admin = mysqli_fetch_assoc(mysqli_query($koneksi,
            "SELECT * FROM tbl_admin WHERE id_admin='$id'"
        ));
    }
}

/* =========================
   GANTI PASSWORD
========================= */
if (isset($_POST['action']) && $_POST['action'] == 'ganti_password') {
    $id           = $admin['id_admin'];
    $pass_lama    = $_POST['password_lama'];
    $pass_baru    = $_POST['password_baru'];
    $pass_konfirm = $_POST['password_konfirm'];

    $hash_lama = $admin['Password'];
    $cocok     = password_verify($pass_lama, $hash_lama)
                 || ($pass_lama === $hash_lama); // fallback plain text

    if (!$cocok) {
        $msg_error = "Password lama tidak sesuai.";
    } elseif (strlen($pass_baru) < 6) {
        $msg_error = "Password baru minimal 6 karakter.";
    } elseif ($pass_baru !== $pass_konfirm) {
        $msg_error = "Konfirmasi password tidak cocok.";
    } else {
        $hash_baru = password_hash($pass_baru, PASSWORD_DEFAULT);
        $update = mysqli_query($koneksi,
            "UPDATE tbl_admin SET Password='$hash_baru' WHERE id_admin='$id'"
        );
        $msg_success = "Password berhasil diperbarui.";
    }
}

/* =========================
   TAMBAH USER
========================= */
// Ganti bagian ganti password
if (isset($_POST['action']) && $_POST['action'] == 'ganti_password') {
    // Ambil id dari username session, bukan dari $admin yang mungkin kosong
    $username_skrg = $_SESSION['username'];
    $cek_admin = mysqli_fetch_assoc(mysqli_query($koneksi,
        "SELECT id_admin FROM tbl_admin WHERE Username='$username_skrg'"
    ));
    $id = $cek_admin['id_admin'];

    $pass_lama    = $_POST['password_lama'];
    $pass_baru    = $_POST['password_baru'];
    $pass_konfirm = $_POST['password_konfirm'];

    // Ambil password saat ini dari DB
    $data_pass = mysqli_fetch_assoc(mysqli_query($koneksi,
        "SELECT Password FROM tbl_admin WHERE id_admin='$id'"
    ));
    $hash_lama = $data_pass['Password'];

    $cocok = password_verify($pass_lama, $hash_lama)
             || ($pass_lama === $hash_lama);

    if (!$cocok) {
        $msg_error = "Password lama tidak sesuai.";
    } elseif (strlen($pass_baru) < 6) {
        $msg_error = "Password baru minimal 6 karakter.";
    } elseif ($pass_baru !== $pass_konfirm) {
        $msg_error = "Konfirmasi password tidak cocok.";
    } else {
        $hash_baru = password_hash($pass_baru, PASSWORD_DEFAULT);
        mysqli_query($koneksi,
            "UPDATE tbl_admin SET Password='$hash_baru' WHERE id_admin='$id'"
        );
        $msg_success = "Password berhasil diperbarui.";
    }
}

/* =========================
   HAPUS USER
========================= */
if (isset($_GET['hapus_user'])) {
    $id_hapus = (int)$_GET['hapus_user'];
    if ($id_hapus == $admin['id_admin']) {
        $msg_error = "Tidak bisa menghapus akun yang sedang aktif.";
    } else {
        mysqli_query($koneksi,
            "DELETE FROM tbl_admin WHERE id_admin='$id_hapus'"
        );
        $msg_success = "User berhasil dihapus.";
    }
}

/* =========================
   AMBIL SEMUA USER
========================= */
$semuaUser = [];
$qUser = mysqli_query($koneksi, "SELECT * FROM tbl_admin ORDER BY id_admin");
while ($u = mysqli_fetch_assoc($qUser)) $semuaUser[] = $u;

/* =========================
   FOTO PROFIL
========================= */
$foto_path = null;
if (!empty($admin['foto'])) {
    $f_abs = __DIR__ . "/uploads/profil/" . $admin['foto'];
    if (file_exists($f_abs)) {
        $foto_path = "uploads/profil/" . $admin['foto'];
    }
}

/* =========================
   TAMBAH USER
========================= */
if (isset($_POST['action']) && $_POST['action'] == 'tambah_user') {
    $t_nama     = mysqli_real_escape_string($koneksi, $_POST['t_nama']);
    $t_username = mysqli_real_escape_string($koneksi, $_POST['t_username']);
    $t_email    = mysqli_real_escape_string($koneksi, $_POST['t_email']);
    $t_password = $_POST['t_password'];

    // Cek username sudah ada
    $cek = mysqli_fetch_assoc(mysqli_query($koneksi,
        "SELECT id_admin FROM tbl_admin WHERE Username='$t_username'"
    ));

    if ($cek) {
        $msg_error = "Username '$t_username' sudah digunakan.";
    } elseif (strlen($t_password) < 6) {
        $msg_error = "Password minimal 6 karakter.";
    } else {
        $hash = password_hash($t_password, PASSWORD_DEFAULT);
        $q = mysqli_query($koneksi,
            "INSERT INTO tbl_admin (Nama, Username, Email, Password)
             VALUES ('$t_nama', '$t_username', '$t_email', '$hash')"
        );
        if ($q) {
            $msg_success = "Administrator '$t_username' berhasil ditambahkan.";
        } else {
            $msg_error = "Gagal menambah user: " . mysqli_error($koneksi);
        }
    }
}

if ($q) {
            $_SESSION['notif_pengelola'] = ['type'=>'success', 'msg'=>"Administrator '$t_username' berhasil ditambahkan."];
            header("Location: pengelola.php#tab-users");
            exit;
        }

include "layout/header.php";
?>

<style>
.pg-header { margin-bottom: 28px; }
.pg-header h2 { font-size: 22px; font-weight: 700; color: #3e2723; margin: 0; }
.pg-header p  { font-size: 13px; color: #8d6e63; margin-top: 4px; }

.pg-grid { display: grid; grid-template-columns: 320px 1fr; gap: 24px; align-items: start; }

/* KARTU PROFIL KIRI */
.profil-card {
    background: #fffaf5;
    border-radius: 20px;
    padding: 30px 24px;
    box-shadow: 0 4px 20px rgba(93,64,55,.10);
    border: 1px solid rgba(141,110,99,.12);
    text-align: center;
}
.avatar-wrap {
    position: relative;
    width: 110px;
    height: 110px;
    margin: 0 auto 18px;
}
.avatar-wrap img,
.avatar-wrap .avatar-placeholder {
    width: 110px; height: 110px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #8d6e63;
    box-shadow: 0 6px 20px rgba(93,64,55,.25);
}
.avatar-wrap .avatar-placeholder {
    background: linear-gradient(135deg, #8d6e63, #5d4037);
    display: flex; align-items: center; justify-content: center;
    font-size: 40px; color: white;
}
.avatar-edit-btn {
    position: absolute; bottom: 2px; right: 2px;
    width: 32px; height: 32px;
    background: linear-gradient(135deg, #6d4c41, #4e342e);
    border: none; border-radius: 50%;
    color: white; font-size: 13px;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    box-shadow: 0 3px 10px rgba(0,0,0,.3);
    transition: transform .2s;
}
.avatar-edit-btn:hover { transform: scale(1.1); }
.profil-nama  { font-size: 18px; font-weight: 700; color: #3e2723; margin-bottom: 4px; }
.profil-uname { font-size: 13px; color: #8d6e63; margin-bottom: 6px; }
.profil-email { font-size: 12px; color: #a1887f; margin-bottom: 18px; }
.profil-badge {
    display: inline-block;
    background: linear-gradient(135deg, #8d6e63, #5d4037);
    color: white; font-size: 11px; font-weight: 600;
    padding: 4px 14px; border-radius: 20px; margin-bottom: 20px;
    letter-spacing: .5px;
}
.profil-divider { height: 1px; background: rgba(141,110,99,.2); margin: 18px 0; }
.profil-stat { display: flex; justify-content: space-around; }
.profil-stat-item { text-align: center; }
.profil-stat-item .val { font-size: 20px; font-weight: 700; color: #5d4037; }
.profil-stat-item .lbl { font-size: 10px; color: #a1887f; text-transform: uppercase; letter-spacing: .5px; }

/* PANEL KANAN */
.panel-tabs {
    display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap;
}
.tab-btn {
    padding: 10px 20px; border: none; border-radius: 12px;
    font-size: 12px; font-weight: 600; cursor: pointer;
    background: #f0e4d4; color: #6d4c41;
    transition: all .2s;
}
.tab-btn.active, .tab-btn:hover {
    background: linear-gradient(135deg, #8d6e63, #5d4037);
    color: white;
}
.tab-panel { display: none; }
.tab-panel.active { display: block; }

.section-card {
    background: #fffaf5;
    border-radius: 20px;
    padding: 26px;
    box-shadow: 0 4px 20px rgba(93,64,55,.08);
    border: 1px solid rgba(141,110,99,.1);
    margin-bottom: 20px;
}
.section-title {
    font-size: 15px; font-weight: 700; color: #3e2723;
    margin-bottom: 20px; display: flex; align-items: center; gap: 8px;
}
.section-title i {
    width: 32px; height: 32px; border-radius: 8px;
    background: linear-gradient(135deg, #8d6e63, #5d4037);
    color: white; display: flex; align-items: center; justify-content: center;
    font-size: 14px;
}

/* USER TABLE */
.user-table th { font-size: 12px; }
.user-badge-self {
    background: #fff3cd; color: #856404;
    font-size: 10px; padding: 2px 8px; border-radius: 10px;
    font-weight: 600; margin-left: 6px;
}

/* LOGOUT BTN */
.logout-card {
    background: linear-gradient(135deg, #c62828, #b71c1c);
    border-radius: 20px; padding: 24px;
    color: white; text-align: center;
}
.logout-card h5 { font-size: 16px; font-weight: 700; margin-bottom: 8px; }
.logout-card p  { font-size: 13px; opacity: .8; margin-bottom: 18px; }

@media (max-width: 768px) {
    .pg-grid { grid-template-columns: 1fr; }
}
</style>

<div class="pg-header">
    <h2>⚙️ Pengelola Sistem</h2>
    <p>Kelola profil, keamanan akun, dan manajemen pengguna</p>
</div>

<?php if ($msg_success): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i><?= $msg_success ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if ($msg_error): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="fas fa-exclamation-circle me-2"></i><?= $msg_error ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['notif_pengelola'])): ?>
<div class="alert alert-<?= $_SESSION['notif_pengelola']['type'] ?> alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i><?= $_SESSION['notif_pengelola']['msg'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['notif_pengelola']); endif; ?>

<div class="pg-grid">

    <!-- =====================
         KARTU PROFIL KIRI
    ===================== -->
    <div>
        <div class="profil-card">

            <!-- Avatar -->
            <div class="avatar-wrap">
                <?php if ($foto_path): ?>
                    <img src="<?= $foto_path ?>?v=<?= time() ?>" alt="Foto Profil">
                <?php else: ?>
                    <div class="avatar-placeholder">
                        <i class="fas fa-user"></i>
                    </div>
                <?php endif; ?>
                <button class="avatar-edit-btn" onclick="document.getElementById('inputFoto').click()" title="Ganti foto">
                    <i class="fas fa-camera"></i>
                </button>
            </div>

            <!-- Form upload foto (hidden) -->
            <form method="POST" enctype="multipart/form-data" id="formFoto">
                <input type="hidden" name="action" value="upload_foto">
                <input type="file" id="inputFoto" name="foto" accept="image/*" style="display:none"
                    onchange="document.getElementById('formFoto').submit()">
            </form>

            <div class="profil-nama"><?= htmlspecialchars($admin['Nama']) ?></div>
            <div class="profil-uname">@<?= htmlspecialchars($admin['Username']) ?></div>
            <div class="profil-email"><?= htmlspecialchars($admin['Email']) ?></div>
            <div class="profil-badge"><i class="fas fa-shield-halved me-1"></i>Administrator</div>

            <?php if ($foto_path): ?>
            <form method="POST" onsubmit="return confirm('Hapus foto profil?')">
                <input type="hidden" name="action" value="hapus_foto">
                <button class="btn btn-sm btn-outline-danger w-100 mb-2">
                    <i class="fas fa-trash me-1"></i> Hapus Foto
                </button>
            </form>
            <?php endif; ?>

            <div class="profil-divider"></div>

            <div class="profil-stat">
                <div class="profil-stat-item">
                    <div class="val"><?= count($semuaUser) ?></div>
                    <div class="lbl">Total Admin</div>
                </div>
                <div class="profil-stat-item">
                    <div class="val"><?= mysqli_num_rows(mysqli_query($koneksi,"SELECT * FROM tbl_responden")) ?></div>
                    <div class="lbl">Responden</div>
                </div>
                <div class="profil-stat-item">
                    <div class="val"><?= mysqli_num_rows(mysqli_query($koneksi,"SELECT * FROM tbl_alternatif")) ?></div>
                    <div class="lbl">Alternatif</div>
                </div>
            </div>
        </div>

        <!-- Logout Card -->
        <div class="logout-card mt-4">
            <h5><i class="fas fa-right-from-bracket me-2"></i>Keluar Sistem</h5>
            <p>Akhiri sesi login Anda sekarang</p>
            <a href="logout.php"
               class="btn btn-light fw-bold"
               onclick="return confirm('Yakin ingin keluar?')">
                <i class="fas fa-right-from-bracket me-2"></i>Logout
            </a>
        </div>
    </div>

    <!-- =====================
         PANEL KANAN
    ===================== -->
    <div>
        <div class="panel-tabs">
            <button class="tab-btn active" onclick="showTab('profil', this)">
                <i class="fas fa-user me-1"></i> Edit Profil
            </button>
            <button class="tab-btn" onclick="showTab('password', this)">
                <i class="fas fa-lock me-1"></i> Ganti Password
            </button>
            <button class="tab-btn" onclick="showTab('users', this)">
                <i class="fas fa-users-gear me-1"></i> Manajemen User
            </button>
        </div>

        <!-- TAB: EDIT PROFIL -->
        <div id="tab-profil" class="tab-panel active">
            <div class="section-card">
                <div class="section-title">
                    <i class="fas fa-user-pen"></i>
                    Informasi Profil
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="edit_profil">
                    <div class="mb-3">
                        <label class="form-label fw-600">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control"
                            value="<?= htmlspecialchars($admin['Nama']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600">Username</label>
                        <div class="input-group">
                            <span class="input-group-text">@</span>
                            <input type="text" name="username" class="form-control"
                                value="<?= htmlspecialchars($admin['Username']) ?>" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-600">Email</label>
                        <input type="email" name="email" class="form-control"
                            value="<?= htmlspecialchars($admin['Email']) ?>" required>
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>

        <!-- TAB: GANTI PASSWORD -->
        <div id="tab-password" class="tab-panel">
            <div class="section-card">
                <div class="section-title">
                    <i class="fas fa-lock"></i>
                    Ganti Password
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="ganti_password">
                    <div class="mb-3">
                        <label class="form-label fw-600">Password Lama</label>
                        <div class="input-group">
                            <input type="password" name="password_lama" id="passLama"
                                class="form-control" required>
                            <button type="button" class="btn btn-outline-secondary"
                                onclick="togglePass('passLama', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600">Password Baru</label>
                        <div class="input-group">
                            <input type="password" name="password_baru" id="passBaru"
                                class="form-control" minlength="6" required>
                            <button type="button" class="btn btn-outline-secondary"
                                onclick="togglePass('passBaru', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="form-text">Minimal 6 karakter.</div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-600">Konfirmasi Password Baru</label>
                        <div class="input-group">
                            <input type="password" name="password_konfirm" id="passKonfirm"
                                class="form-control" required>
                            <button type="button" class="btn btn-outline-secondary"
                                onclick="togglePass('passKonfirm', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-shield-halved me-1"></i> Perbarui Password
                    </button>
                </form>
            </div>
        </div>

        <!-- TAB: MANAJEMEN USER -->
        <div id="tab-users" class="tab-panel">

            <!-- Daftar User -->
            <div class="section-card mb-3">
                <div class="section-title">
                    <i class="fas fa-users"></i>
                    Daftar Administrator
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover user-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th width="80">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $no = 1; foreach ($semuaUser as $u): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <?= htmlspecialchars($u['Nama']) ?>
                                <?php if ($u['id_admin'] == $admin['id_admin']): ?>
                                    <span class="user-badge-self">Anda</span>
                                <?php endif; ?>
                            </td>
                            <td>@<?= htmlspecialchars($u['Username']) ?></td>
                            <td><?= htmlspecialchars($u['Email']) ?></td>
                            <td>
                                <?php if ($u['id_admin'] != $admin['id_admin']): ?>
                                <a href="?hapus_user=<?= $u['id_admin'] ?>#tab-users"
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Hapus user <?= htmlspecialchars($u['Username']) ?>?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php else: ?>
                                <span class="text-muted" title="Tidak bisa hapus akun aktif">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tambah User -->
            <div class="section-card">
                <div class="section-title">
                    <i class="fas fa-user-plus"></i>
                    Tambah Administrator Baru
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="tambah_user">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-600">Nama Lengkap</label>
                            <input type="text" name="t_nama" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-600">Username</label>
                            <input type="text" name="t_username" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-600">Email</label>
                            <input type="email" name="t_email" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-600">Password</label>
                            <input type="password" name="t_password"
                                class="form-control" minlength="6" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus me-1"></i> Tambah User
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
function showTab(name, btn) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    btn.classList.add('active');
}

function togglePass(id, btn) {
    const input = document.getElementById(id);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

// Auto buka tab sesuai hash URL
window.addEventListener('load', function () {
    const hash = window.location.hash;
    if (hash === '#tab-users') {
        showTab('users', document.querySelectorAll('.tab-btn')[2]);
    } else if (hash === '#tab-password') {
        showTab('password', document.querySelectorAll('.tab-btn')[1]);
    }
});
</script>

<?php include "layout/footer.php"; ?>