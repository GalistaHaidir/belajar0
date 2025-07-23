<!-- Sesudah -->
<aside id="sidebar" class="sidebar d-none d-md-flex">
    <div class="d-flex justify-content-between p-4">
        <div class="sidebar-logo">
            <img src="logo.png" alt="Logo Belajar.0">
        </div>
        <button class="toggle-btn border-0" type="button">
            <i id="icon" class="bi bi-arrow-right-short text-white"></i>
        </button>
    </div>
    <ul class="sidebar-nav">
        <li class="sidebar-item">
            <a href="dashboard.php" class="sidebar-link">
                <i class="bi bi-speedometer2 me-1" style="color: #007bff;"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <?php if ($_SESSION['role'] == 'admin'): ?>
            <!-- Fitur Admin -->
            <li class="sidebar-item">
                <a href="manajemen_user.php" class="sidebar-link">
                    <i class="bi bi-people-fill text-warning me-1"></i>
                    <span>Manajemen Pengguna</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="manajemen_kelas.php" class="sidebar-link">
                    <i class="bi bi-diagram-3-fill text-success me-1"></i>
                    <span>Manajemen Kelas</span>
                </a>
            </li>

            <!-- Fitur Guru -->
            <li class="sidebar-item">
                <a href="daftar_kursus.php" class="sidebar-link">
                    <i class="bi bi-easel-fill text-info me-1"></i>
                    <span>Kelas yang Saya Ampu</span>
                </a>
            </li>

            <!-- MENU NILAI -->
            <li class="sidebar-item">
                <a href="nilai_tugas.php" class="sidebar-link">
                    <i class="bi bi-clipboard-check me-1 text-primary"></i>
                    <span>Nilai Tugas Individu</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="nilai_pjbl.php" class="sidebar-link">
                    <i class="bi bi-kanban-fill me-1 text-primary"></i>
                    <span>Nilai Tugas PjBL</span>
                </a>
            </li>

            <!-- Fitur Siswa -->
            <li class="sidebar-item">
                <a href="kursusku.php" class="sidebar-link">
                    <i class="bi bi-backpack-fill me-1"></i>
                    <span>Kursusku</span>
                </a>
            </li>

        <?php elseif ($_SESSION['role'] == 'guru'): ?>
            <!-- Fitur Guru -->
            <li class="sidebar-item">
                <a href="daftar_kursus.php" class="sidebar-link">
                    <i class="bi bi-easel-fill text-info me-1"></i>
                    <span>Kelas yang Saya Ampu</span>
                </a>
            </li>

            <!-- MENU NILAI -->
            <li class="sidebar-item">
                <a href="nilai_tugas.php" class="sidebar-link">
                    <i class="bi bi-clipboard-check me-1 text-primary"></i>
                    <span>Nilai Tugas Individu</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="nilai_pjbl.php" class="sidebar-link">
                    <i class="bi bi-kanban-fill me-1 text-primary"></i>
                    <span>Nilai Tugas PjBL</span>
                </a>
            </li>

        <?php elseif ($_SESSION['role'] == 'siswa'): ?>
            <!-- Fitur Siswa -->
            <li class="sidebar-item">
                <a href="kursusku.php" class="sidebar-link">
                    <i class="bi bi-backpack-fill me-1"></i>
                    <span>Kursusku</span>
                </a>
            </li>

            <!-- Rangkuman Nilai -->
            <li class="sidebar-item">
                <a href="rangkuman_nilai.php" class="sidebar-link">
                    <i class="bi bi-clipboard-data-fill me-1 text-success"></i>
                    <span>Rangkuman Nilai</span>
                </a>
            </li>
        <?php endif; ?>

    </ul>

    <div class="sidebar-footer mb-1">
        <a href="logout.php" class="sidebar-link">
            <i class="bi bi-sign-turn-left-fill text-danger me-1"></i>
            <span>Keluar</span>
        </a>
    </div>
</aside>