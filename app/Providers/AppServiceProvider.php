<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use PDO;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->bind(PDO::class, function ($app) {
            $dsn = 'mysql:host='.env('DB_HOST', '127.0.0.1').
                ';port='.env('DB_PORT', 3306).
                ';dbname='.env('DB_DATABASE', 'formation').
                ';charset=utf8mb4';

            $username = env('DB_USERNAME', 'root');
            $password = env('DB_PASSWORD', '');

            try {
                // Créer une instance de PDO avec des options sécurisées
                return new PDO(
                    $dsn,
                    $username,
                    $password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Gestion des erreurs
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Mode de récupération par défaut
                        PDO::ATTR_EMULATE_PREPARES => false,                  // Désactiver les requêtes émulées
                    ]
                );
            } catch (\PDOException $e) {
                // Gérer les erreurs de connexion
                throw new \RuntimeException('Erreur de connexion PDO : '.$e->getMessage());
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
