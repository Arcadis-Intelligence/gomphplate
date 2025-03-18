# GomPHPlate

## Requirements

* PHP 8.0 or higher
* Gomplate

## Configuration

You can specify the path to the gomplate binary by setting the `GOMPLATE_PATH` environment variable.

## Installation

```bash
composer require arcadis-intelligence/gomphplate
```

## Usage

```php

use ArcadisIntelligence\Gomphplate\Gomphplate;

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
    ],
];

$renderedYaml = Gomphplate::renderYaml($template, $data);
```

Should produce

```yaml
people:
  - name: Alice
    age: 30
  - name: Bob
    age: 25
```