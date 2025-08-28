<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Service\RelatorioService;
use App\Test\Fixture\CatmatFixture;
use App\Test\Fixture\FornecedorFixture;
use App\Test\Fixture\ItemFixture;
use App\Test\Fixture\LoteFixture;
use App\Test\Fixture\LoteMovimentacaoFixture;
use App\Test\Fixture\MovimentacaoFixture;
use App\Test\Fixture\SetorFixture;
use App\Util\ExcelExporter;
use Cake\I18n\FrozenDate;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use DateMalformedStringException;
use PhpOffice\PhpSpreadsheet\Exception;

class RelatorioServiceTest extends TestCase
{
    /**
     * Fixtures a serem carregados para testes de relatório.
     * Mantém os registros existentes nos arquivos de fixture.
     * @var array<string>
     */
    protected array $fixtures = [
        SetorFixture::class,
        FornecedorFixture::class,
        ItemFixture::class,
        CatmatFixture::class,
        LoteFixture::class,
        MovimentacaoFixture::class,
        LoteMovimentacaoFixture::class,
    ];

    private RelatorioService $service;

    public function setUp(): void
    {
        parent::setUp();
        Log::drop('default');
        $this->service = new RelatorioService();
    }

    public function testGetPosicaoEstoqueAtualSemFiltro(): void
    {
        $resultado = $this->service->getPosicaoEstoqueAtual();
        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('itens', $resultado);
        $this->assertArrayHasKey('valor_total_estoque', $resultado);
        $this->assertArrayHasKey('soma_por_tipo', $resultado);

        $soma = 0.0;
        foreach ($resultado['itens'] as $item) {
            $esperado = round($item['quantidade'] * $item['valor_unitario'], 2);
            $this->assertEquals($esperado, $item['valor_total']);
            $soma += $item['valor_total'];
        }
        $this->assertEquals(round($soma, 2), $resultado['valor_total_estoque']);

        $this->assertEquals(
            ['material', 'farmacologico', 'medicamento_vet'],
            array_keys($resultado['soma_por_tipo'])
        );
        foreach ($resultado['soma_por_tipo'] as $valor) {
            $this->assertIsInt($valor);
            $this->assertGreaterThanOrEqual(0, $valor);
        }
    }

    public function testGetPosicaoEstoqueAtualComFiltro(): void
    {
        $resultado = $this->service->getPosicaoEstoqueAtual('material', 'unidade');
        foreach ($resultado['itens'] as $item) {
            $this->assertStringContainsString('unidade', strtolower($item['unidade_medida']));
            $tipo = TableRegistry::getTableLocator()
                ->get('Item')
                ->get($item['item_id'])
                ->tipo_item;
            $this->assertEquals('material', $tipo);
        }
        $soma = $resultado['soma_por_tipo'];
        $this->assertGreaterThan(0, $soma['material']);
        $this->assertEquals(0, $soma['farmacologico']);
        $this->assertEquals(0, $soma['medicamento_vet']);
    }

    public function testDmyToFrozenReturnsNullForNull(): void
    {
        $this->assertNull($this->service->dmyToFrozen(null));
    }

    public function testDmyToFrozenReturnsNullForEmptyString(): void
    {
        $this->assertNull($this->service->dmyToFrozen(''));
    }

    public function testDmyToFrozenReturnsFrozenDate(): void
    {
        $frozen = $this->service->dmyToFrozen('15-08-2021');
        $this->assertInstanceOf(FrozenDate::class, $frozen);
        $this->assertEquals('2021-08-15', $frozen->format('Y-m-d'));
    }

    // ==== Testes de integração para relatórios usando fixtures ==== //

    public function testGetRelatorioEntradasComFixtures(): void
    {
        // Carrega dados de fixture
        $movTable = TableRegistry::getTableLocator()->get('Movimentacao');
        // Fixtures devem conter ao menos uma 'entrada' válida
        $data = ['data_inicio' => '01-01-2025'];

        $result = $this->service->getRelatorioEntradas($data);

        // Verifica que ao menos um registro foi retornado
        $this->assertNotEmpty($result);

        // Valida formato de cada linha
        foreach ($result as $row) {
            $this->assertArrayHasKey('nome_item', $row);
            $this->assertArrayHasKey('quantidade_entrada', $row);
            $this->assertArrayHasKey('data_entrada', $row);
            $this->assertMatchesRegularExpression('/\d{2}-\d{2}-\d{4}/', $row['data_entrada']);
            $this->assertArrayHasKey('validade', $row);
        }
    }

    public function testGetRelatorioEntradasSemFiltrosRetornaTodosTipos(): void
    {
        $all = $this->service->getRelatorioEntradas();
        // Conta total de entradas no fixture Movimentacao
        $movCount = TableRegistry::getTableLocator()->get('Movimentacao')
            ->find()
            ->where(['tipo_movimentacao' => 'entrada'])
            ->count();

        // Cada movimentacao pode gerar múltiplas linhas se tiver vários lotes
        $this->assertGreaterThanOrEqual($movCount, count($all));
    }

