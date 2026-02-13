<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Shared\Models\Plugin;
use App\Http\Controllers\Controller;
use App\Infrastructure\Plugins\PluginManager;
use App\Infrastructure\Plugins\PluginSecurityScanner;
use Illuminate\Http\JsonResponse;

class PluginMarketplaceController extends Controller
{
    public function __construct(
        private readonly PluginManager $pluginManager,
        private readonly PluginSecurityScanner $securityScanner,
    ) {
    }

    /**
     * List all installed plugins.
     */
    public function index(): JsonResponse
    {
        $plugins = $this->pluginManager->list();

        return response()->json([
            'data' => $plugins->map(fn (Plugin $p) => [
                'id'           => $p->id,
                'vendor'       => $p->vendor,
                'name'         => $p->name,
                'full_name'    => $p->getFullName(),
                'version'      => $p->version,
                'display_name' => $p->display_name,
                'description'  => $p->description,
                'status'       => $p->status,
                'is_system'    => $p->is_system,
                'permissions'  => $p->permissions,
                'installed_at' => $p->installed_at?->toIso8601String(),
            ]),
            'meta' => [
                'total'  => $plugins->count(),
                'active' => $plugins->where('status', 'active')->count(),
            ],
        ]);
    }

    /**
     * Show a specific plugin.
     */
    public function show(string $id): JsonResponse
    {
        $plugin = Plugin::findOrFail($id);

        return response()->json([
            'data' => [
                'id'              => $plugin->id,
                'vendor'          => $plugin->vendor,
                'name'            => $plugin->name,
                'full_name'       => $plugin->getFullName(),
                'version'         => $plugin->version,
                'display_name'    => $plugin->display_name,
                'description'     => $plugin->description,
                'author'          => $plugin->author,
                'license'         => $plugin->license,
                'homepage'        => $plugin->homepage,
                'status'          => $plugin->status,
                'is_system'       => $plugin->is_system,
                'permissions'     => $plugin->permissions,
                'dependencies'    => $plugin->dependencies,
                'metadata'        => $plugin->metadata,
                'installed_at'    => $plugin->installed_at?->toIso8601String(),
                'activated_at'    => $plugin->activated_at?->toIso8601String(),
                'last_updated_at' => $plugin->last_updated_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Enable a plugin.
     */
    public function enable(string $id): JsonResponse
    {
        $plugin = Plugin::findOrFail($id);
        $result = $this->pluginManager->enable($plugin->vendor, $plugin->name);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Disable a plugin.
     */
    public function disable(string $id): JsonResponse
    {
        $plugin = Plugin::findOrFail($id);
        $result = $this->pluginManager->disable($plugin->vendor, $plugin->name);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Remove a plugin.
     */
    public function destroy(string $id): JsonResponse
    {
        $plugin = Plugin::findOrFail($id);
        $result = $this->pluginManager->remove($plugin->vendor, $plugin->name);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Scan a plugin for security issues.
     */
    public function scan(string $id): JsonResponse
    {
        $plugin = Plugin::findOrFail($id);
        $result = $this->securityScanner->scan($plugin->path);

        return response()->json([
            'plugin'  => $plugin->getFullName(),
            'safe'    => $result['safe'],
            'issues'  => $result['issues'],
            'summary' => $this->securityScanner->summarize($result['issues']),
        ]);
    }

    /**
     * Discover new plugins from the filesystem.
     */
    public function discover(): JsonResponse
    {
        $result = $this->pluginManager->discover();

        return response()->json([
            'discovered' => $result['discovered'],
            'new'        => $result['new'],
        ]);
    }
}
