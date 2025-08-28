<?php
declare(strict_types=1);

use Migrations\AbstractSeed;

class ItemSeed extends AbstractSeed
{
    public function run(): void
    {
        $bases = [
            'Acepromazina', 'Fenobarbital', 'Ciclosporina', 'Gabapentina', 'Carprofeno',
            'Enrofloxacino', 'Amoxicilina + Clavulanato', 'Doxiciclina', 'Ivermectina',
            'Metronidazol', 'Ketamina', 'Isoflurano', 'Vacina Polivalente (V8/V10)',
            'Vacina Antirrábica', 'Soro Fisiológico 0,9%', 'Pomada Oftálmica', 'Luvas Cirúrgicas',
            'Máscara de Oxigênio', 'Agulha Hipodérmica', 'Gaze Estéril', 'Seringa 10 mL',
            'Escalpelos Descartáveis', 'Sonda Nasogástrica', 'Filtro HEPA', 'Bisturi',
            'Tubo de Ensaio 5 mL', 'Tubo de Ensaio 10 mL', 'Tubo de Ensaio 15 mL',
            'Placa de Petri', 'Pipeta Graduada', 'Frasco de Vidro Estéril',
            'Luvas de Exame', 'Máscara de Proteção', 'Capela de Fluxo Laminar',
            'Bloco Cirúrgico', 'Esterilizador a Óxido de Etileno', 'Oxímetro de Pulso',
            'Ventilador Mecânico', 'Monitor Multiparamétrico', 'Bomba de Infusão',
            'Cateter IV', 'Cateter Urinário', 'Coleira Antipulgas', 'Microchip',
            'Coleira Antirrábica', 'Drosera Injetável', 'Cânula Nasal', 'Cateter Endotraqueal',
            'Pulseira de Identificação', 'Etiqueta de Amostra Biológica', 'Termômetro Clínico',
            'Luvas de Nitrila', 'Aparelho de Raio‑X', 'Geniância de Líquido',
            'Saquinho de Urina', 'Gotário de Medicação', 'Extintor de Incêndio'
        ];

        $dosagens = ['5 mg', '10 mg', '50 mg', '100 mg', '250 mg'];

        $unidades = [
            'AM 1.00 ML', 'AM 10.00 ML', 'AM 2.00 ML', 'AM 2.50 ML', 'AM 20.00 ML',
            'AM 3.00 ML', 'AM 4.00 ML', 'AM 5.00 ML', 'BIS 15.00 G', 'BOL 100.00 ML',
            'BOL 250.00 ML', 'BOL 500.00 ML', 'CX 100.00 UN', 'CX 3.00 UN', 'CX 50',
            'EMB 100.00 UN', 'EMB 4.50 KG', 'EMB 500.00 G', 'ENV', 'FR 1.00 L',
            'FR 1.00 ML', 'FR 10.00 ML', 'FR 100.00 ML', 'FR 1000.00 ML', 'FR 15.00 ML',
            'FR 150.00 ML', 'FR 2.00 ML', 'FR 20.00 ML', 'FR 200.00 G', 'FR 250.00 ML',
            'FR 3.00 ML', 'FR 30.00 ML', 'FR 5.00 ML', 'FR 50.00 ML', 'FR 500.00 ML',
            'FR-AM','G','GL 5.00 L','FR 300G','M','PAR','PCT 100.00 UN','PCT 50.00 UN',
            'PCT 500.00 UN','POTE 350.00 G','POTE 700.00 G','RO 1.00 UN','RO 1.80 M',
            'RO 10.00 M','RO 100.00 M','RO 15.00 M','RO 25.00 M','RO 4.50 M','RO 91.00 M',
            'SAC 8.00 G','SER 30.00 G','TAB','UN'
        ];

        $itens = [];
        $count = 0;

        foreach ($bases as $base) {
            foreach ($dosagens as $dose) {
                if ($count >= 300) break 2;

                $nome = "{$base} {$dose}";
                // Defina tipo_item e flags conforme necessidade
                $itens[] = [
                    'nome'                   => $nome,
                    'tipo_item'              => 'consumível',
                    'estoque_minimo'         => rand(5, 500),
                    'is_ativo'               => true,
                    'is_controlado'          => false,
                    'descricao_completa'     => '',
                    'descricao_complementar' => '',
                    'unidade'                => $unidades[array_rand($unidades)],
                    'observacao'             => '',
                    'legislacao_especifica'  => '',
                ];
                $count++;
            }
        }

        $this->table('item', ['noTimestamps' => true])
            ->insert($itens)
            ->save();
    }
}
