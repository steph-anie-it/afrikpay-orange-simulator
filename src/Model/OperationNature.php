<?php

namespace App\Model;

enum OperationNature
{
    case DEBIT;
    case CREDIT;
    public function value(): string
    {
        return match ($this) {
            OperationNature::DEBIT => 'DEBIT',
            OperationNature::CREDIT => 'CREDIT'
        };
    }

    public function code(): string
    {
        return match ($this) {
            OperationNature::DEBIT => '0',
            OperationNature::CREDIT => '1'
        };
    }
}