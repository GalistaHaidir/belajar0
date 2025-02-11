<aside id="sidebar">
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
            <a href="halaman_utama.php" class="sidebar-link">
                <i class="bi bi-house-door-fill" style="color: #007bff;"></i>
                <span>Halaman Utama</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                data-bs-target="#auth1" aria-expanded="false" aria-controls="auth1">
                <i class="bi bi-file-earmark-code-fill" style="color:rgb(182, 219, 255);"></i>
                <span>Materi</span>
            </a>
            <ul id="auth1" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                <?php if (in_array("Guru", $_SESSION['akses'])) { ?>
                    <li class="sidebar-item">
                        <a href="kelola_materi.php" class="sidebar-link">
                            Kelola Materi
                        </a>
                    </li>
                <?php } ?>
                <li class="sidebar-item">
                    <a href="materi.php" class="sidebar-link">
                        Materi
                    </a>
                </li>
            </ul>
        </li>
        <li class="sidebar-item">
            <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                data-bs-target="#auth2" aria-expanded="false" aria-controls="auth2">
                <i class="bi bi-pencil-fill" style="color: #ff8c00;"></i>
                <span>Soal</span>
            </a>
            <ul id="auth2" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                <?php if (in_array("Guru", $_SESSION['akses'])) { ?>
                    <li class="sidebar-item">
                        <a href="kelola_soal.php" class="sidebar-link">
                            Kelola Soal
                        </a>
                    </li>
                <?php } ?>
                <li class="sidebar-item">
                    <a href="kerjakan_soal.php" class="sidebar-link">
                        Soal
                    </a>
                </li>
            </ul>
        </li>
        <li class="sidebar-item">
            <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                data-bs-target="#auth4" aria-expanded="false" aria-controls="auth4">
                <i class="bi bi-list-check" style="color: #20c997;"></i>
                <span>Tugas</span>
            </a>
            <ul id="auth4" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                <?php if (in_array("Guru", $_SESSION['akses'])) { ?>
                    <li class="sidebar-item">
                        <a href="kelola_tugas.php" class="sidebar-link">
                            Kelola Tugas
                        </a>
                    </li>
                <?php } ?>
                <li class="sidebar-item">
                    <a href="tugas_individu.php" class="sidebar-link">
                        Tugas Individu
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="tugas_kelompok.php" class="sidebar-link">
                        Tugas Kelompok
                    </a>
                </li>
            </ul>
        </li>
        <?php if (in_array("Guru", $_SESSION['akses'])) { ?>
            <li class="sidebar-item">
                <a href="kelola_akunsiswa.php" class="sidebar-link">
                    <i class="bi bi-person-arms-up" style="color:rgb(164, 114, 255);"></i>
                    <span>Siswa</span>
                </a>
            <?php } ?>
            </li>
    </ul>
    <div class="sidebar-footer mb-1">
        <a href="logout.php" class="sidebar-link">
            <i class="bi bi-door-open-fill"style="color:rgb(255, 0, 0);"></i>
            <span>Keluar</span>
        </a>
    </div>
</aside>