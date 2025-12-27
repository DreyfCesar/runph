<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Memory;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Runph\Services\Memory\SharedMemory;
use stdClass;

class SharedMemoryTest extends TestCase
{
    private SharedMemory $memory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->memory = new SharedMemory();
    }

    public function testConstructorInitializesEmptyMemory(): void
    {
        $memory = new SharedMemory();

        $this->assertFalse($memory->has('any-key'));
    }

    public function testConstructorAcceptsInitialData(): void
    {
        $initialData = ['key1' => 'value1', 'key2' => 42];
        $memory = new SharedMemory($initialData);

        $this->assertTrue($memory->has('key1'));
        $this->assertSame('value1', $memory->get('key1'));
        $this->assertTrue($memory->has('key2'));
        $this->assertSame(42, $memory->get('key2'));
    }

    public function testSetStoresValue(): void
    {
        $this->memory->set('test-key', 'test-value');

        $this->assertTrue($this->memory->has('test-key'));
        $this->assertSame('test-value', $this->memory->get('test-key'));
    }

    public function testSetOverwritesExistingValue(): void
    {
        $this->memory->set('key', 'original');
        $this->memory->set('key', 'updated');

        $this->assertSame('updated', $this->memory->get('key'));
    }

    #[DataProvider('variousDataTypsProvider')]
    public function testSetHandlesVariousDataTypes(mixed $value): void
    {
        $this->memory->set('key', $value);

        $this->assertSame($value, $this->memory->get('key'));
    }

    /**
     * @return array<string, mixed[]>
     */
    public static function variousDataTypsProvider(): array
    {
        return [
            'string' => ['string value'],
            'integer' => [42],
            'float' => [3.14],
            'boolean true' => [true],
            'boolean false' => [false],
            'null' => [null],
            'array' => [['nested', 'array']],
            'object' => [new stdClass()],
        ];
    }

    public function testGetReturnsStoredValue(): void
    {
        $this->memory->set('existing-key', 'stored-value');

        $result = $this->memory->get('existing-key');

        $this->assertSame('stored-value', $result);
    }

    public function testGetReturnsNullForNonExistentKey(): void
    {
        $result = $this->memory->get('non-existent-key');

        $this->assertNull($result);
    }

    public function testGetReturnsDefaultValueForNonExistentKey(): void
    {
        $result = $this->memory->get('non-existent-key', 'default-value');

        $this->assertSame('default-value', $result);
    }

    public function testGetReturnsStoredNullValue(): void
    {
        $this->memory->set('null-key', null);

        $result = $this->memory->get('null-key');

        $this->assertNull($result);
    }

    public function testHasReturnsTrueForExistingKey(): void
    {
        $this->memory->set('existing-key', 'value');

        $this->assertTrue($this->memory->has('existing-key'));
    }

    public function testHasReturnsFalseForNonExistentKey(): void
    {
        $this->assertFalse($this->memory->has('non-existent-key'));
    }

    public function testHasReturnsTrueForNullValue(): void
    {
        $this->memory->set('null-key', null);

        $this->assertTrue($this->memory->has('null-key'));
    }

    public function testDeleteRemovesExistingKey(): void
    {
        $this->memory->set('key-to-delete', 'value');

        $this->memory->delete('key-to-delete');

        $this->assertFalse($this->memory->has('key-to-delete'));
        $this->assertNull($this->memory->get('key-to-delete'));
    }

    public function testDeleteNonExistentKeyDoesNotThrowException(): void
    {
        $this->memory->delete('non-existent-key');

        $this->assertFalse($this->memory->has('non-existent-key'));
    }

    public function testDeleteMultipleTimes(): void
    {
        $this->memory->set('key', 'value');
        $this->memory->delete('key');
        $this->memory->delete('key');

        $this->assertFalse($this->memory->has('key'));
    }

    public function testMultipleOperationsWorkCorrectly(): void
    {
        $this->memory->set('key1', 'value1');
        $this->memory->set('key2', 'value2');
        $this->memory->set('key3', 'value3');

        $this->assertTrue($this->memory->has('key1'));
        $this->assertTrue($this->memory->has('key2'));
        $this->assertTrue($this->memory->has('key3'));

        $this->memory->delete('key2');

        $this->assertTrue($this->memory->has('key1'));
        $this->assertFalse($this->memory->has('key2'));
        $this->assertTrue($this->memory->has('key3'));

        $this->assertSame('value1', $this->memory->get('key1'));
        $this->assertSame('value3', $this->memory->get('key3'));
    }

    public function testMemoryIsolationBetweenInstances(): void
    {
        $memory1 = new SharedMemory();
        $memory2 = new SharedMemory();

        $memory1->set('key', 'value1');
        $memory2->set('key', 'value2');

        $this->assertSame('value1', $memory1->get('key'));
        $this->assertSame('value2', $memory2->get('key'));
    }

    public function testEmptyStringAsKey(): void
    {
        $this->memory->set('', 'empty-key-value');

        $this->assertTrue($this->memory->has(''));
        $this->assertSame('empty-key-value', $this->memory->get(''));
    }

    public function testNumericStringAsKey(): void
    {
        $this->memory->set('123', 'numeric-key-value');

        $this->assertTrue($this->memory->has('123'));
        $this->assertSame('numeric-key-value', $this->memory->get('123'));
    }
}
