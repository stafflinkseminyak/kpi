<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiJobDescription extends Model
{
    protected $table = 'kpi_job_descriptions';

    protected $fillable = [
        'contract_id', 'contract_number', 'employee_name', 'position_title',
        'start_date', 'kpi_data', 'jd_data', 'kpi_status', 'jd_status',
        'created_by', 'kpi_approved_by', 'kpi_rejected_by', 'kpi_approved_at',
        'kpi_rejected_at', 'jd_approved_by', 'jd_rejected_by', 'jd_approved_at',
        'jd_rejected_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'kpi_data' => 'array',
        'jd_data' => 'array',
        'kpi_approved_at' => 'datetime',
        'kpi_rejected_at' => 'datetime',
        'jd_approved_at' => 'datetime',
        'jd_rejected_at' => 'datetime',
    ];

    // Relationships
    public function contract() { return $this->belongsTo(Contract::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }

    // Status helpers
    public function isKpiPending(): bool { return $this->kpi_status === 'pending_approval'; }
    public function isKpiApproved(): bool { return $this->kpi_status === 'approved'; }
    public function isJdPending(): bool { return $this->jd_status === 'pending_approval'; }
    public function isJdApproved(): bool { return $this->jd_status === 'approved'; }

    // Default KPI structure
    public static function defaultKpiData(): array
    {
        return [
            'meetings_done' => [
                'title' => 'Meetings Done',
                'targets' => ['You are required to have minimum of 70 meetings per month', ''],
            ],
            'calls_made' => [
                'title' => 'Calls Made',
                'targets' => ['You are required to make at least 500 calls per month to new clients', 'You are required to make 700 follow-up calls'],
            ],
            'mails_sent' => [
                'title' => 'Mails Sent',
                'targets' => ['You are required to send 1500 mails to new leads every month', ''],
            ],
            'connects_made' => [
                'title' => 'Connects Made',
                'targets' => ['', ''],
            ],
            'conversion_rate' => [
                'title' => 'Conversion Rate',
                'targets' => ['', ''],
            ],
        ];
    }
}