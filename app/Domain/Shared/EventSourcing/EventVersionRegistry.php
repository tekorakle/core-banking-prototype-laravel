<?php

declare(strict_types=1);

namespace App\Domain\Shared\EventSourcing;

/**
 * Tracks event versions and their registered upcasters.
 *
 * Maintains a registry of all known event versions and the upcasters
 * needed to transform events between versions.
 */
class EventVersionRegistry
{
    /** @var array<string, EventUpcasterInterface[]> Indexed by event class */
    private array $upcasters = [];

    /** @var array<string, int> Current versions by event class */
    private array $currentVersions = [];

    /**
     * Register an upcaster for an event class.
     */
    public function register(EventUpcasterInterface $upcaster): void
    {
        $key = $upcaster->eventClass();
        $this->upcasters[$key][] = $upcaster;

        // Track the highest version we can upcast to
        $current = $this->currentVersions[$key] ?? 1;
        $this->currentVersions[$key] = max($current, $upcaster->toVersion());
    }

    /**
     * Get upcasters for an event class, ordered by version.
     *
     * @return EventUpcasterInterface[]
     */
    public function getUpcasters(string $eventClass): array
    {
        $upcasters = $this->upcasters[$eventClass] ?? [];

        usort($upcasters, fn (EventUpcasterInterface $a, EventUpcasterInterface $b) => $a->fromVersion() <=> $b->fromVersion());

        return $upcasters;
    }

    /**
     * Get the current (latest) version for an event class.
     */
    public function getCurrentVersion(string $eventClass): int
    {
        return $this->currentVersions[$eventClass] ?? 1;
    }

    /**
     * Check if an event class has registered upcasters.
     */
    public function hasUpcasters(string $eventClass): bool
    {
        return ! empty($this->upcasters[$eventClass]);
    }

    /**
     * Get all registered event classes with their versions.
     *
     * @return array<string, array{current_version: int, upcaster_count: int}>
     */
    public function getAllVersions(): array
    {
        $versions = [];

        foreach ($this->currentVersions as $eventClass => $version) {
            $versions[$eventClass] = [
                'current_version' => $version,
                'upcaster_count'  => count($this->upcasters[$eventClass] ?? []),
            ];
        }

        return $versions;
    }

    /**
     * Get a chain of upcasters to transform an event from one version to the latest.
     *
     * @return EventUpcasterInterface[]
     */
    public function getUpcastChain(string $eventClass, int $fromVersion): array
    {
        $chain = [];
        $currentVersion = $fromVersion;
        $upcasters = $this->getUpcasters($eventClass);

        foreach ($upcasters as $upcaster) {
            if ($upcaster->fromVersion() === $currentVersion) {
                $chain[] = $upcaster;
                $currentVersion = $upcaster->toVersion();
            }
        }

        return $chain;
    }
}
