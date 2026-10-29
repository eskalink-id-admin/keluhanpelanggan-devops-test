<?php

// Source-controlled changes for feature branches. Build metadata travels with the artifact.
return array_replace([
    'version' => '1.1.0',
    'message' => 'Rilis 1.1.0: ringkasan catatan tersedia untuk verifikasi deployment.',
    'simulate_failure' => false,
    'branch' => 'local',
    'commit' => 'untracked',
    'build' => 'local',
], is_file(base_path('release.json'))
    ? json_decode(file_get_contents(base_path('release.json')), true, 512, JSON_THROW_ON_ERROR)
    : []);
