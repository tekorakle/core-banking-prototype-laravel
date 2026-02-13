<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\Lending;

use App\Domain\Lending\Models\LoanApplication;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class ApproveLoanMutation
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

        /** @var LoanApplication|null $application */
        $application = LoanApplication::query()->find($args['id']);

        if (! $application) {
            throw new ModelNotFoundException('Loan application not found.');
        }

        $application->update([
            'status'          => 'approved',
            'approved_amount' => $args['approved_amount'],
            'interest_rate'   => $args['interest_rate'],
            'approved_by'     => (string) $user->id,
            'approved_at'     => now(),
        ]);

        return $application->fresh() ?? $application;
    }
}
