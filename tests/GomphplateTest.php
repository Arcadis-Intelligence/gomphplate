<?php

namespace ArcadisIntelligence\Gomphplate\Tests;

use ArcadisIntelligence\Gomphplate\Exceptions\InvalidDataException;
use ArcadisIntelligence\Gomphplate\Gomphplate;
use PHPUnit\Framework\TestCase;

class GomphplateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testRenderYamlFromStringSuccess()
    {
        $template = <<<YAML
people:
  {{- range .people }}
    - name: {{ .name }}
        age: {{ .age }}
  {{- end }}
YAML;

        $data = [
            'people' => [
                ['name' => 'Alice', 'age' => 30],
                ['name' => 'Bob', 'age' => 25],
            ]
        ];

        $expected = <<<YAML
people:
    - name: Alice
        age: 30
    - name: Bob
        age: 25
YAML;

        $result = Gomphplate::renderYamlFromString($template, $data);
        $this->assertEquals($expected, trim($result));
    }

    public function testRenderYamlFromFile()
    {
        $templatePath = __DIR__ . '/fixtures/template.yaml';

        $data = [
            'people' => [
                ['name' => 'Alice', 'age' => 30],
                ['name' => 'Bob', 'age' => 25],
            ]
        ];

        $expected = <<<YAML
people:
  - name: Alice
    age: 30
  - name: Bob
    age: 25
YAML;

        $result = Gomphplate::renderYamlFromFile($templatePath, $data);
        $this->assertEquals($expected, trim($result));
    }
}
