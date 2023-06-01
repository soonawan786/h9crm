<?php

namespace App\Console\Commands;

use App\Events\BirthdayReminderEvent;
use App\Events\ClientBirthdayReminderEvent;
use App\Models\ClientDetails;
use App\Models\Company;
use App\Models\EmployeeDetails;
use Illuminate\Console\Command;

class BirthdayReminderCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'birthday-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send birthday notification to everyone';

    /**
     * Handle the command.
     *
     * This method retrieves a list of companies, and for each company it retrieves
     * all employees with an upcoming birthday and triggers the "BirthdayReminderEvent".
     */
    public function handle()
    {
        // Get the current day in the format of "m-d"
        $currentDay = now()->format('m-d');

        // Retrieve all companies
        $companies = Company::select('id')->get();

        // Loop through each company
        foreach ($companies as $company) {
            // Retrieve all active employees with an upcoming birthday for the current company
            $upcomingBirthday = EmployeeDetails::join('users', 'employee_details.user_id', '=', 'users.id')
                ->where('employee_details.company_id', $company->id)
                ->where('users.status', 'active')
                ->whereRaw('DATE_FORMAT(`date_of_birth`, "%m-%d") = "' . $currentDay . '"')
                ->orderBy('employee_details.date_of_birth')
                ->select('employee_details.company_id', 'employee_details.date_of_birth', 'users.name', 'users.image', 'users.id')
                ->get()->toArray();
            //client birthday
            $clientBirthay = ClientDetails::join('users', 'client_details.user_id', '=', 'users.id')
                ->where('client_details.company_id', $company->id)
                ->where('users.status', 'active')
                ->whereRaw('DATE_FORMAT(`date_of_birth`, "%m-%d") = "' . $currentDay . '"')
                ->orderBy('client_details.date_of_birth')
                ->select('client_details.company_id', 'client_details.date_of_birth', 'users.name', 'users.image', 'users.id')
                ->get()->toArray();

            // If there is any employee with an upcoming birthday, trigger the "BirthdayReminderEvent"
            if ($upcomingBirthday != null) {
                event(new BirthdayReminderEvent($company, $upcomingBirthday));
            }
            //client birthday
            if ($clientBirthay != null) {
                event(new ClientBirthdayReminderEvent($company, $clientBirthay));
            }
        }
    }

}
