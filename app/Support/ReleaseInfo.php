<?php

namespace App\Support;

class ReleaseInfo
{
    public static function current(): array
    {
        $branch = config('release.branch');
        $target = match (true) {
            $branch === 'master' => 'production',
            $branch === 'qa' => 'qa',
            str_starts_with($branch, 'feature/') => 'preview',
            default => 'local',
        };

        return [
            'version' => config('release.version'),
            'message' => config('release.message'),
            'branch' => $branch,
            'commit' => config('release.commit'),
            'build' => config('release.build'),
            'environment' => app()->environment(),
            'branch_target' => $target,
        ];
    }
}
