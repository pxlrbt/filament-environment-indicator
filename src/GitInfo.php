<?php

namespace pxlrbt\FilamentEnvironmentIndicator;

use Throwable;

/**
 * Lazily resolves git information for the environment badge.
 *
 * Each lookup shells out via exec() at most once and caches the result.
 * exec() may be disabled or fail, so every lookup degrades to null.
 */
class GitInfo
{
    /** @var array<string, string|null> */
    protected array $cache = [];

    /**
     * The current branch name (e.g. "main"), or null when detached/unavailable.
     */
    public function branch(): ?string
    {
        return $this->run('branch', 'git branch --show-current');
    }

    /**
     * The nearest tag reachable from HEAD (e.g. "2.2.0"), or null when no tag exists.
     */
    public function tag(): ?string
    {
        return $this->run('tag', 'git describe --tags --abbrev=0');
    }

    /**
     * The short commit hash of HEAD (e.g. "12345678"), or null when unavailable.
     */
    public function hash(): ?string
    {
        return $this->run('hash', 'git rev-parse --short HEAD');
    }

    protected function run(string $key, string $command): ?string
    {
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }

        try {
            $result = trim((string) exec($command));

            return $this->cache[$key] = ($result === '' ? null : $result);
        } catch (Throwable $th) {
            return $this->cache[$key] = null;
        }
    }
}
