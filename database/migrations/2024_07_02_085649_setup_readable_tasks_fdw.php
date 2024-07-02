<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private $dbHost;
    private $dbPort;
    private $dbDatabase;
    private $dbUsername;
    private $dbPassword;
    private $serverName;
    private $dbDatabaseFdw;

    public function __construct()
    {
        // set value from env or set default
        $this->dbHost        = env('DB_HOST', '172.19.0.2');
        $this->dbPort        = env('DB_PORT', '5432');
        $this->dbDatabase    = env('DB_DATABASE', 'laravel_db_api');
        $this->dbUsername    = env('DB_USERNAME', 'laravel_user_api');
        $this->dbPassword    = env('DB_PASSWORD', 'test_task_pass!');
    }
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::connection('pgsql_fdw')->statement("CREATE EXTENSION IF NOT EXISTS postgres_fdw;");    
        DB::connection('pgsql_fdw')->statement("CREATE SERVER laravel_api_server 
            FOREIGN DATA WRAPPER postgres_fdw OPTIONS (host '$this->dbHost', dbname '$this->dbDatabase', port '$this->dbPort');
        ");
        DB::connection('pgsql_fdw')->statement("CREATE USER MAPPING FOR $this->dbUsername
            SERVER laravel_api_server
            OPTIONS (user '$this->dbUsername', password '$this->dbPassword');
        ");

           // Create the foreign table readable_tasks in laravel_db_fdw_api
        DB::connection('pgsql_fdw')->statement("
           CREATE FOREIGN TABLE readable_tasks (
               id INT,
               title VARCHAR(100),
               content TEXT,
               is_done BOOLEAN DEFAULT false,
               created_at TIMESTAMP,
               updated_at TIMESTAMP,
               deleted_at TIMESTAMP
           )
           SERVER laravel_api_server 
           OPTIONS (schema_name 'public', table_name 'writable_tasks');
       ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('pgsql_fdw')->statement("DROP FOREIGN TABLE IF EXISTS laravel_db_fdw_api.readable_tasks;");
        DB::connection('pgsql_fdw')->statement("DROP USER MAPPING IF EXISTS FOR $this->dbUsername SERVER laravel_db_api_server;");
        DB::connection('pgsql_fdw')->statement("DROP SERVER IF EXISTS laravel_db_api_server CASCADE;");
        DB::connection('pgsql_fdw')->statement("DROP EXTENSION IF EXISTS postgres_fdw CASCADE;");
    }
};
