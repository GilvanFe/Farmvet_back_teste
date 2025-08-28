<?php
declare(strict_types=1);

use Migrations\AbstractSeed;

class SetorSeed extends AbstractSeed
{
    public function run(): void
    {
        $setores = [
            ['codigo' => 'GA', 'nome' => 'Grandes Animais'],
            ['codigo' => 'CPA', 'nome' => 'Clinica Pequenos Animais'],
            ['codigo' => 'CCPA', 'nome' => 'Centro Cirurgico Pequenos Animais'],
            ['codigo' => 'PIS', 'nome' => 'Piscicultura'],
            ['codigo' => 'API', 'nome' => 'Apicultura'],
            ['codigo' => 'AP', 'nome' => 'Anatomia Patologica'],
            ['codigo' => 'BM', 'nome' => 'Biologia Molecular'],
            ['codigo' => 'BAC', 'nome' => 'Lab Bacteriologia'],
            ['codigo' => 'PAR', 'nome' => 'Doencas Parasitarias'],
            ['codigo' => 'VIR', 'nome' => 'Lab Virologia'],
            ['codigo' => 'MA', 'nome' => 'Metabolismo Animal'],
            ['codigo' => 'NAN', 'nome' => 'Nutricao Animal'],
            ['codigo' => 'NAP', 'nome' => 'Nutricao Aplicada'],
            ['codigo' => 'PP', 'nome' => 'Parasitologia de Peixes'],
            ['codigo' => 'PC', 'nome' => 'Patologia Clinica'],
            ['codigo' => 'RA', 'nome' => 'Reproducao Animal'],
            ['codigo' => 'RAS', 'nome' => 'Reproducao Assistida'],
            ['codigo' => 'SF', 'nome' => 'Solos e Forragicultura (gefor)'],
            ['codigo' => 'CAV', 'nome' => 'Ciencia Aviaria'],
            ['codigo' => 'QUA', 'nome' => 'Qualicarnes'],
            ['codigo' => 'TCV', 'nome' => 'Tecnica Cirurgica Veterinaria'],
            ['codigo' => 'IH', 'nome' => 'ImunoHistoquimica'],
            ['codigo' => 'NEC', 'nome' => 'Necropsia'],
            ['codigo' => 'UTI', 'nome' => 'UTI'],
            ['codigo' => 'MULTR', 'nome' => 'Multiuso Reproducao'],
            ['codigo' => 'MULTA', 'nome' => 'Multiuso Alimentos'],
            ['codigo' => 'FE', 'nome' => 'Fazenda Escola'],
            ['codigo' => 'CCC', 'nome' => 'Consultorio Clinica Cirurgica'],
            ['codigo' => 'EME', 'nome' => 'Emergencia'],
            ['codigo' => 'GO', 'nome' => 'Ginecologia Obstetricia'],
            ['codigo' => 'RX', 'nome' => 'Radiologia/Diagn. Imagem'],
            ['codigo' => 'CXGA', 'nome' => 'Caixa Anestesia Grandes Animais'],
            ['codigo' => 'HUMAP', 'nome' => 'Hospital Universitario'],
            ['codigo' => 'UFMS', 'nome' => 'Projeto de pesquisa UFMS'],
            ['codigo' => 'SESAU', 'nome' => 'SESAU Campo Grande'],
            ['codigo' => 'BCL', 'nome' => 'Bioclimatologia'],
            ['codigo' => 'HRMS', 'nome' => 'Hospital Regional'],
            ['codigo' => 'FAMEZ', 'nome' => 'Faculdade de Veterinaria/vencidos'],
            ['codigo' => 'ZOO', 'nome' => 'Zoonoses e Saude Publica'],
            ['codigo' => 'BASE', 'nome' => 'Base Aerea de Campo Grande'],
            ['codigo' => 'ANVET', 'nome' => 'Joao Pedro'],
        ];

        $setorTable = $this->table('setor');
        $setorTable->insert($setores)->save();
    }
}