    public function testGetRelatorioSaidasComFixtures(): void
    {
        $result = $this->service->getRelatorioSaidas();
        $this->assertNotEmpty($result);
        foreach ($result as $row) {
            $this->assertArrayHasKey('nome_item', $row);
            $this->assertArrayHasKey('quantidade_retirada', $row);
            $this->assertArrayHasKey('data_saida', $row);
            $this->assertMatchesRegularExpression('/\d{2}-\d{2}-\d{4}/', $row['data_saida']);
            $this->assertArrayHasKey('valor_total', $row);
        }
    }

    public function testDmyToFrozenReturnsNormalizedDateForInvalidInput(): void
    {
        // A DateTime::createFromFormat irá normalizar datas inválidas
        $frozen = $this->service->dmyToFrozen('31-02-2020');
        $this->assertInstanceOf(FrozenDate::class, $frozen);
        // '31-02-2020' vira efetivamente '02-03-2020'
        $this->assertEquals('2020-03-02', $frozen->format('Y-m-d'));
    }

    public function testGetRelatorioSaidasFiltroPorSetor(): void
    {
        // obtém o código (PK) de um setor no fixture
        $setorCodigo = TableRegistry::getTableLocator()
            ->get('Setor')
            ->find()
            ->select('codigo')
            ->first()
            ->codigo;

        $result = $this->service->getRelatorioSaidas(['setor_id' => $setorCodigo]);

        // Garante que sempre retorna um array
        $this->assertIsArray($result);

        // Se houver linhas, cada uma deve conter o nome do setor
        foreach ($result as $row) {
            $this->assertArrayHasKey('setor', $row);
            $this->assertNotEmpty($row['setor'], 'Nome do setor não deveria estar vazio');
        }
    }

    /**
     * @throws DateMalformedStringException
     */
    public function testEstatisticasConsumoSimples()
    {
        $item = TableRegistry::getTableLocator()->get('Item')
            ->find()
            ->where(['unidade' => 'ml'])
            ->contain(['Lote'])
            ->first();

        $lote = TableRegistry::getTableLocator()->get('Lote')
            ->find()
            ->contain(['Item'])
            ->where(['item_id' => $item->id])
            ->all();

        $this->assertNotEmpty($item, 'Nenhum item com unidade "ml" encontrado.');

        $this->assertNotEmpty($lote, 'Item encontrado não possui lote associado.');

        $inicio = '2024-06-01';
        $fim = '2025-06-30';

        $result = $this->service->getEstatisticasConsumo([
            'periodo_inicio' => $inicio,
            'periodo_fim' => $fim,
            'completo' => false,
        ]);

        $this->assertArrayHasKey('itens', $result);
        $this->assertNotEmpty($result['itens'], 'Nenhum item retornado');

        $itemRetornado = current(array_filter($result['itens'], fn($i) => $i['item_id'] === $item->id));
        $this->assertNotEmpty($itemRetornado, 'Item esperado não encontrado no resultado');

        $this->assertEquals($item->nome, $itemRetornado['nome']);
        $this->assertArrayHasKey('quantidade_consumida', $itemRetornado);
        $this->assertArrayHasKey('estoque_atual', $itemRetornado);
        $this->assertArrayHasKey('tempo_estimado_uso_dias', $itemRetornado);

        $quantidades = array_column($result['itens'], 'quantidade_consumida');
        $this->assertEquals(max($quantidades), $result['maior_consumo']['quantidade']);
        $this->assertEquals(min($quantidades), $result['menor_consumo']['quantidade']);
    }

    /**
     * @throws DateMalformedStringException
     */
    public function testEstatisticasComFiltroDeUnidade()
    {
        $item = TableRegistry::getTableLocator()->get('Item')
            ->find()
            ->where(['unidade LIKE' => '%ml%'])
            ->contain(['Lote'])
            ->first();

        $catmat = TableRegistry::getTableLocator()->get('Catmat')
            ->find()
            ->contain(['Item'])
            ->where(['item_id' => $item->id])
            ->first();

        $this->assertNotEmpty($item, 'Nenhum item com unidade contendo "ml" encontrado.');
        $this->assertNotEmpty($catmat, 'Item encontrado não possui CATMAT vinculado.');

        $result = $this->service->getEstatisticasConsumo([
            'unidade' => 'ml',
            'completo' => true,
        ]);

        $this->assertArrayHasKey('itens', $result);
        $this->assertNotEmpty($result['itens'], 'Nenhum item retornado');

        $itemRetornado = current(array_filter($result['itens'], fn($i) => $i['item_id'] === $item->id));
        $this->assertNotEmpty($itemRetornado, 'Item esperado não encontrado no resultado');

        $this->assertStringContainsStringIgnoringCase('ml', $itemRetornado['unidade']);
        $this->assertEquals($catmat->codigo_catmat, $itemRetornado['codigo_catmat']);
        $this->assertEquals($item->descricao_completa, $itemRetornado['descricao_completa']);
    }

