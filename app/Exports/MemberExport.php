<?php

namespace App\Exports;

use App\Services\MemberService;
use App\Http\Requests\PaginateRequest;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class MemberExport implements FromCollection, WithHeadings
{
    public MemberService $memberService;
    public PaginateRequest $request;

    public function __construct(MemberService $memberService, $request)
    {
        $this->memberService = $memberService;
        $this->request = $request;
    }

    public function collection(): \Illuminate\Support\Collection
    {
        $memberArray = [];
        $members = $this->memberService->list($this->request);

        foreach ($members as $member) {
            $memberArray[] = [
                $member->name,
                $member->phone,
                $member->card_number,
                $member->point_balance,
                $member->user ? $member->user->name : 'N/A',
                $member->user ? $member->user->email : 'N/A',
                $member->is_active ? trans('all.label.active') : trans('all.label.inactive'),
                $member->created_at ? $member->created_at->format('Y-m-d H:i:s') : '',
            ];
        }
        return collect($memberArray);
    }

    public function headings(): array
    {
        return [
            trans('all.label.name'),
            trans('all.label.phone'),
            trans('all.label.card_number'),
            trans('all.label.point_balance'),
            trans('all.label.user_name'),
            trans('all.label.user_email'),
            trans('all.label.status'),
            trans('all.label.created_at'),
        ];
    }
}
