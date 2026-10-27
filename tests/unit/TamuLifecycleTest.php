<?php

namespace Tests\Unit;

use App\Models\TamuModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class TamuLifecycleTest extends CIUnitTestCase
{
    private TamuModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new TamuModel();
    }

    public function testModelSoftDeleteConfiguration(): void
    {
        $this->assertTrue(
            $this->getPrivateProperty($this->model, 'useSoftDeletes'),
            'TamuModel should enable $useSoftDeletes'
        );

        $this->assertSame(
            'deleted_at',
            $this->getPrivateProperty($this->model, 'deletedField'),
            'TamuModel should use deleted_at as $deletedField'
        );

        $allowedFields = $this->getPrivateProperty($this->model, 'allowedFields');
        $this->assertContains('is_archived', $allowedFields, 'is_archived must be in $allowedFields');
        $this->assertContains('deleted_at', $allowedFields, 'deleted_at must be in $allowedFields');
    }

    public function testLifecycleMethodSignaturesExist(): void
    {
        $this->assertTrue(method_exists($this->model, 'arsipkan'));
        $this->assertTrue(method_exists($this->model, 'batalArsip'));
        $this->assertTrue(method_exists($this->model, 'arsipkanSebelumTahun'));
        $this->assertTrue(method_exists($this->model, 'pulihkan'));
        $this->assertTrue(method_exists($this->model, 'hapusPermanen'));
        $this->assertTrue(method_exists($this->model, 'countAktif'));
        $this->assertTrue(method_exists($this->model, 'countArsip'));
        $this->assertTrue(method_exists($this->model, 'countSampah'));
    }

    public function testNormalizeCountsAlwaysPreservesAllLabels(): void
    {
        $labels   = ['Laki-laki', 'Perempuan'];
        $existing = [
            ['label' => 'Perempuan', 'count' => 5],
        ];

        // Call protected normalizeCounts using Reflection
        $method = new \ReflectionMethod(TamuModel::class, 'normalizeCounts');
        $method->setAccessible(true);
        $result = $method->invoke($this->model, $labels, $existing);

        $this->assertCount(2, $result);
        $this->assertSame(['label' => 'Laki-laki', 'count' => 0], $result[0]);
        $this->assertSame(['label' => 'Perempuan', 'count' => 5], $result[1]);
    }
}
