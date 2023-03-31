<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\CustomFieldGroup
 *
 * @property int $id
 * @property string $name
 * @property string|null $model
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFieldGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFieldGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFieldGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFieldGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFieldGroup whereModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFieldGroup whereName($value)
 * @mixin \Eloquent
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFieldGroup whereCompanyId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CustomField[] $customField
 * @property-read int|null $custom_field_count
 */
class CustomFieldGroup extends BaseModel
{

    use HasCompany;

    const ALL_FIELDS = [
        ['name' => 'Client', 'model' => ClientDetails::CUSTOM_FIELD_MODEL],
        ['name' => 'Employee', 'model' => EmployeeDetails::CUSTOM_FIELD_MODEL],
        ['name' => 'Project', 'model' => Project::CUSTOM_FIELD_MODEL],
        ['name' => 'Invoice', 'model' => Invoice::CUSTOM_FIELD_MODEL],
        ['name' => 'Estimate', 'model' => Estimate::CUSTOM_FIELD_MODEL],
        ['name' => 'Task', 'model' => Task::CUSTOM_FIELD_MODEL],
        ['name' => 'Expense', 'model' => Expense::CUSTOM_FIELD_MODEL],
        ['name' => 'Lead', 'model' => Lead::CUSTOM_FIELD_MODEL],
        ['name' => 'Product', 'model' => Product::CUSTOM_FIELD_MODEL],
        ['name' => 'Ticket', 'model' => Ticket::CUSTOM_FIELD_MODEL],
        ['name' => 'Time Log', 'model' => ProjectTimeLog::CUSTOM_FIELD_MODEL],
        ['name' => 'Contract', 'model' => Contract::CUSTOM_FIELD_MODEL]
    ];

    public $timestamps = false;

    public function customField(): HasMany
    {
        return $this->HasMany(CustomField::class);
    }

    public static function customFieldsDataMerge($model)
    {
        $customFields = CustomField::exportCustomFields($model);

        $customFieldsDataMerge = [];

        foreach ($customFields as $customField) {
            $customFieldsData = [
                $customField->name => [
                    'data' => $customField->name,
                    'name' => $customField->name,
                    'title' => $customField->name,
                    'visible' => false
                ]
            ];

            $customFieldsDataMerge = array_merge($customFieldsDataMerge, $customFieldsData);
        }

        return $customFieldsDataMerge;
    }

}
