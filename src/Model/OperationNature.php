<?php

namespace App\Model;

enum OperationNature
{
    case DEBIT;
    case CREDIT;
    case BALANCE;

    public function value(): string
    {
        return match ($this) {
            OperationNature::DEBIT => 'DEBIT',
            OperationNature::CREDIT => 'CREDIT',
            OperationNature::BALANCE => 'BALANCE'
        };
    }

    public function code(): string
    {
        return match ($this) {
            OperationNature::DEBIT => '0',
            OperationNature::CREDIT => '1',
            OperationNature::BALANCE => '2'
        };
    }
}