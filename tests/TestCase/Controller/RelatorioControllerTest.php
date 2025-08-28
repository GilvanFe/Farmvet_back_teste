<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Test\Fixture\CatmatFixture;
use App\Test\Fixture\ItemFixture;
use App\Test\Fixture\LoteFixture;
use App\Test\Fixture\MovimentacaoFixture;
use App\Test\Fixture\LoteMovimentacaoFixture;
use App\Test\Fixture\FornecedorFixture;
use App\Test\Fixture\SetorFixture;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class RelatorioControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures necessários para cobrir estoque e movimentações
     * @var array<string>
     */
    public array $fixtures = [
        SetorFixture::class,
        FornecedorFixture::class,
        ItemFixture::class,
        CatmatFixture::class,
        LoteFixture::class,
        MovimentacaoFixture::class,
        LoteMovimentacaoFixture::class,
    ];

    /**
     * /relatorio/posicao-atual
     */
    public function testPosicaoAtualSemParametros(): void
    {
        $this->get('/relatorio/posicao-atual');
        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $body = json_decode((string)$this->_response->getBody(), true);
        $this->assertTrue($body['success']);
        $this->assertArrayHasKey('data', $body);
        $this->assertArrayHasKey('itens', $body['data']);
        $this->assertArrayHasKey('valor_total_estoque', $body['data']);
        $this->assertArrayHasKey('soma_por_tipo', $body['data']);
    }

    public function testPosicaoAtualParametroInvalido(): void
    {
        // tipo_item numérico
        $this->get('/relatorio/posicao-atual?tipo_item=14');
        $this->assertResponseCode(400);

        // tipo_item fora do enum
        $this->get('/relatorio/posicao-atual?tipo_item=medicamento_veterinario');
        $this->assertResponseCode(400);
    }

    public function testPosicaoAtualComFiltroTipo(): void
    {
        $this->get('/relatorio/posicao-atual?tipo_item=material');
        $this->assertResponseOk();

        $body = json_decode((string)$this->_response->getBody(), true);
        // soma_por_tipo vem com 'material' na primeira posição
        $firstKey = array_key_first($body['data']['soma_por_tipo']);
        $this->assertEquals('material', $firstKey);
    }

    /**
     * /relatorio?tipo=...
     */
    public function testGetRelatorioMovimentacaoSemTipo(): void
    {
        $this->get('/relatorio');
        $this->assertResponseCode(400);
    }

    public function testGetRelatorioMovimentacaoTipoInvalido(): void
    {
        $this->get('/relatorio?tipo=foo');
        $this->assertResponseCode(400);
    }

    public function testRelatorioEntradasMissingDates(): void
    {
        // falta data_inicio e data_fim
        $this->get('/relatorio?tipo=entrada');
        $this->assertResponseCode(400);
    }

    public function testRelatorioSaidasMissingDates(): void
    {
        // falta data_inicio e data_fim
        $this->get('/relatorio?tipo=saida');
        $this->assertResponseCode(400);
    }

    public function testRelatorioEntradasSuccess(): void
    {
        // Use um intervalo que exista nos fixtures, ex: mov.data = '2025-01-01'
        $this->get('/relatorio?tipo=entrada&data_inicio=01-01-2025&data_fim=31-12-2025');
        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $body = json_decode((string)$this->_response->getBody(), true);
        $this->assertTrue($body['success']);
        $this->assertArrayHasKey('data', $body);
        $this->assertIsArray($body['data']);

        if (!empty($body['data'])) {
            $row = $body['data'][0];
            $this->assertArrayHasKey('nome_item', $row);
            $this->assertArrayHasKey('quantidade_entrada', $row);
            $this->assertArrayHasKey('data_entrada', $row);
        }
    }

    public function testRelatorioSaidasSuccess(): void
    {
        $this->get('/relatorio?tipo=saida&data_inicio=01-01-2025&data_fim=31-12-2025');
        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $body = json_decode((string)$this->_response->getBody(), true);
        $this->assertTrue($body['success']);
        $this->assertArrayHasKey('data', $body);
        $this->assertIsArray($body['data']);

        if (!empty($body['data'])) {
            $row = $body['data'][0];
            $this->assertArrayHasKey('nome_item', $row);
            $this->assertArrayHasKey('quantidade_retirada', $row);
            $this->assertArrayHasKey('data_saida', $row);
        }
    }

    public function testEstatisticasConsumoComCompletoFalse(): void
    {
        $this->get('/relatorio/consumo?periodo_inicio=2025-06-01&periodo_fim=2025-06-30&completo=false');
        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $json = json_decode((string)$this->_response->getBody(), true);
        $this->assertTrue($json['success']);
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('itens', $json['data']);
        $this->assertArrayHasKey('maior_consumo', $json['data']);
        $this->assertArrayHasKey('menor_consumo', $json['data']);

        foreach ($json['data']['itens'] as $item) {
            $this->assertArrayHasKey('item_id', $item);
            $this->assertArrayHasKey('nome', $item);
            $this->assertArrayHasKey('quantidade_consumida', $item);
            $this->assertArrayHasKey('estoque_atual', $item);
            $this->assertArrayHasKey('tempo_estimado_uso_dias', $item);

            // ❌ Campos que não devem existir quando completo = false
            $this->assertArrayNotHasKey('codigo_catmat', $item);
            $this->assertArrayNotHasKey('descricao_completa', $item);
            $this->assertArrayNotHasKey('unidade', $item);
        }

        $this->assertArrayHasKey('nome', $json['data']['maior_consumo']);
        $this->assertArrayHasKey('quantidade', $json['data']['maior_consumo']);

        $this->assertArrayHasKey('nome', $json['data']['menor_consumo']);
        $this->assertArrayHasKey('quantidade', $json['data']['menor_consumo']);
    }

    public function testEstatisticasConsumoComParametrosValidos(): void
    {
        $this->get('/relatorio/consumo?periodo_inicio=2025-06-01&periodo_fim=2025-06-30&completo=true');
        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $json = json_decode((string)$this->_response->getBody(), true);
        $this->assertTrue($json['success']);

        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('itens', $json['data']);
        $this->assertArrayHasKey('maior_consumo', $json['data']);
        $this->assertArrayHasKey('menor_consumo', $json['data']);

        foreach ($json['data']['itens'] as $item) {
            $this->assertArrayHasKey('item_id', $item);
            $this->assertArrayHasKey('nome', $item);
            $this->assertArrayHasKey('quantidade_consumida', $item);
            $this->assertArrayHasKey('estoque_atual', $item);
            $this->assertArrayHasKey('tempo_estimado_uso_dias', $item);
            $this->assertArrayHasKey('codigo_catmat', $item);
            $this->assertArrayHasKey('descricao_completa', $item);
            $this->assertArrayHasKey('unidade', $item);

            $this->assertIsInt($item['quantidade_consumida']);
            $this->assertIsInt($item['estoque_atual']);
            $this->assertIsInt($item['tempo_estimado_uso_dias']);
        }

        $this->assertArrayHasKey('nome', $json['data']['maior_consumo']);
        $this->assertArrayHasKey('quantidade', $json['data']['maior_consumo']);

        $this->assertArrayHasKey('nome', $json['data']['menor_consumo']);
        $this->assertArrayHasKey('quantidade', $json['data']['menor_consumo']);
    }

    public function testEstatisticasConsumoSemResultados(): void
    {
        $this->get('/relatorio/consumo?periodo_inicio=2030-01-01&periodo_fim=2030-01-31&completo=true');
        $this->assertResponseCode(200);

        $json = json_decode((string)$this->_response->getBody(), true);
        $this->assertTrue($json['success']);

        $this->assertEquals([], $json['data']['itens']);
        $this->assertNull($json['data']['maior_consumo']);
        $this->assertNull($json['data']['menor_consumo']);
    }

    public function testEstatisticasConsumoParametrosInvalidos(): void
    {
        $this->get('/relatorio/consumo?periodo_inicio=abc&periodo_fim=xyz&completo=talvez');
        $this->assertResponseCode(400);

        $body = (string)$this->_response->getBody();
        $this->assertStringContainsString('período', strtolower($body));
        $this->assertStringContainsString('completo', strtolower($body));
    }

    public function testEstatisticasConsumoComFiltroTipoItemEUnidade(): void
    {
        $this->get('/relatorio/consumo?periodo_inicio=2025-06-01&periodo_fim=2025-06-30&tipo_item=medicamento_vet&unidade=ml&completo=true');
        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $json = json_decode((string)$this->_response->getBody(), true);
        $this->assertTrue($json['success']);
        $this->assertArrayHasKey('data', $json);
    }

    public function testEstatisticasConsumoComTipoItemInvalido(): void
    {
        $this->get('/relatorio/consumo?periodo_inicio=2025-06-01&periodo_fim=2025-06-30&tipo_item=invalido&completo=true');
        $this->assertResponseCode(400);

        $this->assertStringContainsString("tipo_item", (string)$this->_response->getBody());
    }

    public function testEstatisticasConsumoSemParametroCompleto(): void
    {
        $this->get('/relatorio/consumo?periodo_inicio=2025-06-01&periodo_fim=2025-06-30');
        $this->assertResponseCode(400);

        $this->assertStringContainsString("completo", (string)$this->_response->getBody());
    }

    public function testExportarEstatisticasConsumoMensalExcelComParametrosValidos(): void
    {
        $this->configRequest([
            'headers' => ['Accept' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        ]);

        $this->get('/relatorio/consumo/export/excel?periodo_inicio=2024-01-01&periodo_fim=2024-04-01&tipo_item=material&unidade=ml');

        $this->assertResponseOk();
        $this->assertHeaderContains('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertHeaderContains('Content-Disposition', 'attachment; filename="relatorio_consumo_mensal.xlsx"');
    }

    public function testExportarEstatisticasConsumoMensalExcelComParametrosInvalidos(): void
    {
        $this->get('/relatorio/consumo/export/excel?periodo_inicio=abc&periodo_fim=xyz');

        $this->assertResponseCode(400);
        $this->assertResponseContains('Parâmetros inválidos');
    }

    public function testExportarEstatisticasConsumoMensalExcelSemFiltrosOpcionais(): void
    {
        $this->configRequest(['headers' => [
            'Accept' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ]]);

        $this->get('/relatorio/consumo/export/excel?periodo_inicio=2024-01-01&periodo_fim=2024-03-31');

        $this->assertResponseOk();
        $this->assertHeaderContains('Content-Disposition', 'attachment; filename="relatorio_consumo_mensal.xlsx"');
    }

    public function testExportarEstatisticasConsumoMensalExcelComErroInterno(): void
    {
        // Simula falha ao chamar o service, você pode usar mock no Service futuramente
        $this->get('/relatorio/consumo/export/excel?periodo_inicio=2024-01-01&periodo_fim=2024-03-31&tipo_item=invalido');

        $this->assertResponseCode(400);
        $this->assertResponseContains("O parâmetro 'tipo_item' deve ser");
    }

    public function testExportarEstatisticasConsumoMensalExcelComDataFormatoInvalido(): void
    {
        $this->get('/relatorio/consumo/export/excel?periodo_inicio=01-01-2024&periodo_fim=31-03-2024');

        $this->assertResponseCode(400);
        $this->assertResponseContains("periodo_inicio must be a valid date");
    }

    public function testExportarEstatisticasConsumoMensalExcelComTipoItemInvalido(): void
    {
        $this->get('/relatorio/consumo/export/excel?periodo_inicio=2024-01-01&periodo_fim=2024-02-01&tipo_item=remedio');

        $this->assertResponseCode(400);
        $this->assertResponseContains("tipo_item");
    }

    public function testExportarEstatisticasConsumoMensalExcelComTipoItemValido(): void
    {
        $this->get('/relatorio/consumo/export/excel?periodo_inicio=2024-01-01&periodo_fim=2024-02-01&tipo_item=medicamento_vet');

        $this->assertResponseOk();
    }

    public function testExportarEstatisticasConsumoMensalExcelComUnidadeValida(): void
    {
        $this->get('/relatorio/consumo/export/excel?periodo_inicio=2024-01-01&periodo_fim=2024-02-01&unidade=ml');

        $this->assertResponseOk();
    }

    public function testExportarEstatisticasConsumoMensalExcelSemCamposOpcionais(): void
    {
        $this->get('/relatorio/consumo/export/excel?periodo_inicio=2024-01-01&periodo_fim=2024-02-01');

        $this->assertResponseOk();
    }
}
