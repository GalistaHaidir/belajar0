<!-- navbar.php -->
<nav class="navbar navbar-expand-md bg-white shadow-sm px-4 py-3 d-md-none">
    <div class="container-fluid p-0">
        <!-- Hamburger hanya di mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mobileNavbar" aria-controls="mobileNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="ms-2 me-auto">
            <p class="lead text-muted mb-0" style="text-transform: capitalize;">Halo, <?php echo $_SESSION['role']; ?></p>
        </div>

        <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary fs-6">
            <?php echo ucfirst($_SESSION['name']); ?>
        </span>
    </div>

    <!-- Menu Mobile -->
    <div class="collapse navbar-collapse mt-3" id="mobileNavbar">
        <ul class="navbar-nav w-100">
            <li class="nav-item">
                <a class="nav-link" href="dashboard.php">
                    <i class="bi bi-speedometer2 me-1"></i> Dashboard
                </a>
            </li>

            <?php if ($_SESSION['role'] === 'admin'): ?>
                <!-- Fitur Admin -->
                <li class="nav-item">
                    <a class="nav-link" href="manajemen_user.php">
                        <i class="bi bi-people-fill me-1 text-warning"></i> Manajemen Pengguna
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manajemen_kelas.php">
                        <i class="bi bi-diagram-3-fill me-1 text-success"></i> Manajemen Kelas
                    </a>
                </li>

                <!-- Fitur Guru -->
                <li class="nav-item">
                    <a class="nav-link" href="daftar_kursus.php">
                        <i class="bi bi-easel-fill me-1 text-info"></i> Kelas yang Saya Ampu
                    </a>
                </li>

                <!-- Menu Nilai (Guru) -->
                <li class="nav-item">
                    <a class="nav-link" href="nilai_tugas.php">
                        <i class="bi bi-clipboard-check me-1 text-primary"></i> Nilai Tugas Individu
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="nilai_pjbl.php">
                        <i class="bi bi-kanban-fill me-1 text-primary"></i> Nilai Tugas PjBL
                    </a>
                </li>

                <!-- Fitur Siswa (tanpa Rangkuman Nilai) -->
                <li class="nav-item">
                    <a class="nav-link" href="kursusku.php">
                        <i class="bi bi-backpack-fill me-1"></i> Kursusku
                    </a>
                </li>

            <?php elseif ($_SESSION['role'] === 'guru'): ?>
                <!-- Fitur Guru -->
                <li class="nav-item">
                    <a class="nav-link" href="daftar_kursus.php">
                        <i class="bi bi-easel-fill me-1 text-info"></i> Kelas yang Saya Ampu
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="nilai_tugas.php">
                        <i class="bi bi-clipboard-check me-1 text-primary"></i> Nilai Tugas Individu
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="nilai_pjbl.php">
                        <i class="bi bi-kanban-fill me-1 text-primary"></i> Nilai Tugas PjBL
                    </a>
                </li>

            <?php elseif ($_SESSION['role'] === 'siswa'): ?>
                <!-- Fitur Siswa -->
                <li class="nav-item">
                    <a class="nav-link" href="kursusku.php">
                        <i class="bi bi-backpack-fill me-1"></i> Kursusku
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="rangkuman_nilai.php">
                        <i class="bi bi-clipboard-data-fill me-1 text-success"></i> Rangkuman Nilai
                    </a>
                </li>
            <?php endif; ?>

            <li>
                <hr class="dropdown-divider">
            </li>
            <li class="nav-item">
                <a class="nav-link text-danger" href="logout.php">
                    <i class="bi bi-door-open-fill me-1"></i> Keluar
                </a>
            </li>
        </ul>
    </div>

</nav>