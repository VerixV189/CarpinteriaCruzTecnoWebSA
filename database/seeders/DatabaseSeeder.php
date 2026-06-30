<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Cliente;
use App\Models\Carpintero;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Ejecutar el script SQL para roles, permisos y rol_permiso
        $sql = <<<SQL
        INSERT INTO roles (id, nombre, estado) VALUES
        (1, 'Propietario', 'Activo'),
        (2, 'Cliente', 'Activo'),
        (3, 'Carpintero', 'Activo');

        INSERT INTO permisos (id, nombre) VALUES
        (1, 'LISUSU'), (2, 'REGUSU'), (3, 'ACTUSU'), (4, 'ELIMUSU'),
        (5, 'LISCLI'), (6, 'REGCLI'), (7, 'ACTCLI'), (8, 'ELIMCLI'),
        (9, 'LISCARP'), (10, 'REGCARP'), (11, 'ACTCARP'), (12, 'ELIMCARP'),
        (13, 'LISPROD'), (14, 'REGPROD'), (15, 'ACTPROD'), (16, 'ELIMPROD'),
        (17, 'LISCOT'), (18, 'REGCOT'), (19, 'ACTCOT'), (20, 'ELIMCOT'),
        (21, 'LISPED'), (22, 'REGPED'), (23, 'ACTPED'), (24, 'ELIMPED'),
        (25, 'LISDET'), (26, 'REGDET'), (27, 'ACTDET'), (28, 'ELIMDET'),
        (29, 'LISPAG'), (30, 'REGPAG'), (31, 'ACTPAG'), (32, 'ELIMPAG'),
        (33, 'LISVEN'), (34, 'REGVEN'), (35, 'ACTVEN'), (36, 'ELIMVEN'),
        (37, 'LISINSU'), (38, 'REGINSU'), (39, 'ACTINSU'), (40, 'ELIMINSU'),
        (41, 'LISPROV'), (42, 'REGPROV'), (43, 'ACTPROV'), (44, 'ELIMPROV'),
        (45, 'LISROL'), (46, 'REGROL'), (47, 'ACTROL'), (48, 'ELIMROL'),
        (49, 'LISTIP'), (50, 'REGTIP'), (51, 'ACTTIP'), (52, 'ELIMTIP'),
        (53, 'LISIMG'), (54, 'REGIMG'), (55, 'ACTIMG'), (56, 'ELIMIMG'),
        (57, 'LISPERM'), (58, 'REGPERM'), (59, 'ACTPERM'), (60, 'ELIMPERM'),
        (61, 'LISDETCOT'), (62, 'REGDETCOT'), (63, 'ACTDETCOT'), (64, 'ELIMDETCOT'),
        (65, 'REPVENTOT'), (66, 'REPVENPROD'), (67, 'REPCOTCARP'), (68, 'REPDETPED'),
        (69, 'REPPAGVEN'), (70, 'REPPRODINV'), (71, 'REPINSUPROV'), (72, 'REPPEDFEC'),
        (73, 'REGVENCON'), (74, 'REGVENCRE');

        INSERT INTO rol_permiso (rol_id, permiso_id)
        SELECT 1, id FROM permisos;

        INSERT INTO rol_permiso (rol_id, permiso_id) VALUES
        (2, 5), (2, 6), (2, 7), (2, 13), (2, 17), (2, 18), (2, 19), (2, 20),
        (2, 21), (2, 25), (2, 29), (2, 33), (2, 53);

        -- Nota: Eliminé los permisos duplicados que estaban en el script original del carpintero
        INSERT INTO rol_permiso (rol_id, permiso_id) VALUES
        (3, 9), (3, 11), (3, 13), (3, 14), (3, 15), (3, 17), (3, 19), (3, 61),
        (3, 62), (3, 63), (3, 21), (3, 23), (3, 25), (3, 27), (3, 37), (3, 38),
        (3, 39), (3, 41), (3, 49), (3, 53), (3, 54), (3, 55), (3, 56);
        SQL;

        DB::unprepared($sql);

        // Ajustar secuencias si la base de datos es PostgreSQL
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SELECT setval('roles_id_seq', (SELECT MAX(id) FROM roles))");
            DB::statement("SELECT setval('permisos_id_seq', (SELECT MAX(id) FROM permisos))");
        }

        // 2. Crear Usuarios de Prueba

        // 2.1 Propietario
        User::create([
            'nombre' => 'Erik',
            'apellido' => 'Propietario',
            'email' => 'erikaguilar189@gmail.com',
            'password' => Hash::make('12345678'),
            'telefono' => '12345678',
            'estado' => 'Activo',
            'rol_id' => 1,
        ]);

        // 2.2 Cliente
        $clienteUser = User::create([
            'nombre' => 'Erik',
            'apellido' => 'Cliente',
            'email' => 'erikaguilar20000@gmail.com',
            'password' => Hash::make('12345678'),
            'telefono' => '77777777',
            'estado' => 'Activo',
            'rol_id' => 2,
        ]);

        Cliente::create([
            'usuario_id' => $clienteUser->id,
            'nit_facturacion' => '123456012',
            'razon_social' => 'Juan Perez',
            'direccion_envio' => 'Av. Siempre Viva 123',
        ]);

        // 2.3 Carpintero
        $carpinteroUser = User::create([
            'nombre' => 'Juan',
            'apellido' => 'Gomez',
            'email' => 'juangomez2000@gmail.com',
            'password' => Hash::make('12345678'),
            'telefono' => '66666666',
            'estado' => 'Activo',
            'rol_id' => 3,
        ]);

        Carpintero::create([
            'usuario_id' => $carpinteroUser->id,
            'especialidad' => 'Muebles a Medida',
            'costo_hora' => 50.00,
        ]);

        // 3. Crear registros iniciales de page_visits para todas las páginas principales
        $pages = [
            '/',
            'dashboard',
            'clientes',
            'usuarios',
            'roles',
            'permisos',
            'carpinteros',
            'insumos',
            'productos',
            'proveedores',
            'tipos',
            'cotizaciones',
            'pedidos',
            'ventas',
            'pagos',
            'marketplace',
            'carrito',
            'bitacoras',
            'login',
            'register',
        ];

        foreach ($pages as $page) {
            \App\Models\PageVisit::create([
                'url' => $page,
                'visits' => 0,
            ]);
        }
    }
}
