<?php

declare(strict_types=1);

namespace App\Util;

use Cake\Log\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExcelExporter
{
    /** @var null|callable */
    private static $mockCallback = null;

    /**
     * Define função mockável para testes.
     */
    public static function setMock(?callable $mock): void
    {
        self::$mockCallback = $mock;
    }

    /**
     * Gera uma planilha com múltiplas abas.
     *
     * @param array $dadosPorAba ['nomeAba' => [array de dados]]
     * @param callable $cabecalhoCallback retorna array com os nomes das colunas
     * @param callable $linhaCallback recebe uma linha e retorna array indexado
     *
     * @return string Caminho do arquivo Excel gerado
     *
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public static function gerarPlanilhaComAbas(array $dadosPorAba, callable $cabecalhoCallback, callable $linhaCallback): string
    {
        // Executa mock se definido (usado em testes)
        if (is_callable(self::$mockCallback)) {
            return call_user_func(self::$mockCallback, $dadosPorAba, $cabecalhoCallback, $linhaCallback);
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0); // remove aba padrão

        foreach ($dadosPorAba as $nomeAba => $dados) {
            $sheet = new Worksheet($spreadsheet, $nomeAba);
            $spreadsheet->addSheet($sheet);
            $spreadsheet->setActiveSheetIndexByName($nomeAba);

            $cabecalho = $cabecalhoCallback();
            $sheet->fromArray($cabecalho, null, 'A1');

            $linhaExcel = 2;
            foreach ($dados as $linha) {
                $valores = $linhaCallback($linha);
                $sheet->fromArray($valores, null, 'A' . $linhaExcel);
                $linhaExcel++;
            }

            // Ajuste de largura automática
            $colCount = count($cabecalho);
            for ($col = 1; $col <= $colCount; $col++) {
                $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
            }
        }

        // Criação do arquivo físico
        $arquivoPath = sys_get_temp_dir() . '/relatorio_' . uniqid() . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($arquivoPath);

        return $arquivoPath;
    }
}
