<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Privacy;

use App\Domain\Privacy\Contracts\MerkleTreeServiceInterface;
use App\Domain\Privacy\Exceptions\CommitmentNotFoundException;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

/**
 * Privacy API Controller.
 *
 * Handles privacy pool Merkle tree operations for mobile clients.
 *
 * @OA\Tag(
 *     name="Privacy",
 *     description="Privacy pool Merkle tree and proof endpoints"
 * )
 */
class PrivacyController extends Controller
{
    public function __construct(
        private readonly MerkleTreeServiceInterface $merkleService,
    ) {
    }

    /**
     * Get the current Merkle root for a network.
     *
     * @OA\Get(
     *     path="/api/v1/privacy/merkle-root",
     *     operationId="getMerkleRoot",
     *     tags={"Privacy"},
     *     summary="Get the current Merkle root for a privacy pool",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="network",
     *         in="query",
     *         required=true,
     *         description="Blockchain network (polygon, base, arbitrum)",
     *         @OA\Schema(type="string", enum={"polygon", "base", "arbitrum"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Current Merkle root",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="root", type="string", example="0x1234..."),
     *                 @OA\Property(property="network", type="string", example="polygon"),
     *                 @OA\Property(property="leaf_count", type="integer", example=1000),
     *                 @OA\Property(property="tree_depth", type="integer", example=32),
     *                 @OA\Property(property="block_number", type="integer", example=55000000),
     *                 @OA\Property(property="synced_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid network"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function getMerkleRoot(Request $request): JsonResponse
    {
        $network = $request->query('network');

        if (empty($network) || ! is_string($network)) {
            return response()->json([
                'error' => [
                    'code'    => 'ERR_PRIVACY_306',
                    'message' => 'Network parameter is required',
                ],
            ], 400);
        }

        try {
            $root = $this->merkleService->getMerkleRoot($network);

            return response()->json([
                'data' => $root->toArray(),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'error' => [
                    'code'               => 'ERR_PRIVACY_307',
                    'message'            => $e->getMessage(),
                    'supported_networks' => $this->merkleService->getSupportedNetworks(),
                ],
            ], 400);
        }
    }

    /**
     * Get the Merkle proof path for a commitment.
     *
     * @OA\Post(
     *     path="/api/v1/privacy/merkle-path",
     *     operationId="getMerklePath",
     *     tags={"Privacy"},
     *     summary="Get Merkle proof path for a commitment",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"commitment", "network"},
     *             @OA\Property(property="commitment", type="string", description="32-byte hex commitment with 0x prefix"),
     *             @OA\Property(property="network", type="string", enum={"polygon", "base", "arbitrum"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Merkle proof path",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="commitment", type="string"),
     *                 @OA\Property(property="root", type="string"),
     *                 @OA\Property(property="network", type="string"),
     *                 @OA\Property(property="leaf_index", type="integer"),
     *                 @OA\Property(property="siblings", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="path_indices", type="array", @OA\Items(type="integer")),
     *                 @OA\Property(property="proof_depth", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid parameters"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Commitment not found in tree")
     * )
     */
    public function getMerklePath(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'commitment' => 'required|string|regex:/^(0x)?[a-fA-F0-9]{64}$/',
            'network'    => 'required|string',
        ]);

        try {
            $path = $this->merkleService->getMerklePath(
                $validated['commitment'],
                $validated['network']
            );

            return response()->json([
                'data' => $path->toArray(),
            ]);
        } catch (CommitmentNotFoundException $e) {
            return response()->json([
                'error' => [
                    'code'       => $e->getErrorCode(),
                    'message'    => $e->getMessage(),
                    'commitment' => $e->commitment,
                    'network'    => $e->network,
                ],
            ], $e->getHttpStatusCode());
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'error' => [
                    'code'               => 'ERR_PRIVACY_307',
                    'message'            => $e->getMessage(),
                    'supported_networks' => $this->merkleService->getSupportedNetworks(),
                ],
            ], 400);
        }
    }

    /**
     * Verify a commitment against a Merkle proof.
     *
     * @OA\Post(
     *     path="/api/v1/privacy/verify-commitment",
     *     operationId="verifyCommitment",
     *     tags={"Privacy"},
     *     summary="Verify a commitment exists in the tree",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"commitment", "network", "siblings", "path_indices"},
     *             @OA\Property(property="commitment", type="string"),
     *             @OA\Property(property="network", type="string"),
     *             @OA\Property(property="root", type="string"),
     *             @OA\Property(property="leaf_index", type="integer"),
     *             @OA\Property(property="siblings", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="path_indices", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Verification result",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="valid", type="boolean"),
     *                 @OA\Property(property="commitment", type="string"),
     *                 @OA\Property(property="network", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid parameters"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function verifyCommitment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'commitment'     => 'required|string|regex:/^(0x)?[a-fA-F0-9]{64}$/',
            'network'        => 'required|string',
            'root'           => 'required|string|regex:/^(0x)?[a-fA-F0-9]{64}$/',
            'leaf_index'     => 'required|integer|min:0',
            'siblings'       => 'required|array',
            'siblings.*'     => 'required|string|regex:/^(0x)?[a-fA-F0-9]{64}$/',
            'path_indices'   => 'required|array',
            'path_indices.*' => 'required|integer|min:0|max:1',
        ]);

        try {
            $path = \App\Domain\Privacy\ValueObjects\MerklePath::fromArray([
                'commitment'   => $validated['commitment'],
                'root'         => $validated['root'],
                'network'      => $validated['network'],
                'leaf_index'   => $validated['leaf_index'],
                'siblings'     => $validated['siblings'],
                'path_indices' => $validated['path_indices'],
            ]);

            $isValid = $this->merkleService->verifyCommitment($validated['commitment'], $path);

            return response()->json([
                'data' => [
                    'valid'      => $isValid,
                    'commitment' => $validated['commitment'],
                    'network'    => $validated['network'],
                ],
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'error' => [
                    'code'    => 'ERR_PRIVACY_308',
                    'message' => $e->getMessage(),
                ],
            ], 400);
        }
    }

    /**
     * Get supported networks.
     *
     * @OA\Get(
     *     path="/api/v1/privacy/networks",
     *     operationId="getPrivacyNetworks",
     *     tags={"Privacy"},
     *     summary="Get supported privacy pool networks",
     *     @OA\Response(
     *         response=200,
     *         description="Supported networks",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="networks", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="tree_depth", type="integer"),
     *                 @OA\Property(property="provider", type="string")
     *             )
     *         )
     *     )
     * )
     */
    public function getNetworks(): JsonResponse
    {
        return response()->json([
            'data' => [
                'networks'   => $this->merkleService->getSupportedNetworks(),
                'tree_depth' => $this->merkleService->getTreeDepth(),
                'provider'   => $this->merkleService->getProviderName(),
            ],
        ]);
    }

    /**
     * Trigger a Merkle tree sync.
     *
     * @OA\Post(
     *     path="/api/v1/privacy/sync",
     *     operationId="syncMerkleTree",
     *     tags={"Privacy"},
     *     summary="Trigger a Merkle tree sync from the blockchain",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"network"},
     *             @OA\Property(property="network", type="string", enum={"polygon", "base", "arbitrum"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sync completed",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="root", type="string"),
     *                 @OA\Property(property="network", type="string"),
     *                 @OA\Property(property="synced_at", type="string", format="date-time")
     *             ),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid network"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function syncTree(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'network' => 'required|string',
        ]);

        try {
            $root = $this->merkleService->syncTree($validated['network']);

            return response()->json([
                'data'    => $root->toArray(),
                'message' => 'Merkle tree synced successfully',
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'error' => [
                    'code'               => 'ERR_PRIVACY_307',
                    'message'            => $e->getMessage(),
                    'supported_networks' => $this->merkleService->getSupportedNetworks(),
                ],
            ], 400);
        }
    }
}
