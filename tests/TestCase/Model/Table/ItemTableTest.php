<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ItemTable;
use App\Test\Fixture\ItemFixture;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ItemTable Test Case
 */
class ItemTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ItemTable
     */
    protected $Item;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        ItemFixture::class
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Item') ? [] : ['className' => ItemTable::class];
        $this->Item = $this->getTableLocator()->get('Item', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Item);

        parent::tearDown();
    }

    public function testValidationDescricaoCompletaObrigatoria(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\ItemTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
