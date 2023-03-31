<?php

namespace Database\Seeders;

use App\Models\Designation;
use App\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($companyId)
    {

        $departments = [
            ['team_name' => 'Marketing', 'company_id' => $companyId],
            ['team_name' => 'Sales', 'company_id' => $companyId],
            ['team_name' => 'Human Resources', 'company_id' => $companyId],
            ['team_name' => 'Public Relations', 'company_id' => $companyId],
            ['team_name' => 'Research', 'company_id' => $companyId],
            ['team_name' => 'Finance', 'company_id' => $companyId],
        ];

        $designations = [
            ['name' => 'Trainee', 'company_id' => $companyId],
            ['name' => 'Senior', 'company_id' => $companyId],
            ['name' => 'Junior', 'company_id' => $companyId],
            ['name' => 'Team Lead', 'company_id' => $companyId],
            ['name' => 'Project Manager', 'company_id' => $companyId],
        ];

        Team::insert($departments);
        Designation::insert($designations);
    }

}
