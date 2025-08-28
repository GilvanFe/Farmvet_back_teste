<?php
declare(strict_types=1);

namespace App\Test\TestCase\Util;

use App\Util\ExcelExporter;
use Cake\TestSuite\TestCase;
use PhpOffice\PhpSpreadsheet\Exception;

class ExcelExporterTest extends TestCase
{
    /**
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function testGerarPlanilhaComAbasGeraArquivoValido(): void
    {
        $dados = [
            'Janeiro' => [
                ['nome' => 'Produto A', 'quantidade' => 10],
            ]
        ];

        $headersFn = fn() => ['Nome', 'Quantidade'];
        $linhaFn = fn($linha) => [$linha['nome'], $linha['quantidade']];

        $caminho = ExcelExporter::gerarPlanilhaComAbas($dados, $headersFn, $linhaFn);

        $this->assertIsString($caminho);
        $this->assertFileExists($caminho);

        unlink($caminho); // limpa o arquivo temporário
    }
}
