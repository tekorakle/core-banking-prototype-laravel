<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\Compliance;

use App\Domain\Compliance\Models\ComplianceAlert;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TriggerAmlCheckMutation
{
    /**
     * @param  array<string, mixed>  $args
     */
    public function __invoke(mixed $rootValue, array $args): ComplianceAlert
    {
        $user = Auth::user();

        if (! $user) {
            throw new AuthenticationException('Unauthenticated.');
        }

        return ComplianceAlert::create([
            'alert_id'    => Str::uuid()->toString(),
            'type'        => $args['check_type'] ?? 'aml_screening',
            'severity'    => 'medium',
            'status'      => 'open',
            'title'       => 'AML Check Triggered',
            'description' => "AML check for {$args['entity_type']} {$args['entity_id']}",
            'source'      => 'graphql',
            'entity_type' => $args['entity_type'],
            'entity_id'   => $args['entity_id'],
            'detected_at' => now(),
        ]);
    }
}
