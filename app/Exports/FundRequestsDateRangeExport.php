<?php

namespace App\Exports;

use App\Models\FundRequest;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;

class FundRequestsDateRangeExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected Builder $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    // Tells Laravel Excel to use this query to fetch data
    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Requester',
            'Team',
            'Request Type',
            'Amount (NGN)',
            'Account Name',
            'Account Number',
            'Bank Name',
            'Status',
            'Reason',
            'Date Created',
            'Processed By',
        ];
    }

    /**
    * @var FundRequest $fundRequest
    */
    public function map($fundRequest): array
    {
        return [
            $fundRequest->id,
            $fundRequest->user->name ?? 'N/A',
            ucwords(str_replace('_', ' ', $fundRequest->team)),
            $fundRequest->request_type,
            number_format((float) $fundRequest->amount, 2, '.', ''), // Cast to float for precision
            $fundRequest->account_name,
            $fundRequest->account_number,
            $fundRequest->bank_name,
            $fundRequest->status,
            $fundRequest->reason,
            $fundRequest->created_at->toDateTimeString(),
            $fundRequest->approver->name ?? 'N/A',
        ];
    }
}