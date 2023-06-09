<?php

use betterphp\cli\RouteType;
use betterphp\utils\attributes\BodyParam;
use betterphp\utils\attributes\DELETE;
use betterphp\utils\attributes\GET;
use betterphp\utils\attributes\PathParam;
use betterphp\utils\attributes\POST;
use betterphp\utils\attributes\ProtectedRoute;
use betterphp\utils\attributes\PUT;
use betterphp\utils\attributes\QueryParam;
use betterphp\utils\attributes\Controller;

require_once dirname(__DIR__) . '/utils/attributes/Controller.php';
require_once dirname(__DIR__) . '/utils/attributes/ProtectedRoute.php';
function deleteDirRecursively(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }

    foreach (scandir($dir) as $filename) {
        if ($filename[0] === '.') continue;
        $filePath = $dir . '/' . $filename;
        if (is_dir($filePath)) {
            deleteDirRecursively($filePath);
        } else {
            unlink($filePath);
        }
    }

    rmdir($dir);
}

function scanAllDir($dir): array
{
    $result = [];
    foreach(scandir($dir) as $filename) {
        if ($filename[0] === '.') continue;
        $filePath = $dir . '/' . $filename;
        if (is_dir($filePath)) {
            foreach (scanAllDir($filePath) as $childFilename) {
                $result[] = $filename . '/' . $childFilename;
            }
        } else {
            $result[] = $filename;
        }
    }
    return $result;
}

function getHttpMethod(ReflectionMethod $reflection): string {
    $attributes = $reflection->getAttributes();
    foreach ($attributes as $attribute) {
        $attributeClass = $attribute->newInstance();
        if ($attributeClass::class === GET::class) {
            return 'GET';
        } else if ($attributeClass::class === POST::class) {
            return 'POST';
        } else if ($attributeClass::class === DELETE::class) {
            return 'DELETE';
        } else if ($attributeClass::class === PUT::class) {
            return 'PUT';
        }
    }
    return '';
}

function methodToString(ReflectionMethod $reflection) : string {
    $startLine = $reflection->getStartLine();
    $endLine = $reflection->getEndLine();
    $length = $endLine - $startLine - 1;
    $source = file($reflection->getFileName());
    $body = implode("\t", array_slice($source, $startLine, $length));
    $methodSignature = '(';
    $parameters = $reflection->getParameters();
    foreach ($parameters as $parameter) {
        $methodSignature .= '$' . $parameter->getName();
        if ($parameter->isDefaultValueAvailable()) {
            $methodSignature .= ' = ' . $parameter->getDefaultValue();
        }
        $methodSignature .= ', ';
    }
    $methodSignature = rtrim($methodSignature, ', ') . ')';

    $temp =  'function ' . $methodSignature . ' {' . PHP_EOL;
    $temp .= "\t\ttry {" . PHP_EOL;
    $temp .= "\t\t" .  $body . PHP_EOL;
    $temp .= "\t\t} catch (ApiException \$e) {" . PHP_EOL;
    $temp .= "\t\t\treturn new Response(\$e->getCode(), \$e->getMessage());" . PHP_EOL;
    $temp .= "\t\t}" . PHP_EOL;
    $temp .= "\t};" . PHP_EOL;

    return $temp;
}
function removeDuplicateUses(string $content): string {
    // Find all use statements using regular expression
    $pattern = '/use\s+(.*?);/';
    preg_match_all($pattern, $content, $matches);

    // Remove duplicate use statements
    $uniqueUses = array_unique($matches[1]);

    // Build the unique use statements
    $uniqueUseStatements = [];
    foreach ($uniqueUses as $use) {
        $uniqueUseStatements[] = 'use ' . $use . ';';
    }

    // Replace the duplicate use statements with unique ones
    $contentWithoutDuplicates = preg_replace($pattern, '', $content);
    $contentWithoutDuplicates = preg_replace('/<\?php\s+/', '<?php' . PHP_EOL . implode(PHP_EOL, $uniqueUseStatements) . PHP_EOL, $contentWithoutDuplicates, 1);

    return $contentWithoutDuplicates;
}
function getCallableParams(ReflectionMethod $reflection) : array {
    $parameters = $reflection->getParameters();
    $params = [];
    foreach ($parameters as $parameter) {
        $params[] = $parameter->getName();
    }
    return $params;
}

