<nav class="navbar navbar-expand px-4 py-3 shadow-sm d-none d-md-flex justify-content-between">
    <div class="d-flex align-items-center">
        <p class="lead text-muted mb-0 me-2" style="text-transform: capitalize;">Halo, <?php echo $_SESSION['role']; ?></p>
        <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary fs-6">
            <?php echo ucfirst($_SESSION['name']); ?>
        </span>
    </div>

    <div class="text-end">
        <div class="fs-5 fw-semibold text-secondary"><?php echo date('l, d F Y'); ?></div>
    </div>
</nav>