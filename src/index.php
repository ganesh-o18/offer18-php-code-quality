#!/usr/bin/env php

<?php
require '../vendor/autoload.php';

use PhpParser\Error;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;

class NonCamelCaseVariableVisitor extends NodeVisitorAbstract {
  public $nonCamelCaseVariables = [];

  public function enterNode(Node $node) {
    if ($node instanceof Node\Expr\Variable && is_string($node->name)) {
      // Check if the variable name is not camel case
      if (!preg_match('/^[a-z]+([A-Z][a-z]*)*$/', $node->name)) {
        $this->nonCamelCaseVariables[] = $node->name;
      }
    }
  }
}

$code = <<<'CODE'
<?php
$nonCamel_case = 1;
$another_var = 2;
$CamelCase = 3;
$correctVariable = 4;
CODE;

try {
  $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
  $ast = $parser->parse($code);

  $traverser = new NodeTraverser();
  $visitor = new NonCamelCaseVariableVisitor();
  $traverser->addVisitor($visitor);
  $traverser->traverse($ast);
  echo "Non-camel-case variables:\n";
  if (count($visitor->nonCamelCaseVariables) > 0) {
    echo @json_encode($visitor->nonCamelCaseVariables);
  }

} catch (Error $e) {
  echo 'Parse error: ', $e->getMessage();
}


