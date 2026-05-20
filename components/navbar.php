<?php
$currentPage = basename($_SERVER['SCRIPT_NAME']);
?>

<nav class="bg-card border-b border-border sticky top-0 z-50">
    <div class="max-w-[1300px] mx-auto px-4 h-16 flex items-center gap-8 overflow-x-auto no-scrollbar">

        <a href="<?= BASE_URL ?>index.php" class="flex items-center gap-2 text-lg font-bold whitespace-nowrap pr-4">
            <i class="bi bi-car-front-fill text-primary text-xl"></i> Fuhrpark OS
        </a>

        <div class="flex items-center gap-6 h-full text-sm font-medium">
            <?php
            $navItems = [
                    ['Fahrzeuge', 'bi-car-front', ['index.php', 'vehicle_details.php', 'vehicle_form.php'], 'index.php'],
                    ['Fahrer', 'bi-people', ['drivers_list.php', 'driver_form.php', 'assign_driver.php'], 'views/drivers/drivers_list.php'],
                    ['Reifen', 'bi-speedometer2', ['tires_list.php', 'tire_form.php'], 'views/vehicles/tires_list.php'],
                    ['Versicherungen', 'bi-shield-check', ['insurance_list.php', 'insurance_form.php'], 'views/vehicles/insurance_list.php'],
                    ['Vignetten', 'bi-globe-europe-africa', ['vignette_list.php', 'vignette_form.php'], 'views/vehicles/vignette_list.php'],
                    ['Service', 'bi-wrench', ['service_list.php', 'service_form.php'], 'views/service/service_list.php']
            ];

            foreach ($navItems as $item):
                $isActive = in_array($currentPage, $item[2])
                        ? 'border-primary text-foreground'
                        : 'border-transparent text-muted-foreground hover:text-foreground hover:border-border';
                ?>
                <a href="<?= BASE_URL . $item[3] ?>" class="flex items-center gap-2 h-full border-b-2 transition-colors whitespace-nowrap <?= $isActive ?>">
                    <i class="bi <?= $item[1] ?>"></i> <?= $item[0] ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="flex-grow"></div>

        <div class="flex items-center h-full">
            <?php
            $isWarningsActive = ($currentPage === 'warnings_list.php')
                    ? 'border-primary text-foreground'
                    : 'border-transparent text-muted-foreground hover:text-foreground hover:border-border';
            ?>
            <a href="<?= BASE_URL ?>views/vehicles/warnings_list.php" class="flex items-center gap-2 h-full border-b-2 text-sm font-medium transition-colors whitespace-nowrap px-4 border-l border-border <?= $isWarningsActive ?>">
                <i class="bi bi-bell"></i> Warnungen
            </a>

            <a href="<?= BASE_URL ?>logout.php" class="flex items-center gap-2 h-full border-b-2 border-transparent text-sm font-medium text-muted-foreground hover:text-destructive transition-colors whitespace-nowrap pl-4 border-l border-border">
                <i class="bi bi-box-arrow-right"></i> Abmelden
            </a>
        </div>
    </div>
</nav>

<style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>