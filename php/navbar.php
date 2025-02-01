<nav class="navbar navbar-expand px-4 py-3">
    <h3 class="fw-bold" style="text-transform: capitalize;">Hi, <?= $sessionUsername; ?></h3>
    <div class="navbar-collapse collapse">
        <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown">
                <a href="#" data-bs-toggle="dropdown" class="nav-icon pe-md-0">
                    <img src="profile/<?php echo htmlspecialchars($fotoProfil); ?>" class="avatar omg-fluid">
                </a>
                <div class="dropdown-menu dropdown-menu-end rounded-4 border-0 shadow mt-3">
                    <a href="profil.php" class="dropdown-item">
                        <i class="bi bi-person-fill"></i>
                        <span>Akun</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="logout.php" class="dropdown-item">
                        <i class="bi bi-door-open-fill"></i>
                        <span>Keluar</span>
                    </a>
                </div>
            </li>
        </ul>
    </div>
</nav>