    /**
     * @throws DateMalformedStringException
     */
    public function testGetEstatisticasConsumoMensal(): void
    {
        $filtros = [
            'periodo_inicio' => '2024-02-01',
            'periodo_fim' => '2024-03-16',
            'tipo_item' => null,
            'unidade' => null,
            'completo' => false
        ];

        $resultado = $this->service->getEstatisticasConsumoMensal($filtros);

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('2024-02', $resultado);
        $this->assertArrayHasKey('2024-03', $resultado);

        foreach ($resultado as $mes => $dados) {
            $this->assertArrayHasKey('itens', $dados);
            $this->assertArrayHasKey('maior_consumo', $dados);
            $this->assertArrayHasKey('menor_consumo', $dados);
        }
    }

    /**
     * @throws DateMalformedStringException
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function testExportarEstatisticasConsumoMensalParaExcel(): void
    {
        $filtros = [
            'periodo_inicio' => '2024-02-01',
            'periodo_fim' => '2024-03-31',
            'tipo_item' => null,
            'unidade' => null,
            'completo' => false
        ];

        $caminhoArquivo = $this->service->exportarEstatisticasConsumoMensalParaExcel($filtros);

        $this->assertIsString($caminhoArquivo);
        $this->assertFileExists($caminhoArquivo);

        // Limpa o arquivo gerado após o teste
        unlink($caminhoArquivo);
    }

    public function testExportarEstatisticasConsumoMensalParaExcelComDadosValidos(): void
    {
        // Mock do service para interceptar o getEstatisticasConsumoMensal
        $service = $this->getMockBuilder(RelatorioService::class)
            ->onlyMethods(['getEstatisticasConsumoMensal'])
            ->getMock();

        $filtros = [
            'periodo_inicio' => '2024-01-01',
            'periodo_fim' => '2024-06-30',
            'tipo_item' => 'medicamento_vet',
            'unidade' => 'ml',
            'completo' => true,
        ];

        $dadosMock = [
            '2024-01' => [
                [
                    'nome' => 'Dipirona',
                    'quantidade_consumida' => 100,
                    'estoque_atual' => 50,
                    'tempo_estimado_uso_dias' => 15,
                ]
            ],
            '2024-02' => [
                [
                    'nome' => 'Amoxicilina',
                    'quantidade_consumida' => 200,
                    'estoque_atual' => 70,
                    'tempo_estimado_uso_dias' => 10,
                ]
            ]
        ];

        $service->expects($this->once())
            ->method('getEstatisticasConsumoMensal')
            ->with($filtros)
            ->willReturn($dadosMock);

        // Mock estático do ExcelExporter
        $exporter = $this->getMockBuilder(ExcelExporter::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['gerarPlanilhaComAbas'])
            ->getMock();

        // Usa função anônima para substituir temporariamente o comportamento estático
        $caminhoFalso = '/tmp/relatorio_consumo.xlsx';

        // Substitui temporariamente o método estático (requer PHP 8.1+ com Patchwork ou outro mock de métodos estáticos)
        // Para simplificar aqui, vamos mockar fora da estrutura tradicional (exemplo abaixo):
        ExcelExporter::setMock(function ($dados, $headersFn, $linhaFn) use ($caminhoFalso) {
            return $caminhoFalso;
        });

        $resultado = $service->exportarEstatisticasConsumoMensalParaExcel($filtros);

        $this->assertIsString($resultado);
        $this->assertEquals($caminhoFalso, $resultado);
    }

    /**
     * @throws DateMalformedStringException
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function testExportarEstatisticasConsumoMensalParaExcelSemDados(): void
    {
        $service = $this->getMockBuilder(RelatorioService::class)
            ->onlyMethods(['getEstatisticasConsumoMensal'])
            ->getMock();

        $filtros = [
            'periodo_inicio' => '2024-01-01',
            'periodo_fim' => '2024-06-30',
            'tipo_item' => 'medicamento_vet',
            'unidade' => 'ml',
            'completo' => true,
        ];

        $service->expects($this->once())
            ->method('getEstatisticasConsumoMensal')
            ->with($filtros)
            ->willReturn([]);

        ExcelExporter::setMock(fn() => '/tmp/relatorio_vazio.xlsx');

        $resultado = $service->exportarEstatisticasConsumoMensalParaExcel($filtros);

        $this->assertIsString($resultado);
        $this->assertStringContainsString('relatorio', $resultado);
    }
}
