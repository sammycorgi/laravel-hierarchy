<?php


use Illuminate\Support\ServiceProvider;

class HierarchyServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([__DIR__ . "/../config/hierarchy.php" => config_path("hierarchy.php")], "config");

        if (! class_exists("CreateHierarchyChildModelRecordsTable")) {
            $this->publishes([
                __DIR__ . '/../database/migrations/2021_08_19_000000_create_hierarchy_child_model_records_table.php' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_hierarchy_child_model_records_table.php'),
            ], 'migrations');
        }
    }
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . "/../config/hierarchy.php", 'hierarchy');
    }
}