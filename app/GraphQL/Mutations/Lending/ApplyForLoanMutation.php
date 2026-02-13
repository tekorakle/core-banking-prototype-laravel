<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\Lending;

use App\Domain\Lending\Models\LoanApplication;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;

class ApplyForLoanMutation
{
    /**
     * @param  array<string, mixed>  $args
     */
    public function __invoke(mixed $rootValue, array $args): LoanApplication
    {
        $user = Auth::user();

        if (! $user) {
            throw new AuthenticationException('Unauthenticated.');
        }

        return LoanApplication::create([
            'borrower_id'      => (string) $user->id,
            'requested_amount' => $args['requested_amount'],
            'term_months'      => $args['term_months'],
            'purpose'          => $args['purpose'],
            'status'           => 'submitted',
            'submitted_at'     => now(),
        ]);
    }
}
