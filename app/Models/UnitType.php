<?php

namespace App\Models;

use App\Models\BaseModel;
use App\Traits\HasCompany;
use App\Models\EstimateItem;
use App\Models\InvoiceItems;
use App\Models\ProposalItem;
use App\Models\ProposalTemplate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UnitType extends BaseModel
{
    use HasCompany;

    protected $table = 'unit_types';
    protected $id = 'id';
    protected $fillable = ['unit_type', 'company_id', 'default'];

    public function invoicesItems()
    {
        return $this->hasMany(InvoiceItems::class);
    }

    public function proposalitems()
    {
        return $this->hasMany(ProposalItem::class);
    }

    public function estimateitems()
    {
        return $this->hasMany(EstimateItem::class);
    }

    public function creditnoteitems()
    {
        return $this->hasMany(CreditNotes::class);
    }

    public function proposaltemplate()
    {
        return $this->hasMany(ProposalTemplate::class);
    }

    public function estimatetemplate()
    {
        return $this->hasMany(EstimateTemplate::class);
    }

    public function recurringInvoice()
    {
        return $this->hasMany(RecurringInvoice::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

}