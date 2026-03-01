<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Compliance\Services\GdprService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'GDPR',
    description: 'General Data Protection Regulation (GDPR) compliance operations'
)]
class GdprController extends Controller
{
    public function __construct(
        private readonly GdprService $gdprService
    ) {
    }

        #[OA\Get(
            path: '/api/gdpr/consent-status',
            operationId: 'getGdprConsentStatus',
            tags: ['GDPR'],
            summary: 'Get user\'s consent status',
            description: 'Retrieve the current consent status for various data processing activities',
            security: [['sanctum' => []]]
        )]
    #[OA\Response(
        response: 200,
        description: 'Successful operation',
        content: new OA\JsonContent(properties: [
        new OA\Property(property: 'consents', type: 'object', properties: [
        new OA\Property(property: 'privacy_policy', type: 'boolean', example: true),
        new OA\Property(property: 'terms', type: 'boolean', example: true),
        new OA\Property(property: 'marketing', type: 'boolean', example: false),
        new OA\Property(property: 'data_retention', type: 'boolean', example: true),
        ]),
        new OA\Property(property: 'dates', type: 'object', properties: [
        new OA\Property(property: 'privacy_policy_accepted_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'terms_accepted_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'marketing_consent_at', type: 'string', format: 'date-time', nullable: true),
        ]),
        ])
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthenticated'
    )]
    public function consentStatus(): JsonResponse
    {
        $user = Auth::user();
        /** @var User $user */

        return response()->json(
            [
                'consents' => [
                    'privacy_policy' => $user->privacy_policy_accepted_at !== null,
                    'terms'          => $user->terms_accepted_at !== null,
                    'marketing'      => $user->marketing_consent_at !== null,
                    'data_retention' => $user->data_retention_consent,
                ],
                'dates' => [
                    'privacy_policy_accepted_at' => $user->privacy_policy_accepted_at,
                    'terms_accepted_at'          => $user->terms_accepted_at,
                    'marketing_consent_at'       => $user->marketing_consent_at,
                ],
            ]
        );
    }

        #[OA\Post(
            path: '/api/gdpr/consent',
            operationId: 'updateGdprConsent',
            tags: ['GDPR'],
            summary: 'Update user\'s consent preferences',
            description: 'Update consent preferences for various data processing activities',
            security: [['sanctum' => []]],
            requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
        new OA\Property(property: 'privacy_policy', type: 'boolean', example: true),
        new OA\Property(property: 'terms', type: 'boolean', example: true),
        new OA\Property(property: 'marketing', type: 'boolean', example: false),
        new OA\Property(property: 'data_retention', type: 'boolean', example: true),
        ]))
        )]
    #[OA\Response(
        response: 200,
        description: 'Consent updated successfully',
        content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Consent preferences updated successfully'),
        ])
    )]
    #[OA\Response(
        response: 422,
        description: 'Validation error',
        content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')
    )]
    public function updateConsent(Request $request): JsonResponse
    {
        $request->validate(
            [
                'privacy_policy' => 'sometimes|boolean',
                'terms'          => 'sometimes|boolean',
                'marketing'      => 'sometimes|boolean',
                'data_retention' => 'sometimes|boolean',
            ]
        );

        $user = Auth::user();
        /** @var User $user */
        $this->gdprService->updateConsent($user, $request->all());

        return response()->json(
            [
                'message' => 'Consent preferences updated successfully',
            ]
        );
    }

        #[OA\Post(
            path: '/api/gdpr/export',
            operationId: 'requestGdprDataExport',
            tags: ['GDPR'],
            summary: 'Request data export (GDPR Article 20)',
            description: 'Request a complete export of all personal data in a machine-readable format',
            security: [['sanctum' => []]]
        )]
    #[OA\Response(
        response: 200,
        description: 'Export request successful',
        content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Data export requested. You will receive an email with your data shortly.'),
        new OA\Property(property: 'preview', type: 'object', properties: [
        new OA\Property(property: 'sections', type: 'array', example: ['personal_info', 'accounts', 'transactions', 'documents'], items: new OA\Items(type: 'string')),
        new OA\Property(property: 'generated_at', type: 'string', format: 'date-time'),
        ]),
        ])
    )]
    #[OA\Response(
        response: 500,
        description: 'Server error',
        content: new OA\JsonContent(properties: [
        new OA\Property(property: 'error', type: 'string', example: 'Failed to process data export request'),
        ])
    )]
    public function requestDataExport(): JsonResponse
    {
        $user = Auth::user();
        /** @var User $user */
        try {
            $data = $this->gdprService->exportUserData($user);

            // In a real application, this would queue a job to generate
            // and email the export to the user
            return response()->json(
                [
                    'message' => 'Data export requested. You will receive an email with your data shortly.',
                    'preview' => [
                        'sections'     => array_keys($data),
                        'generated_at' => now(),
                    ],
                ]
            );
        } catch (Exception $e) {
            return response()->json(
                [
                    'error' => 'Failed to process data export request',
                ],
                500
            );
        }
    }

        #[OA\Post(
            path: '/api/gdpr/delete',
            operationId: 'requestGdprAccountDeletion',
            tags: ['GDPR'],
            summary: 'Request account deletion (GDPR Article 17)',
            description: 'Request complete deletion of account and personal data (right to be forgotten)',
            security: [['sanctum' => []]],
            requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['confirm'], properties: [
        new OA\Property(property: 'confirm', type: 'boolean', example: true, description: 'Confirmation of deletion request'),
        new OA\Property(property: 'reason', type: 'string', maxLength: 500, example: 'No longer using the service'),
        ]))
        )]
    #[OA\Response(
        response: 200,
        description: 'Deletion request successful',
        content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Account deletion request processed. Your account will be deleted within 30 days.'),
        ])
    )]
    #[OA\Response(
        response: 400,
        description: 'Deletion not allowed',
        content: new OA\JsonContent(properties: [
        new OA\Property(property: 'error', type: 'string', example: 'Account cannot be deleted at this time'),
        new OA\Property(property: 'reasons', type: 'array', example: ['Outstanding balance', 'Active loans'], items: new OA\Items(type: 'string')),
        ])
    )]
    #[OA\Response(
        response: 422,
        description: 'Validation error'
    )]
    #[OA\Response(
        response: 500,
        description: 'Server error'
    )]
    public function requestDeletion(Request $request): JsonResponse
    {
        $request->validate(
            [
                'confirm' => 'required|boolean|accepted',
                'reason'  => 'sometimes|string|max:500',
            ]
        );

        $user = Auth::user();
        /** @var User $user */

        // Check if deletion is allowed
        $check = $this->gdprService->canDeleteUserData($user);
        if (! $check['can_delete']) {
            return response()->json(
                [
                    'error'   => 'Account cannot be deleted at this time',
                    'reasons' => $check['reasons'],
                ],
                400
            );
        }

        try {
            // In a real application, this would queue a job and require
            // additional confirmation steps
            $this->gdprService->deleteUserData(
                $user,
                [
                    'reason'                 => $request->reason,
                    'delete_documents'       => true,
                    'anonymize_transactions' => true,
                ]
            );

            return response()->json(
                [
                    'message' => 'Account deletion request processed. Your account will be deleted within 30 days.',
                ]
            );
        } catch (Exception $e) {
            return response()->json(
                [
                    'error' => 'Failed to process deletion request',
                ],
                500
            );
        }
    }

        #[OA\Get(
            path: '/api/gdpr/retention-policy',
            operationId: 'getGdprRetentionPolicy',
            tags: ['GDPR'],
            summary: 'Get data retention policy',
            description: 'Retrieve information about data retention periods and user rights under GDPR'
        )]
    #[OA\Response(
        response: 200,
        description: 'Successful operation',
        content: new OA\JsonContent(properties: [
        new OA\Property(property: 'policy', type: 'object', properties: [
        new OA\Property(property: 'transaction_data', type: 'string', example: '7 years (regulatory requirement)'),
        new OA\Property(property: 'kyc_documents', type: 'string', example: '5 years after account closure'),
        new OA\Property(property: 'audit_logs', type: 'string', example: '3 years'),
        new OA\Property(property: 'marketing_data', type: 'string', example: 'Until consent withdrawn'),
        new OA\Property(property: 'inactive_accounts', type: 'string', example: 'Deleted after 2 years of inactivity'),
        ]),
        new OA\Property(property: 'user_rights', type: 'object', properties: [
        new OA\Property(property: 'access', type: 'string', example: 'You can request a copy of your data at any time'),
        new OA\Property(property: 'rectification', type: 'string', example: 'You can update your personal information'),
        new OA\Property(property: 'erasure', type: 'string', example: 'You can request deletion (subject to legal requirements)'),
        new OA\Property(property: 'portability', type: 'string', example: 'You can export your data in machine-readable format'),
        new OA\Property(property: 'object', type: 'string', example: 'You can object to certain processing activities'),
        ]),
        ])
    )]
    public function retentionPolicy(): JsonResponse
    {
        return response()->json(
            [
                'policy' => [
                    'transaction_data'  => '7 years (regulatory requirement)',
                    'kyc_documents'     => '5 years after account closure',
                    'audit_logs'        => '3 years',
                    'marketing_data'    => 'Until consent withdrawn',
                    'inactive_accounts' => 'Deleted after 2 years of inactivity',
                ],
                'user_rights' => [
                    'access'        => 'You can request a copy of your data at any time',
                    'rectification' => 'You can update your personal information',
                    'erasure'       => 'You can request deletion (subject to legal requirements)',
                    'portability'   => 'You can export your data in machine-readable format',
                    'object'        => 'You can object to certain processing activities',
                ],
            ]
        );
    }
}
