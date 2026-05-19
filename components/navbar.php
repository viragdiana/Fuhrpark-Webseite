<?php
$currentPage = basename($_SERVER['SCRIPT_NAME']);
?>

<nav class="bg-card border-b border-border sticky top-0 z-50">
    <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex overflow-x-auto items-center h-16 gap-8 no-scrollbar">

            <a href="<?= BASE_URL ?>index.php" class="flex items-center gap-2 text-lg font-bold text-foreground whitespace-nowrap pr-4">
                <i class="bi bi-car-front-fill text-primary text-xl"></i>
                Fuhrpark OS
            </a>

            <div class="flex items-center gap-6 h-full">
                <a href="<?= BASE_URL ?>index.php"
                   class="flex items-center gap-2 text-sm font-medium h-full border-b-2 transition-colors whitespace-nowrap <?= in_array($currentPage, ['index.php', 'vehicle_details.php', 'vehicle_form.php']) ? 'border-primary text-foreground' : 'border-transparent text-muted-foreground hover:text-foreground hover:border-border' ?>">
                    <i class="bi bi-car-front"></i> Fahrzeuge
                </a>

                <a href="<?= BASE_URL ?>views/drivers/drivers_list.php"
                   class="flex items-center gap-2 text-sm font-medium h-full border-b-2 transition-colors whitespace-nowrap <?= in_array($currentPage, ['drivers_list.php', 'driver_form.php', 'assign_driver.php']) ? 'border-primary text-foreground' : 'border-transparent text-muted-foreground hover:text-foreground hover:border-border' ?>">
                    <i class="bi bi-people"></i> Fahrer
                </a>

                <a href="#" class="flex items-center gap-2 text-sm font-medium h-full border-b-2 border-transparent text-muted-foreground hover:text-foreground hover:border-border transition-colors whitespace-nowrap">
                    <i class="bi bi-speedometer2"></i> Reifen
                </a>

                <a href="#" class="flex items-center gap-2 text-sm font-medium h-full border-b-2 border-transparent text-muted-foreground hover:text-foreground hover:border-border transition-colors whitespace-nowrap">
                    <i class="bi bi-shield-check"></i> Versicherungen
                </a>

                <a href="#" class="flex items-center gap-2 text-sm font-medium h-full border-b-2 border-transparent text-muted-foreground hover:text-foreground hover:border-border transition-colors whitespace-nowrap">
                    <i class="bi bi-globe-europe-africa"></i> Vignetten
                </a>

                <a href="#" class="flex items-center gap-2 text-sm font-medium h-full border-b-2 border-transparent text-muted-foreground hover:text-foreground hover:border-border transition-colors whitespace-nowrap">
                    <i class="bi bi-wrench"></i> Service
                </a>
            </div>

            <div class="flex-grow"></div>

            <a href="#" class="flex items-center gap-2 text-sm font-medium text-muted-foreground hover:text-foreground transition-colors whitespace-nowrap pl-4 border-l border-border">
                <i class="bi bi-bell"></i> Warnungen
            </a>
        </div>
    </div>
</nav>

<style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>