function getRouteType(ReflectionMethod $reflectionMethod): RouteType {
    $params = $reflectionMethod->getParameters();
    foreach ($params as $param) {
        $attributes = $param->getAttributes();
        foreach ($attributes as $attribute) {
            $attributeClass = $attribute->newInstance();
            if ($attributeClass::class === PathParam::class) {
                return RouteType::PATH_PARAM;
            } else if ($attributeClass::class === QueryParam::class) {
                return RouteType::QUERY_PARAM;
            } else if ($attributeClass::class === BodyParam::class) {
                return RouteType::BODY_PARAM;
            }
        }
    }
    return RouteType::NORMAL;
}


function getRequiresOldContent(string $oldContent): array {
    $requires = [];

    // Find all require_once statements using regular expression
    $pattern = '/require_once\s*([\'"]([^\'"]+)[\'"])\s*;/';
    preg_match_all($pattern, $oldContent, $matches);

    // Extract the matched requires and add them to the requires array
    if (!empty($matches[1])) {
        foreach ($matches[1] as $require) {
            $requires[] = 'require_once ' . $require . ';';
        }
    }

    return $requires;
}

function getUseOldContent(string $oldContent): array {
    $uses = [];

    // Find all use statements using regular expression
    $pattern = '/use\s+(.*?);/';
    preg_match_all($pattern, $oldContent, $matches);

    // Extract the matched use statements and add them to the uses array
    if (!empty($matches[1])) {
        foreach ($matches[1] as $use) {
            $uses[] = 'use ' . $use . ';';
        }
    }

    return $uses;
}


function getOldContentWithoutRequiresAndUses(string $oldContent): string {
    // Remove <?php tag
    $contentWithoutPHP = preg_replace('/^<\?php\s*/', '', $oldContent);

    // Remove require_once statements
    $contentWithoutRequires = preg_replace('/require_once\s+[\'"].*?[\'"];/s', '', $contentWithoutPHP);

    // Remove use statements
    $contentWithoutRequiresAndUses = preg_replace('/use\s+.*?;/s', '', $contentWithoutRequires);

    return trim($contentWithoutRequiresAndUses);
}


/**
 * @throws ReflectionException
 */
function getClassAttribute(ReflectionClass $reflection, $attributeClass): ReflectionAttribute|false {
    $attributes = $reflection->getAttributes();
    $classToFind = new ReflectionClass($attributeClass);
    foreach ($attributes as $attribute) {
        $attributeClass = $attribute->newInstance();
        if ($attributeClass::class === $classToFind->getName()) {
            return $attribute;
        }
    }
    return false;
}

/**
 * @throws ReflectionException
 */
function getMethodAttribute(ReflectionMethod $reflection, string $attributeClass): ReflectionAttribute|false {
    $attributes = $reflection->getAttributes();
    $classToFind = new ReflectionClass($attributeClass);
    foreach ($attributes as $attribute) {
        $attributeClass = $attribute->newInstance();
        if ($attributeClass::class === $classToFind->getName()) {
            return $attribute;
        }
    }
    return false;
}

/**
 * @throws ReflectionException
 */
function getPropertyAttribute(ReflectionProperty $reflection, string $attributeClass): ReflectionAttribute|false {
    $attributes = $reflection->getAttributes();
    $classToFind = new ReflectionClass($attributeClass);
    foreach ($attributes as $attribute) {
        $attributeClass = $attribute->newInstance();
        if ($attributeClass::class === $classToFind->getName()) {
            return $attribute;
        }
    }
    return false;
}

function isProtectedRoute(ReflectionMethod $reflection): bool {
    $attributes = $reflection->getAttributes();
    foreach ($attributes as $attribute) {
        $attributeClass = $attribute->newInstance();
        if ($attributeClass::class === ProtectedRoute::class) {
            return true;
        }
    }
    return false;
}