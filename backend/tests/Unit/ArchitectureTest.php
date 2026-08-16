<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ArchitectureTest extends TestCase
{
    public static function forbiddenControllerDependencies(): array
    {
        return [
            ['::query('],
            ['::create('],
            ['DB::'],
        ];
    }

    #[DataProvider('forbiddenControllerDependencies')]
    public function test_api_controllers_do_not_access_persistence_directly(string $forbidden): void
    {
        foreach (File::allFiles(app_path('Http/Controllers/Api')) as $file) {
            $this->assertStringNotContainsString(
                $forbidden,
                $file->getContents(),
                "{$file->getFilename()} must delegate persistence to an application use case."
            );
        }
    }
}
