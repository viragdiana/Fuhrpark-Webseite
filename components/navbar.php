<?php
$currentPage = basename($_SERVER['SCRIPT_NAME']);
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow-sm">
    <div class="container" style="max-width: 1300px;">

        <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>index.php">
            <i class="bi bi-car-front-fill me-2 text-primary"></i>Fuhrpark OS
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto gap-2">

                <li class="nav-item">
                    <a class="nav-link <?= in_array($currentPage, ['index.php', 'vehicle_details.php', 'vehicle_form.php']) ? 'active text-primary fw-bold' : '' ?>"
                       href="<?= BASE_URL ?>index.php">
                        <i class="bi bi-speedometer2 me-1"></i> Fahrzeuge
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= in_array($currentPage, ['drivers_list.php', 'driver_form.php', 'assign_driver.php']) ? 'active text-primary fw-bold' : '' ?>"
                       href="<?= BASE_URL ?>views/drivers/drivers_list.php">
                        <i class="bi bi-people me-1"></i> Fahrer
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= in_array($currentPage, ['service_form.php']) ? 'active text-primary fw-bold' : '' ?>"
                       href="#">
                        <i class="bi bi-wrench-adjustable me-1"></i> Service
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav gap-3">
                <li class="nav-item">
                    <a class="nav-link" href="#"><i class="bi bi-gear"></i> Einstellungen</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="#"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>