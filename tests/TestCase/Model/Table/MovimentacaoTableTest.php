<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MovimentacaoTable;
use App\Test\Fixture\ItemFixture;
use App\Test\Fixture\MovimentacaoFixture;
use App\Test\Fixture\SetorFixture;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MovimentacaoTable Test Case
 */
class MovimentacaoTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\MovimentacaoTable
     */
    protected $Movimentacao;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        MovimentacaoFixture::class,
        LoteFixture::class,
        LoteMovimentacaoFixture::class,
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Movimentacao') ? [] : ['className' => MovimentacaoTable::class];
        $this->Movimentacao = $this->getTableLocator()->get('Movimentacao', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Movimentacao);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\MovimentacaoTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \App\Model\Table\MovimentacaoTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
