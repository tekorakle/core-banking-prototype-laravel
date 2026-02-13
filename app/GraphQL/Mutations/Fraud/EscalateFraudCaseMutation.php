<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\Fraud;

use App\Domain\Fraud\Models\FraudCase;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class EscalateFraudCaseMutation
{
    /**
     * @param  array<string, mixed>  $args
     */
    public function __invoke(mixed $rootValue, array $args): FraudCase
    {
        $user = Auth::user();

        if (! $user) {
            throw new AuthenticationException('Unauthenticated.');
        }

        /** @var FraudCase|null $fraudCase */
        $fraudCase = FraudCase::query()->find($args['id']);

        if (! $fraudCase) {
            throw new ModelNotFoundException('Fraud case not found.');
        }

        $fraudCase->update([
            'severity'         => $args['severity'],
            'resolution_notes' => $args['notes'] ?? $fraudCase->resolution_notes,
        ]);

        return $fraudCase->fresh() ?? $fraudCase;
    }
}
