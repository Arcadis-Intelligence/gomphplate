<?php

namespace Arc\Gomphplate\Tests;

use Arc\Gomphplate\Exceptions\InvalidDataException;
use Arc\Gomphplate\Gomphplate;
use PHPUnit\Framework\TestCase;

class GomphplateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testRenderYamlSuccess()
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

        $result = Gomphplate::renderYaml($template, json_encode($data));
        $this->assertEquals($expected, trim($result));
    }

    public function testRenderYamlInvalidJson()
    {
        $this->expectException(InvalidDataException::class);
        Gomphplate::renderYaml("template", "invalid json");
    }
}
