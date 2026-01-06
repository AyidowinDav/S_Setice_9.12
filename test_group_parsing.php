<?php
/**
 * Test script for parseGroupesManuels function
 */

// Mock PDO class for testing
class MockPDO {
    public function prepare($query) {
        return new MockStatement();
    }
}

class MockStatement {
    public function execute($params) {
        // Mock data - simulate finding students
        $mockStudents = [
            ['nom' => 'Dupont', 'prenom' => 'Jean'],
            ['nom' => 'Martin', 'prenom' => 'Marie'],
            ['nom' => 'Dubois', 'prenom' => 'Pierre'],
            ['nom' => 'Garcia', 'prenom' => 'Sophie']
        ];

        $this->mockData = [];
        foreach ($params as $i => $param) {
            if ($i % 3 == 0) { // nom
                $nom = $param;
            } elseif ($i % 3 == 1) { // prenom
                $prenom = $param;
            } elseif ($i % 3 == 2) { // id_promotion
                // Check if student exists
                foreach ($mockStudents as $student) {
                    if ($student['nom'] === $nom && $student['prenom'] === $prenom) {
                        $this->mockData = [['id' => rand(1, 100)]];
                        break;
                    }
                }
            }
        }
    }

    public function fetch() {
        return !empty($this->mockData) ? $this->mockData[0] : false;
    }
}

/**
 * Parse la définition manuelle des groupes
 * Format attendu: "Groupe nom: étudiant1, étudiant2; Groupe nom2: étudiant3"
 */
function parseGroupesManuels($definition, $pdo, $id_promotion) {
    $groupes = [];

    // Séparer les groupes par ";"
    $groupes_str = explode(';', $definition);

    foreach ($groupes_str as $groupe_str) {
        $groupe_str = trim($groupe_str);
        if (empty($groupe_str)) continue;

        // Séparer le nom du groupe et les étudiants par ":"
        $parts = explode(':', $groupe_str, 2);
        if (count($parts) !== 2) continue;

        $nom_groupe = trim($parts[0]);
        $etudiants_str = trim($parts[1]);

        // Séparer les étudiants par ","
        $etudiants_noms = array_map('trim', explode(',', $etudiants_str));

        $etudiants_ids = [];
        foreach ($etudiants_noms as $nom_prenom) {
            if (empty($nom_prenom)) continue;

            // Séparer nom et prénom
            $parts_nom = explode(' ', $nom_prenom, 2);
            if (count($parts_nom) !== 2) continue;

            $nom = trim($parts_nom[0]);
            $prenom = trim($parts_nom[1]);

            // Trouver l'étudiant dans la promotion
            $stmt = $pdo->prepare("SELECT id FROM etudiants WHERE nom = ? AND prenom = ? AND id_promotion = ?");
            $stmt->execute([$nom, $prenom, $id_promotion]);
            $etudiant = $stmt->fetch();

            if ($etudiant) {
                $etudiants_ids[] = $etudiant['id'];
            } else {
                return []; // Erreur si étudiant non trouvé
            }
        }

        if (!empty($etudiants_ids)) {
            $groupes[] = $etudiants_ids;
        }
    }

    return $groupes;
}

// Test cases
$testCases = [
    [
        'input' => 'Groupe 1: Dupont Jean, Martin Marie; Groupe 2: Dubois Pierre, Garcia Sophie',
        'expected' => '2 groups parsed successfully',
        'description' => 'Valid format with 2 groups'
    ],
    [
        'input' => 'Groupe A: Dupont Jean',
        'expected' => '1 group parsed successfully',
        'description' => 'Valid format with 1 group'
    ],
    [
        'input' => 'Invalid format without colon',
        'expected' => 'Empty result due to invalid format',
        'description' => 'Invalid format - no colon'
    ],
    [
        'input' => 'Groupe 1: Invalid Student Name',
        'expected' => 'Empty result due to invalid student name',
        'description' => 'Invalid student name format'
    ],
    [
        'input' => '',
        'expected' => 'Empty result for empty input',
        'description' => 'Empty input'
    ]
];

$pdo = new MockPDO();

echo "Testing parseGroupesManuels function:\n\n";

foreach ($testCases as $i => $test) {
    echo "Test " . ($i + 1) . ": " . $test['description'] . "\n";
    echo "Input: '" . $test['input'] . "'\n";

    $result = parseGroupesManuels($test['input'], $pdo, 1);

    echo "Result: " . (empty($result) ? 'Empty array' : count($result) . ' groups') . "\n";
    echo "Expected: " . $test['expected'] . "\n";
    echo "Status: " . (empty($result) === (strpos($test['expected'], 'Empty') !== false) ? 'PASS' : 'FAIL') . "\n\n";
}

echo "Testing completed.\n";
?>
